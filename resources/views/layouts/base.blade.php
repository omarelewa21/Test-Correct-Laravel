<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta charset="UTF-8">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 }}">
    <title version="{{ \tcCore\Http\Helpers\BaseHelper::getCurrentVersion() }}">Test-Correct</title>
    <link rel="icon" href="{{ asset('img/icons/Logo-Test-Correct-recolored-icon-only.svg') }}" />



    @livewireStyles
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}" id="app-css-stylesheet">
    @if(config('bugsnag.browser_key') != '')
        <script src="//d2wy8f7a9ursnm.cloudfront.net/v7/bugsnag.min.js"></script>
        <script>
            Bugsnag.start({
                apiKey: '{{ config('bugsnag.browser_key') }}',
                enabledBreadcrumbTypes: ['error', 'log'],
                autoTrackSessions: false
            })
        </script>
    @endif
    @stack('styling')


</head>
<body id="body" class="ck-content flex flex-col min-h-screen {{ $bodyClass ?? '' }} {{ Browser::isTablet() || Browser::deviceModel()  === 'iPad' ? 'using-ipad' : '' }}">
{{ $slot }}

@livewireScripts

@livewire('livewire-ui-modal')
@hasSection('notification')
    @yield('notification')
@else
    <x-notification />
@endif
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
<script src="{{ mix('/js/ckeditor.js') }}" type="text/javascript"></script>
<script src="https://www.wiris.net/client/plugins/app/WIRISplugins.js?viewer=image"></script>

@if(!is_null(Auth::user())&&Auth::user()->text2speech)
    <script src="//cdn-eu.readspeaker.com/script/12749/webReader/webReader.js?pids=wr&amp;noDefaultSkin=1&amp;&mobile=0&amp;language={{Auth::user()->getLanguageReadspeaker()}}"
            type="text/javascript" id="rs_req_Init"></script>
    <script src="{{ mix('/js/readspeaker_tlc.js') }}"></script>
    <script>
        readspeakerLoadCore();
    </script>
@endif
@stack('scripts')
<script>
    Alpine.start();
    Core.init();
    @if (!is_null(Auth::user()) && Auth::user()->isA('teacher') && Auth::user()->getUseAutoLogOutAttribute())
        Core.startUserLogoutInterval(true, @js(Auth::user()->sessionLength) )
    @endif
    window.processingRequest = false;
    window.cmsProcessTally = 0;
    Livewire.hook('message.sent', (message, component) => {
        window.processingRequest = true

    });
    Livewire.hook('message.processed', (message, component) => {
        window.processingRequest = false
    });
</script>
<script>
    {{-- Place custom styles at the end of the head to overload default ckeditor styling --}}
    var loadDeferredStyles = function() {
        document.querySelector('head').appendChild(document.querySelector('#app-css-stylesheet'))
    };
    var raf = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
        window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
    if (raf) {
        raf(function () {
            window.setTimeout(loadDeferredStyles, 0);
        });
    }
    else {
        window.addEventListener('load', loadDeferredStyles);
    }
</script>
</body>
</html>
