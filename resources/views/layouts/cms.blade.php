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
    <script>
        window.WEBSPELLCHECKER_CONFIG = {
            "autoSearch": true,
            "autoDestroy": true,
            "autocorrect": true,
            "autocomplete": true,
            "serviceProtocol": "http",
            "servicePort": "80",
            "serviceHost": "testwsc.test-correct.nl",
            "servicePath": "wscservice/api"
        }
    </script>
    <script src="http://testwsc.test-correct.nl:80/wscservice/wscbundle/wscbundle.js"></script>
@endpush
</x-layouts.base>