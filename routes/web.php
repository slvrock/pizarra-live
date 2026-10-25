
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardController;

/*
|--------------------------------------------------------------------------
| Página principal de la pizarra
|--------------------------------------------------------------------------
*/

Route::get('/', [
    BoardController::class,
    'index'
])->name('board.index');


/*
|--------------------------------------------------------------------------
| Recibir un trazo realizado por un participante
|--------------------------------------------------------------------------
*/

Route::post('/trazos', [
    BoardController::class,
    'draw'
])->name('board.draw');


/*
|--------------------------------------------------------------------------
| Limpiar la pizarra compartida
|--------------------------------------------------------------------------
*/

Route::post('/limpiar', [
    BoardController::class,
    'clear'
])->name('board.clear');

Route::post('/participantes/actividad', [
    BoardController::class,
    'heartbeat'
])->name('board.heartbeat');



// Mostrar formulario de administrador.
Route::get('/administrador', [
    BoardController::class,
    'adminForm'
])->name('board.admin.form');

// Validar contraseña de administrador.
Route::post('/administrador', [
    BoardController::class,
    'adminLogin'
])
->middleware('throttle:5,1')
->name('board.admin.login');

// Cerrar sesión de administrador.
Route::post('/administrador/salir', [
    BoardController::class,
    'adminLogout'
])->name('board.admin.logout');

