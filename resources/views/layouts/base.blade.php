<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Test-Correct</title>
    <link rel="icon" href="https://www.test-correct.nl/wp-content/uploads/2019/01/cropped-fav-192x192.png" sizes="192x192" />
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <script src="/ckeditor/ckeditor.js" type="text/javascript"></script>

    @livewireStyles
    <link rel="stylesheet" href="/css/app.css">
</head>
<body id="body" class="flex flex-col min-h-screen">
{{ $slot }}

@livewireScripts
<script>
    window.livewire.onError(statusCode => {
        if (statusCode === 440) {
            location.href = '{{ config('app.url_login') }}';

            return false
        }
    })
</script>
<script src="https://cdn.jsdelivr.net/gh/livewire/sortable@v0.x.x/dist/livewire-sortable.js"></script>
<script src="https://www.wiris.net/demo/plugins/app/WIRISplugins.js?viewer=image"></script>
<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
</body>
</html>
