<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="sj-app font-sans text-gray-900 antialiased">
        <div class="sj-shell flex flex-col items-center pt-10 sm:pt-16">
            <div class="w-full max-w-5xl px-4">
                <form method="POST" action="{{ route('locale.switch') }}" class="mb-4 flex justify-end">
                    @csrf
                    <label for="guest-locale-select" class="sr-only">{{ __('Idioma') }}</label>
                    <select id="guest-locale-select" name="locale" class="rounded border-gray-300 text-sm" onchange="this.form.submit()">
                        <option value="es" @selected(app()->getLocale() === 'es')>{{ __('Español') }}</option>
                        <option value="en" @selected(app()->getLocale() === 'en')>{{ __('Inglés') }}</option>
                    </select>
                </form>
            </div>
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>




