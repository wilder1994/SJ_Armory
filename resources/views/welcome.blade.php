<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'SJ Armory') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <style>
            * { box-sizing: border-box; }
            body {
                margin: 0;
                font-family: Figtree, sans-serif;
                background: #0b123c;
                min-height: 100svh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .wrap {
                position: relative;
                width: 100%;
                height: 100svh;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
            }
            .blur-bg {
                position: absolute;
                inset: 0;
                background: url('{{ asset('images/sj-welcome.jpg') }}') center center / cover no-repeat;
                filter: blur(18px) brightness(0.7);
                transform: scale(1.05);
            }
            .hero {
                position: relative;
                z-index: 1;
                width: 100%;
                height: 100%;
                object-fit: contain;
            }
            .cta {
                position: absolute;
                top: 1.5rem;
                right: 1.5rem;
                z-index: 2;
            }
            .login-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.9rem 2.2rem;
                border-radius: 999px;
                background: #0b6fb6;
                color: #ffffff;
                font-weight: 600;
                font-size: 0.95rem;
                text-decoration: none;
                box-shadow: 0 10px 20px rgba(3, 23, 55, 0.35);
                transition: transform 150ms ease, box-shadow 150ms ease, background 150ms ease;
            }
            .login-button:hover {
                background: #0a5f9c;
                transform: translateY(-1px);
                box-shadow: 0 14px 26px rgba(3, 23, 55, 0.4);
            }
        </style>
    </head>
    <body>
        <div class="wrap">
            <div class="blur-bg"></div>
            <img src="{{ asset('images/sj-welcome.jpg') }}" alt="SJ Seguridad Privada" class="hero" />
            <div class="cta">
                <a href="{{ route('login') }}" class="login-button">{{ __('Iniciar sesion') }}</a>
            </div>
        </div>
    </body>
</html>




