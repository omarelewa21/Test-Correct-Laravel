<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('img/icons/Logo-Test-Correct-recolored-icon-only.svg') }}"/>
    <title>Test-Correct</title>

    <!-- Alpine -->

{{--    <script src="/ckeditor/ckeditor.js" type="text/javascript"></script>--}}


    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-592V9J3');</script>
    <!-- End Google Tag Manager -->
    @livewireStyles
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
{{--    <link rel="stylesheet" href="/css/onboarding.css?v=25052022">--}}
    @stack('page_styles')
</head>
<body id="body" class="antialiased font-sans bg-light-grey onboarding-page">
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-592V9J3" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

{{ $slot }}

@livewireScripts
<script src="{{ mix('/js/app.js') }}"></script>
@stack('page_scripts')
<script>
    Alpine.start();
</script>
</body>
</html>