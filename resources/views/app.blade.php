<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @if(Vite::cspNonce() && config('app.env') === 'local')
            <meta property="csp-nonce" nonce="{{ Vite::cspNonce() }}">
        @endif

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
