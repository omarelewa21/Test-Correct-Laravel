<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="icon" href="https://www.test-correct.nl/wp-content/uploads/2019/01/cropped-fav-32x32.png" sizes="32x32" />
    <link rel="icon" href="https://www.test-correct.nl/wp-content/uploads/2019/01/cropped-fav-192x192.png" sizes="192x192" />
    <title>Test-Correct</title>
    <!-- Tailwind CSS -->
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">

    <!-- Alpine -->
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>

    <script src="{{ mix('/js/app.js') }}"></script>
    <script src="/ckeditor/ckeditor.js" type="text/javascript"></script>


    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-156194835-1"></script>
    @livewireStyles
    <link rel="stylesheet" href="/css/onboarding.css">
    @stack('page_styles')
</head>
<body class="antialiased font-sans bg-light-grey">
{{ $slot }}

@livewireScripts
</body>
@stack('page_scripts')
</html>
