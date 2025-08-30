<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scratch Card - {{ $code }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app" data-code="{{ $code }}">
        <div class="d-flex justify-content-center align-items-center" style="height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="text-center text-white">
                <div class="spinner-border text-white mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading your scratch card...</p>
            </div>
        </div>
    </div>
    
    <script>
        window.ticketCode = '{{ $code }}';
    </script>
</body>
</html>
