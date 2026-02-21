<!DOCTYPE html>
<html lang="fr">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPA-UJKZ</title>
    <link rel="icon" type="image/png" href="{{ asset('images/ujkz.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 w-screen h-screen overflow-hidden">
    <div id="app">
        <div class="flex items-center justify-center min-h-screen text-gray-600">
            Chargement de l'application…
        </div>
    </div>
</body>
</html>