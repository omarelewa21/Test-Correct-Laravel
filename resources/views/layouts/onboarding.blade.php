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


    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-592V9J3');</script>
    <!-- End Google Tag Manager -->
    @livewireStyles
    <link rel="stylesheet" href="/css/onboarding.css">
    @stack('page_styles')
</head>
<body class="antialiased font-sans bg-light-grey">
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-592V9J3" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

{{ $slot }}

@livewireScripts
</body>
@stack('page_scripts')
</html>
