
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>Pizarra Live</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f1f5f9;
            color: #1e293b;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0 0 6px;
            font-size: 28px;
        }

        .subtitle {
            margin: 0;
            color: #64748b;
            font-size: 14px;
        }

        .status {
            padding: 9px 12px;
            border-radius: 20px;
            background: #fef3c7;
            color: #92400e;
            font-size: 13px;
            white-space: nowrap;
        }

        .status.connected {
            background: #dcfce7;
            color: #166534;
        }

        .toolbar {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 2px 8px #00000012;
        }

        .tools, .colors {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .tool-btn, .clear-btn {
            min-height: 42px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 14px;
            background: white;
            cursor: pointer;
            font-size: 14px;
        }

        .tool-btn.active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .color-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 2px solid #e2e8f0;
            cursor: pointer;
        }

        .color-btn.active {
            outline: 3px solid #2563eb;
            outline-offset: 2px;
        }

        .size-control {
            display: flex;
            gap: 8px;
            align-items: center;
            font-size: 14px;
        }

        #size {
            width: 95px;
            cursor: pointer;
        }

        .clear-btn {
            margin-left: auto;
            color: #b91c1c;
            background: #fee2e2;
        }

        .clear-btn:disabled {
            opacity: 0.5;
            cursor: wait;
        }

        .canvas-container {
            padding: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: white;
            box-shadow: 0 2px 8px #00000012;
            overflow: hidden;
        }

        #board {
            display: block;
            width: 100%;
            height: auto;
            background: white;
            cursor: crosshair;

            /* Necesario para dibujar con el dedo */
            touch-action: none;
        }

        .footer {
            margin-top: 12px;
            color: #64748b;
            font-size: 13px;
            text-align: center;
        }

        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }

            .header {
                align-items: flex-start;
                flex-direction: column;
            }

            h1 {
                font-size: 23px;
            }

            .toolbar {
                padding: 12px;
                gap: 16px;
            }

            .clear-btn {
                margin-left: 0;
                width: 100%;
            }

            .canvas-container {
                padding: 3px;
            }
        }


/* PANEL DE PARTICIPANTES */

.participants-box {
    background: white;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 8px #00000012;
}

.participants-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 12px;
}

#participantsNames {
    color: #64748b;
    font-size: 13px;
    overflow-wrap: anywhere;
}

#participantForm {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

#participantName {
    flex: 1;
    min-width: 160px;
    padding: 11px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
}

#participantForm button {
    padding: 11px 20px;
    background: #2563eb;
    border: none;
    border-radius: 8px;
    color: white;
    cursor: pointer;
}

#participantMessage {
    margin: 10px 0 0;
    color: #64748b;
    font-size: 13px;
}

    </style>
</head>

<body>

<div class="container">

    <!-- ENCABEZADO -->
    <div class="header">
        <div>
            <h1>🎨 Pizarra Live</h1>
            <p class="subtitle">
                Pizarra colaborativa en tiempo real
            </p>
        </div>

        <span class="status" id="connectionStatus">
            Conectando...
        </span>

@if(session('board_admin'))

    <form method="POST"
          action="{{ route('board.admin.logout') }}">

        @csrf

        <button type="submit" class="tool-btn">
            🔓 Salir de administrador
        </button>

    </form>

@else

    <a href="{{ route('board.admin.form') }}"
       style="color:#2563eb; font-size:14px;">
        🔐 Administrador
    </a>

@endif

    </div>


<!-- PARTICIPANTES -->
<div class="participants-box">

    <div class="participants-info">
        <strong>
            👥 Participantes activos:
            <span id="participantsCount">
                {{ count($initialParticipants) }}
            </span>
        </strong>

        <span id="participantsNames">
            {{ implode(' · ', $initialParticipants) ?: 'Todavía no hay participantes' }}
        </span>
    </div>

    <form id="participantForm">
        <input
            type="text"
            id="participantName"
            placeholder="Ingresá tu apodo"
            minlength="2"
            maxlength="30"
            required
        >

        <button type="submit">
            Participar
        </button>
    </form>

    <p id="participantMessage">
        Ingresá tu apodo para aparecer en la lista.
    </p>

</div>


    <!-- HERRAMIENTAS -->
    <div class="toolbar">

        <div class="tools">
            <button type="button"
                    class="tool-btn active"
                    data-tool="pencil">
                ✏️ Lápiz
            </button>

            <button type="button"
                    class="tool-btn"
                    data-tool="eraser">
                Borrador
            </button>
        </div>

        <!-- COLORES -->
        <div class="colors">

            <button type="button"
                    class="color-btn active"
                    data-color="#111827"
                    style="background:#111827"
                    aria-label="Negro"></button>

            <button type="button"
                    class="color-btn"
                    data-color="#2563eb"
                    style="background:#2563eb"
                    aria-label="Azul"></button>

            <button type="button"
                    class="color-btn"
                    data-color="#ef4444"
                    style="background:#ef4444"
                    aria-label="Rojo"></button>

            <button type="button"
                    class="color-btn"
                    data-color="#22c55e"
                    style="background:#22c55e"
                    aria-label="Verde"></button>

            <button type="button"
                    class="color-btn"
                    data-color="#eab308"
                    style="background:#eab308"
                    aria-label="Amarillo"></button>

            <button type="button"
                    class="color-btn"
                    data-color="#9333ea"
                    style="background:#9333ea"
                    aria-label="Violeta"></button>

        </div>

        <!-- GROSOR -->
        <label class="size-control">
            Grosor
            <input type="range"
                   id="size"
                   min="1"
                   max="30"
                   value="5">
            <span id="sizeValue">5</span>
        </label>

        <!-- LIMPIAR -->
       
        @if(session('board_admin'))

            <button type="button"
                    class="clear-btn"
                    id="clearBtn">
                🗑️ Limpiar pizarra
            </button>

        @endif


    </div>

    <!-- PIZARRA -->
    <div class="canvas-container">
        <canvas id="board"
                width="1200"
                height="800">
        </canvas>
    </div>

    <p class="footer">
        Laravel + Reverb + Echo + WebSockets
    </p>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // 1. CONFIGURACION INICIAL
    // ==========================================

    const canvas = document.getElementById('board');
    const ctx = canvas.getContext('2d');

    const csrf = document.querySelector(
        'meta[name="csrf-token"]'
    ).content;

    const drawUrl = @json(route('board.draw'));
    const clearUrl = @json(route('board.clear'));

    const status = document.getElementById(
        'connectionStatus'
    );

    let strokes = @json($initialStrokes);
    let currentStroke = null;
    let activePointer = null;

    let tool = 'pencil';
    let color = '#111827';
    let size = 5;

        // Inicialmente no se permite dibujar.
    let canDraw = false;

    function setDrawingAccess(enabled) {
        canDraw = enabled;

        canvas.style.cursor = enabled
            ? 'crosshair'
            : 'not-allowed';
    }

    setDrawingAccess(false);

    // ==========================================
    // 2. GENERAR IDENTIFICADOR DE TRAZO
    // ==========================================

    function generateId() {
        if (window.crypto &&
            typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }

        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'
            .replace(/[xy]/g, function(c) {
                const r = Math.floor(Math.random() * 16);
                const v = c === 'x' ? r : (r & 3 | 8);
                return v.toString(16);
            });
    }

    // ==========================================
    // 3. OBTENER COORDENADAS
    // ==========================================

    function getPoint(e) {
        const rect = canvas.getBoundingClientRect();

        return {
            x: Math.max(0, Math.min(1,
                (e.clientX - rect.left) / rect.width
            )),
            y: Math.max(0, Math.min(1,
                (e.clientY - rect.top) / rect.height
            ))
        };
    }

    // ==========================================
    // 4. DIBUJAR UN TRAZO
    // ==========================================

    function drawStroke(stroke) {

        const points = stroke.points;

        if (!points || points.length === 0) {
            return;
        }

        ctx.save();

        ctx.globalCompositeOperation =
            stroke.tool === 'eraser'
                ? 'destination-out'
                : 'source-over';

        ctx.strokeStyle = stroke.color;
        ctx.fillStyle = stroke.color;
        ctx.lineWidth = stroke.size;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        ctx.beginPath();

        const first = points[0];

        if (points.length === 1) {

            ctx.arc(
                first.x * canvas.width,
                first.y * canvas.height,
                stroke.size / 2,
                0,
                Math.PI * 2
            );

            ctx.fill();

        } else {

            ctx.moveTo(
                first.x * canvas.width,
                first.y * canvas.height
            );

            for (let i = 1; i < points.length; i++) {
                ctx.lineTo(
                    points[i].x * canvas.width,
                    points[i].y * canvas.height
                );
            }

            ctx.stroke();
        }

        ctx.restore();
    }

    // ==========================================
    // 5. REDIBUJAR TODA LA PIZARRA
    // ==========================================

    function render() {

        ctx.clearRect(
            0,
            0,
            canvas.width,
            canvas.height
        );

        strokes.forEach(drawStroke);

        if (currentStroke) {
            drawStroke(currentStroke);
        }
    }
    // Reconstruir los dibujos almacenados en MySQL
    render();

    
    // ==========================================
    // 6. ENVIAR DATOS A LARAVEL
    // ==========================================

    async function send(url, data = {}) {

        console.log('Enviando a Laravel:', url, data);

        const response = await fetch(url, {
            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },

            body: JSON.stringify(data)
        });

        // Leer la respuesta de Laravel
        const responseText = await response.text();

        let result;

        try {
            result = JSON.parse(responseText);
        } catch (error) {
            result = {
                message: responseText.substring(0, 500)
            };
        }

        console.log('Estado HTTP:', response.status);
        console.log('Respuesta Laravel:', result);

        if (!response.ok) {

            let mensaje = result.message ||
                'Error HTTP ' + response.status;

            // Mostrar errores de validacion
            if (result.errors) {
                mensaje += '\n' +
                    Object.values(result.errors)
                        .flat()
                        .join('\n');
            }

            throw new Error(mensaje);
        }

        return result;
    }


    // ==========================================
    // PARTICIPANTES ACTIVOS
    // ==========================================

    const heartbeatUrl = @json(route('board.heartbeat'));

    const nameInput = document.getElementById(
        'participantName'
    );

    const participantMessage = document.getElementById(
        'participantMessage'
    );

    // Identificador propio de esta pestaña.
    let participantId = sessionStorage.getItem(
        'pizarra_live_participant_id'
    );

    if (!participantId) {
        participantId = generateId();

        sessionStorage.setItem(
            'pizarra_live_participant_id',
            participantId
        );
    }

    // Recuperar apodo anterior.
    let activeName = sessionStorage.getItem(
        'pizarra_live_participant_name'
    ) || '';

    // Actualizar listado en pantalla.
    function updateParticipants(names) {

        if (!Array.isArray(names)) return;

        document.getElementById(
            'participantsCount'
        ).textContent = names.length;

        document.getElementById(
            'participantsNames'
        ).textContent = names.length
            ? names.join(' · ')
            : 'Todavia no hay participantes';
    }

    // Informar actividad a Laravel.
    async function sendHeartbeat(name) {

        if (!name) return;

        try {
            const result = await send(
                heartbeatUrl,
                {
                    participant_uuid: participantId,
                    name: name
                }
            );

            activeName = name;

            sessionStorage.setItem(
                'pizarra_live_participant_name',
                name
            );

            updateParticipants(result.names);

            // El servidor reconocio al participante.
             setDrawingAccess(true);

            participantMessage.textContent =
                'Participando como: ' + name;

        } catch (error) {

            console.error(
                'Error actualizando participante:',
                error
            );
              
            // No permitir dibujar si falla el registro 

            participantMessage.textContent =
                'No se pudo actualizar tu participacion.';

                   setDrawingAccess(false); 
        }
    }

    // Registrar apodo.
    document.getElementById('participantForm')
        .addEventListener('submit', e => {

            e.preventDefault();

            const name = nameInput.value.trim();

            if (name.length < 2) {
                alert('Ingresa un apodo de al menos 2 caracteres.');
                return;
            }

            sendHeartbeat(name);
        });

    // Restaurar participante si actualiza la pagina.
    if (activeName) {
        nameInput.value = activeName;
        sendHeartbeat(activeName);
    }

    // Enviar actividad cada 15 segundos.
    setInterval(() => {
        if (activeName) {
            sendHeartbeat(activeName);
        }
    }, 15000);



    // ==========================================
    // 7. COMENZAR A DIBUJAR
    // ==========================================

    canvas.addEventListener('pointerdown', e => {

            if (!canDraw) {
            alert('Primero debes ingresar tu apodo.');

            document.getElementById('participantName').focus();

            return;
        }

        if (currentStroke) return;

        if (e.pointerType === 'mouse' &&
            e.button !== 0) return;

        e.preventDefault();

        activePointer = e.pointerId;

        canvas.setPointerCapture(e.pointerId);

        currentStroke = {
            id: generateId(),
            tool: tool,
            color: color,
            size: size,
            points: [getPoint(e)]
        };

        render();
    });

    // ==========================================
    // 8. MOVIMIENTO DEL LAPIZ
    // ==========================================

    canvas.addEventListener('pointermove', e => {

        if (!currentStroke ||
            e.pointerId !== activePointer) {
            return;
        }

        e.preventDefault();

        const point = getPoint(e);

        const last = currentStroke.points[
            currentStroke.points.length - 1
        ];

        const distance = Math.hypot(
            point.x - last.x,
            point.y - last.y
        );

        if (distance > 0.001 &&
            currentStroke.points.length < 1500) {

            currentStroke.points.push(point);

            render();
        }
    });

    // ==========================================
    // 9. FINALIZAR Y ENVIAR DIBUJO
    // ==========================================

    canvas.addEventListener('pointerup', async e => {

        if (!currentStroke ||
            e.pointerId !== activePointer) {
            return;
        }

        e.preventDefault();

        const stroke = currentStroke;

        currentStroke = null;
        activePointer = null;

        strokes.push(stroke);

        render();

        try {
            await send(drawUrl, stroke);

        } catch (error) {

            console.error(
                'Error enviando trazo:',
                error
            );

            strokes = strokes.filter(
                s => s.id !== stroke.id
            );

            render();

            alert(
                        'No se pudo compartir el dibujo.\n\n' +
                        error.message
                    );
        }
    });

    // Cancelar dibujo interrumpido.
    canvas.addEventListener('pointercancel', e => {

        if (e.pointerId !== activePointer) {
            return;
        }

        currentStroke = null;
        activePointer = null;

        render();
    });

    // ==========================================
    // 10. SELECCIONAR HERRAMIENTA
    // ==========================================

    const toolButtons = document.querySelectorAll(
        '[data-tool]'
    );

    function updateToolButtons() {

        toolButtons.forEach(button => {
            button.classList.toggle(
                'active',
                button.dataset.tool === tool
            );
        });
    }

    toolButtons.forEach(button => {

        button.addEventListener('click', () => {

            tool = button.dataset.tool;

            updateToolButtons();
        });
    });

    // ==========================================
    // 11. SELECCIONAR COLOR
    // ==========================================

    const colorButtons = document.querySelectorAll(
        '[data-color]'
    );

    colorButtons.forEach(button => {

        button.addEventListener('click', () => {

            color = button.dataset.color;

            // Al seleccionar color, usar lapiz.
            tool = 'pencil';

            updateToolButtons();

            colorButtons.forEach(b => {
                b.classList.toggle(
                    'active',
                    b === button
                );
            });
        });
    });

    // ==========================================
    // 12. CONTROLAR GROSOR
    // ==========================================

    document.getElementById('size')
        .addEventListener('input', e => {

            size = Number(e.target.value);

            document.getElementById(
                'sizeValue'
            ).textContent = size;
        });

    // ==========================================
    // 13. LIMPIAR PIZARRA
    // ==========================================

    const clearBtn = document.getElementById(
        'clearBtn'
    );

    clearBtn?.addEventListener('click', async () => {

        const confirmed = confirm(
            '¿Querés limpiar la pizarra de todos los participantes?'
        );

        if (!confirmed) return;

        clearBtn.disabled = true;

        try {

            await send(clearUrl);

            strokes = [];
            currentStroke = null;
            activePointer = null;

            render();

        } catch (error) {

            console.error(
                'Error limpiando pizarra:',
                error
            );

            alert('No se pudo limpiar la pizarra.');

        } finally {
            clearBtn.disabled = false;
        }
    });

    // ==========================================
    // 14. LARAVEL ECHO + REVERB
    // ==========================================

    if (!window.Echo) {
        console.error(
            'Laravel Echo no esta inicializado.'
        );

        status.textContent = 'Echo no disponible';
        return;
    }

    // Suscribirse al canal compartido.
    window.Echo.channel('pizarra-live')

        // Recibir dibujos de otros participantes.
        .listen('.StrokeDrawn', (event) => {

            const stroke = event.stroke;

            if (!stroke) return;

            // Evitar duplicar el trazo del emisor.
            const exists = strokes.some(
                s => s.id === stroke.id
            );

            if (!exists) {
                strokes.push(stroke);
                render();
            }
        })

        // Recibir la orden de limpiar.
 
        .listen('.BoardCleared', () => {

            strokes = [];
            currentStroke = null;
            activePointer = null;

            render();
        })

        // Actualizar participantes en vivo.
        .listen('.ParticipantsUpdated', (event) => {

            updateParticipants(event.names);

        });


    // ==========================================
    // 15. ESTADO DE CONEXION
    // ==========================================

    const connection =
        window.Echo.connector?.pusher?.connection;

    function updateConnection(state) {

        const connected = state === 'connected';

        status.classList.toggle(
            'connected',
            connected
        );

        status.textContent = connected
            ? '● Conectado'
            : '● ' + (
                state === 'connecting'
                    ? 'Conectando...'
                    : 'Desconectado'
            );
    }

    if (connection) {

        updateConnection(connection.state);

        connection.bind('state_change', states => {
            updateConnection(states.current);
        });
    }

});
</script>

</body>
</html>
