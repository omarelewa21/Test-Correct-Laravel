<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta charset="UTF-8">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 }}">
    <title version="{{ \tcCore\Http\Helpers\BaseHelper::getCurrentVersion() }}">Test-Correct</title>
    <link rel="icon" href="{{ asset('img/icons/Logo-Test-Correct-recolored-icon-only.svg') }}"/>
    {{--    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">--}}
    <script  src="https://cdn.anychart.com/releases/8.11.0/js/anychart-base.min.js"
            type="text/javascript"></script>
    <script src="/ckeditor/ckeditor.js" type="text/javascript"></script>
    @if(!is_null(Auth::user())&&Auth::user()->canUseTeacherCkEditorWithWebSpellChecker())
        <script src="{{ mix('/js/ckeditor_teacher_wsc.js') }}" type="text/javascript"></script>
    @elseif(!is_null(Auth::user())&&Auth::user()->canUseTeacherCkEditorWithoutWebSpellChecker())
        <script src="{{ mix('/js/ckeditor_teacher.js') }}" type="text/javascript"></script>
    @else
        <script src="{{ mix('/js/ckeditor.js') }}" type="text/javascript"></script>
    @endif

    @livewireStyles
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
    @if(config('bugsnag.browser_key') != '')
        <script src="//d2wy8f7a9ursnm.cloudfront.net/v7/bugsnag.min.js"></script>
        <script>Bugsnag.start({ apiKey: '{{ config('bugsnag.browser_key') }}' })</script>
    @endif
    @stack('styling')




</head>
<body id="body" class="flex flex-col min-h-screen">
{{ $slot }}

@livewireScripts
<script>
    window.livewire.onError(statusCode => {

        if (statusCode === 406) {
            Livewire.emit('set-force-taken-away');

            return false;
        }
        if (statusCode === 440 || statusCode === 419 || statusCode === 401 || statusCode === 403) {
            location.href = '{{ \tcCore\Http\Helpers\BaseHelper::getLoginUrl() }}';

            return false
        }
    })
</script>
<script src="{{ mix('/js/app.js') }}"></script>

<script src="https://www.wiris.net/client/plugins/app/WIRISplugins.js?viewer=image"></script>

@if(!is_null(Auth::user())&&Auth::user()->text2speech)
<script src="//cdn-eu.readspeaker.com/script/12749/webReader/webReader.js?pids=wr&amp;noDefaultSkin=1&amp;&mobile=0&amp;language={{Auth::user()->getLanguageReadspeaker()}}" type="text/javascript" id="rs_req_Init"></script>
<script src="{{ mix('/js/readspeaker_tlc.js') }}"></script>
<script>
    readspeakerLoadCore();
</script>
@endif
@if (!is_null(Auth::user()) && Auth::user()->isA('teacher'))
<script>
    Core.startUserLogoutInterval(true, {{session('extensionTime', 15*60)}}) // session check if extensionTime exists, else => session time will be 15 min
</script>
@endif
@stack('scripts')
<script>
    Alpine.start();
    Core.init();
    {{--Echo.connector.pusher.config.auth.headers['X-CSRF-TOKEN'] = '{{ csrf_token() }}'--}}
</script>
</body>
</html>
