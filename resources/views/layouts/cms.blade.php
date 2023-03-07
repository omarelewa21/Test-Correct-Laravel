<x-layouts.base>
    <div id="cms-container" class="min-h-screen">
        @if(Auth::user()->schoolLocation->canUseCmsWithDrawer() && request()->query('withDrawer'))
            <livewire:drawer.cms/>
        @endif
        {{ $slot }}
    </div>
    <x-notification/>
    @livewire('livewire-ui-modal')

@push('scripts')
    @if(Auth::user()->schoolLocation->allow_wsc)
        <script>
            window.WEBSPELLCHECKER_CONFIG = {
                "autoSearch": false,
                "autoDestroy": true,
                "autocorrect": true,
                "autocomplete": true,
                "serviceProtocol": "https",
                "servicePort": "80",
                "serviceHost": "wsc.test-correct.nl",
                "servicePath": "wscservice/api",
                "srcUrl": "https://wsc.test-correct.nl/wscservice/wscbundle/wscbundle.js",
            }
        </script>
{{--        <script src="https://wsc.test-correct.nl/wscservice/wscbundle/wscbundle.js"></script>--}}
    @endif
@endpush
</x-layouts.base>
