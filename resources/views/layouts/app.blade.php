<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎭</text></svg>">

        <style>
            input[type="checkbox"] {
                transform: scale(1.5);
            }
            input[type="text"] {
                min-width: 300px;
            }

            @stack('styles')
        </style>
        <title>{{ $title ?? config('app.name') }}</title>
    </head>
    <body>
        <nav class="navbar bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand text-white fw-bold" href="/">
                    <span class="me-1">🎭</span>
                    {{ config('app.name') }}
                </a>
                
                <div>
                    <a href="/import" class="btn btn-outline-light">Import</a>
                </div>
            </div>
        </nav>

        @yield('content')
    </body>
</html>