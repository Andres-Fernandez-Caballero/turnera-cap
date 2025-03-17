<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pista de Patinaje - Turnos</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
        <style>
            /* Reset básico */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Roboto', sans-serif;
            }
    
            body {
                background-color: #f4faff;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 100vh;
                padding: 20px;
            }
    
            .container {
                background: white;
                padding: 40px;
                border-radius: 12px;
                box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
                text-align: center;
                max-width: 500px;
            }
    
            h1 {
                color: #1565c0;
                font-size: 24px;
                margin-bottom: 10px;
                font-weight: 700;
            }
    
            p {
                color: #666;
                font-size: 16px;
                margin-bottom: 20px;
            }
    
            .btn {
                display: inline-block;
                background-color: #1976d2;
                color: white;
                padding: 12px 24px;
                border-radius: 6px;
                text-decoration: none;
                font-size: 16px;
                font-weight: 500;
                transition: background 0.3s ease;
            }
    
            .btn:hover {
                background-color: #0d47a1;
            }
    
            footer {
                margin-top: 20px;
                font-size: 14px;
                color: #999;
            }
        </style>
    </head>
    <body>
    
        <div class="container">
            <h1>Turnos para Pista de Patinaje</h1>
            <p>Administra y gestiona los turnos de la pista de patinaje de manera rápida y sencilla.</p>
            <a href="{{ route('filament.admin.auth.login') }}" class="btn">Acceder al Panel</a>
            <footer>&copy; 2025 Pista de Patinaje. Todos los derechos reservados.</footer>
        </div>
    
    </body>
    </html>
    