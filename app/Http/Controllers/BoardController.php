<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BoardStroke;
use App\Events\StrokeDrawn;
use App\Events\BoardCleared;
use App\Models\BoardParticipant;
use App\Events\ParticipantsUpdated;
use Illuminate\Support\Facades\Hash;


class BoardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MOSTRAR PIZARRA Y RECUPERAR DIBUJOS
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        // Obtener todos los trazos guardados
        // respetando el orden en que se registraron.

        $strokes = BoardStroke::orderBy('id', 'asc')
            ->get()
            ->map(function ($stroke) {

                return [
                    'id' => $stroke->stroke_uuid,
                    'tool' => $stroke->tool,
                    'color' => $stroke->color,
                    'size' => $stroke->size,
                    'points' => $stroke->points
                ];

            })
            ->all();

        // Enviar los dibujos a la vista
       return view('board', [
    'initialStrokes' => $strokes,

    'initialParticipants' => BoardParticipant::where(
        'last_seen',
        '>=',
        now()->subSeconds(45)
    )
    ->orderBy('name')
    ->pluck('name')
    ->all()
]); 
    }

    /*
    |--------------------------------------------------------------------------
    | GUARDAR Y TRANSMITIR UN DIBUJO
    |--------------------------------------------------------------------------
    */

    public function draw(Request $request)
    { 

        // Verificar que el participante haya ingresado
        // un apodo y tenga una sesion activa.

        $participantUuid = $request->session()
            ->get('board_participant_uuid');

        $isActive = $participantUuid
            && BoardParticipant::where(
                'participant_uuid',
                $participantUuid
            )
            ->where(
                'last_seen',
                '>=',
                now()->subSeconds(45)
            )
            ->exists();

        if (!$isActive) {
            return response()->json([
                'success' => false,
                'message' => 'Debes ingresar un apodo antes de dibujar.'
            ], 403);
        }

        // Validar los datos recibidos
        $data = $request->validate([

            'id' => [
                'required',
                'uuid'
            ],

            'tool' => [
                'required',
                'in:pencil,eraser'
            ],

            'color' => [
                'required',
                'regex:/^#[0-9a-fA-F]{6}$/'
            ],

            'size' => [
                'required',
                'integer',
                'between:1,30'
            ],

            'points' => [
                'required',
                'array',
                'min:1',
                'max:1500'
            ],

            'points.*.x' => [
                'required',
                'numeric',
                'between:0,1'
            ],

            'points.*.y' => [
                'required',
                'numeric',
                'between:0,1'
            ]
        ]);

        // PASO 1: Guardar el dibujo en MySQL
        BoardStroke::create([
            'stroke_uuid' => $data['id'],
            'tool' => $data['tool'],
            'color' => $data['color'],
            'size' => $data['size'],
            'points' => $data['points']
        ]);

        // PASO 2: Transmitir el evento con Reverb
        broadcast(new StrokeDrawn($data));

        // PASO 3: Responder al navegador
        return response()->json([
            'success' => true,
            'message' => 'Dibujo guardado y transmitido.'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LIMPIAR LA PIZARRA
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | LIMPIAR PIZARRA - SOLO ADMINISTRADOR
    |--------------------------------------------------------------------------
    */

    public function clear(Request $request)
    {
        // Verificar permisos de administrador.
        if (!$request->session()->get('board_admin', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el administrador puede limpiar la pizarra.'
            ], 403);
        }

        // Eliminar los dibujos almacenados.
        BoardStroke::query()->delete();

        // Informar a todos mediante WebSockets.
        broadcast(new BoardCleared());

        return response()->json([
            'success' => true,
            'message' => 'Pizarra limpiada correctamente.'
        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | REGISTRAR Y ACTUALIZAR PARTICIPANTES
    |--------------------------------------------------------------------------
    */

    public function heartbeat(Request $request)
    {
        $data = $request->validate([
            'participant_uuid' => [
                'required',
                'uuid'
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:30'
            ]
        ]);

        // Eliminar participantes inactivos
        $removed = BoardParticipant::where(
            'last_seen',
            '<',
            now()->subSeconds(45)
        )->delete();

        // Buscar si el participante ya existe
        $participant = BoardParticipant::firstOrNew([
            'participant_uuid' => $data['participant_uuid']
        ]);

        // Detectar nuevo participante o cambio de apodo
        $changed = !$participant->exists ||
            $participant->name !== $data['name'];

        // Actualizar datos
        $participant->name = $data['name'];
        $participant->last_seen = now();

        $participant->save();

        // Habilitar a este participante en la sesion Laravel.
            $request->session()->put(
                'board_participant_uuid',
                $participant->participant_uuid
            );

        // Obtener participantes activos
        $names = BoardParticipant::where(
            'last_seen',
            '>=',
            now()->subSeconds(45)
        )
        ->orderBy('name')
        ->pluck('name')
        ->all();

        // Avisar a los navegadores cuando cambia la lista
        if ($changed || $removed > 0) {
            broadcast(new ParticipantsUpdated($names));
        }

        return response()->json([
            'success' => true,
            'count' => count($names),
            'names' => $names
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | MOSTRAR ACCESO ADMINISTRADOR
    |--------------------------------------------------------------------------
    */

    public function adminForm()
    {
        return view('board-admin');
    }

    /*
    |--------------------------------------------------------------------------
    | INICIAR SESION COMO ADMINISTRADOR
    |--------------------------------------------------------------------------
    */

    public function adminLogin(Request $request)
    {
        $data = $request->validate([
            'password' => [
                'required',
                'string'
            ]
        ]);

        $hash = config('board.admin_password_hash');

        if (
            !is_string($hash) ||
            !Hash::check($data['password'], $hash)
        ) {
            return back()->withErrors([
                'password' => 'Contraseña incorrecta.'
            ]);
        }

        // Renovar identificador de sesion.
        $request->session()->regenerate();

        // Marcar la sesion como administradora.
        $request->session()->put(
            'board_admin',
            true
        );

        return redirect()->route('board.index');
    }

    /*
    |--------------------------------------------------------------------------
    | CERRAR SESION DE ADMINISTRADOR
    |--------------------------------------------------------------------------
    */

    public function adminLogout(Request $request)
    {
        $request->session()->forget('board_admin');

        $request->session()->regenerate();

        return redirect()->route('board.index');
    }


}
