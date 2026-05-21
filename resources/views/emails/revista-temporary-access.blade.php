<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Acceso temporal') }}</title>
</head>
<body style="font-family: system-ui, -apple-system, Segoe UI, sans-serif; line-height: 1.5; color: #111827;">
    <p>{{ __('Hola :name,', ['name' => $recipientName]) }}</p>
    <p>{{ __('Se le ha otorgado acceso temporal para cargar fotografías técnicas de armas en :app.', ['app' => $appName]) }}</p>
    <ul style="padding-left: 1.25rem;">
        <li><strong>{{ __('Enlace') }}:</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></li>
        <li><strong>{{ __('Correo') }}:</strong> {{ $loginEmail }}</li>
        <li><strong>{{ __('Código temporal') }}:</strong> {{ $accessCode }}</li>
        <li><strong>{{ __('Válido hasta') }}:</strong> {{ $expiresAt->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</li>
    </ul>
    <p>{{ __('El código deja de funcionar al vencer las 12 horas o si el responsable revoca el acceso.') }}</p>
    <p style="margin-top: 1.5rem; color: #6b7280; font-size: 0.875rem;">{{ __('Si no esperaba este mensaje, ignore el correo.') }}</p>
</body>
</html>
