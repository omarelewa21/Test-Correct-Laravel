<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Test-Correct</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">

    <script src="/ckeditor/ckeditor.js" type="text/javascript"></script>

    @livewireStyles
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="flex flex-col min-h-screen">
{{ $slot }}

@livewireScripts
</body>
</html>