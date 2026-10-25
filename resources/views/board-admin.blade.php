<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Administrador - Pizarra Live</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            font-family: Arial, sans-serif;
        }

        .login-box {
            width: 100%;
            max-width: 390px;
            margin: 15px;
            padding: 30px;
            border-radius: 15px;
            background: white;
            box-shadow: 0 5px 25px #00000012;
        }

        h2 {
            margin-top: 0;
            color: #1e293b;
        }

        p {
            color: #64748b;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 13px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
        }

        .error {
            color: #dc2626;
            font-size: 13px;
        }

        a {
            display: block;
            margin-top: 18px;
            text-align: center;
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="login-box">

    <h2>🔐 Administrador</h2>

    <p>
        Ingresá tu contraseña para habilitar
        el control de la pizarra.
    </p>

    <form method="POST"
          action="{{ route('board.admin.login') }}">

        @csrf

        <input
            type="password"
            name="password"
            placeholder="Contraseña de administrador"
            autocomplete="current-password"
            required
        >

        @error('password')
            <p class="error">{{ $message }}</p>
        @enderror

        <button type="submit">
            Ingresar
        </button>

    </form>

    <a href="{{ route('board.index') }}">
        Volver a la pizarra
    </a>

</div>

</body>
</html>
