<x-layouts.base>
    <livewire:co-learning.header testName="{{ $testName ?? '' }}"/>

    <main class="flex flex-1 items-stretch mx-8 xl:mx-28 m-foot-head">
        {{ $slot  }}
    </main>



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
                    "enableBadgeButton":false
                }
            </script>
            <script src="https://wsc.test-correct.nl/wscservice/wscbundle/wscbundle.js"></script>
        @endif
    @endpush
</x-layouts.base>