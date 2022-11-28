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
    {{--    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">--}}
    <script src="/ckeditor/ckeditor.js" type="text/javascript"></script>
    <script src="{{ mix('/js/ckeditor.js') }}" type="text/javascript"></script>

    @livewireStyles
<style>
    @font-face {
        font-family: "Nunito Bold";
        src: url("file:{{base_path()}}/resources/fonts/Nunito/Nunito-Bold.ttf") format('truetype');
        font-weight: bold;
    }
    @font-face {
        font-family: Nunito Regular;
        src: url("file:{{base_path()}}/resources/fonts/Nunito/Nunito-Regular.ttf") format('truetype');
        font-weight: normal;
    }
</style>

    <link rel="stylesheet" href="file://{{ public_path('/css/app_pdf.css') }}">
    @if(config('bugsnag.browser_key') != '')
        <script src="//d2wy8f7a9ursnm.cloudfront.net/v7/bugsnag.min.js"></script>
        <script>Bugsnag.start({ apiKey: '{{ config('bugsnag.browser_key') }}' })</script>
    @endif
    @stack('styling')




</head>
<body id="body" class="flex flex-col min-h-screen" onload="addIdsToQuestionHtml()">
{{ $slot }}


</body>
</html>
