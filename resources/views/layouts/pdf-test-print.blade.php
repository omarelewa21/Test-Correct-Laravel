@props([
'titleForPdfPage' => 'Test-Correct'
])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta charset="UTF-8">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 }}">
    <title version="{{ \tcCore\Http\Helpers\BaseHelper::getCurrentVersion() }}">{{ $titleForPdfPage }}</title>
    <link rel="icon" href="{{ asset('img/icons/Logo-Test-Correct-recolored-icon-only.svg') }}"/>

    <link rel="stylesheet" href="file://{{ public_path('/css/app_pdf.css') }}">
    <link rel="stylesheet" href="file://{{ public_path('/css/print-test-pdf.css') }}">
    @if(config('bugsnag.browser_key') != '')
        <script src="//d2wy8f7a9ursnm.cloudfront.net/v7/bugsnag.min.js"></script>
        <script>
        Bugsnag.start({
            apiKey: '{{ config('bugsnag.browser_key') }}',
            enabledBreadcrumbTypes: ['error', 'log', 'navigation', 'request']
        })
        </script>
    @endif
    @stack('styling')

</head>
<body id="body" class="min-h-screen test-print-pdf">
{{ $slot }}


</body>
</html>
