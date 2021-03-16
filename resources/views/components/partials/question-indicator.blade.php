<div class="question-indicator w-full">
    <div class="flex flex-wrap" x-data=""
         x-init="setTimeout( function() { $dispatch('current-updated', {'current': {{ $this->q }} })}, 1)">
        @foreach($nav as $key => $q)

            <div class="flex flex-col mb-3 relative
                    @if($q['group']['id'] != 0 && !$loop->last && $nav[$key+1]['group']['id'] != 0 && $nav[$key+1]['group']['id'] === $q['group']['id'])
                        number-divider group
                    @endif
                    @if (!$q['answered'] && ($q['group']['closed'] || $q['closed']))
                            incomplete
                    @elseif($q['answered'])
                                        complete
                    @endif
                    ">
                <section wire:key="nav_{{$key}}"
                         class="question-number rounded-full text-center cursor-pointer
                                {!! $key === ($this->q - 1) ? 'active' : ''!!}
                                @if (!$q['answered'] && ($q['group']['closed'] || $q['closed']))
                                    incomplete
                                @elseif($q['answered'])
                                    complete
                                @endif
                                 "
                         wire:click="goToQuestion({{ 1+$key}})"

                         {{--                 wire:click="$set('q',{{ 1+$key}})"--}}
                         {{--                 x-on:click="$dispatch('current-updated', {'current': {{ 1+$key }} })"--}}

                         x-on:current-question-answered.window="$wire.updateQuestionIndicatorColor()"
                >
                    <span class="align-middle">{{ ++$key }}</span>
                </section>
                <div class="h-max-h-4 ml-2 mt-1 flex">
                    @if($q['closeable'] && !$q['closed'])
                        <x-icon.unlocked/>
                    @elseif($q['closed'])
                        <x-icon.locked/>
                    @endif
                </div>
            </div>

            @if($q['group']['closeable'] && $this->lastQuestionInGroup[$q['group']['id']] === $key)
                <div class="mr-3">
                    @if($q['group']['closed'])
                        <x-icon.locked/>
                    @else
                        <x-icon.unlocked/>
                    @endif
                </div>
            @endif
        @endforeach
            <div class="flex space-x-6 ml-auto min-w-max justify-end items-center">
            @if(Auth::user()->text2speech)
                <div id="__ba_panel"></div>
                <x-button.text-button @click="toggleBrowseAloud()">
                    <x-icon.audio/>
                    <span>{{ __('test_take.speak') }}</span>
                </x-button.text-button>
            @endif
            <x-button.text-button wire:click="toOverview({{ $this->q }})">
                <x-icon.preview/>
                <span>{{ __('test_take.overview') }}</span>
            </x-button.text-button>
        </div>
    </div>

    @if(Auth::user()->text2speech)
        <script>
            function toggleBrowseAloud() {
                if (typeof BrowseAloud == 'undefined') {
                    var s = document.createElement('script');
                    s.src = 'https://www.browsealoud.com/plus/scripts/3.1.0/ba.js';
                    s.integrity = "sha256-VCrJcQdV3IbbIVjmUyF7DnCqBbWD1BcZ/1sda2KWeFc= sha384-k2OQFn+wNFrKjU9HiaHAcHlEvLbfsVfvOnpmKBGWVBrpmGaIleDNHnnCJO4z2Y2H sha512-gxDfysgvGhVPSHDTieJ/8AlcIEjFbF3MdUgZZL2M5GXXDdIXCcX0CpH7Dh6jsHLOLOjRzTFdXASWZtxO+eMgyQ=="
                    s.crossOrigin = 'anonymous';

                    document.getElementsByTagName('BODY')[0].appendChild(s);
                    waitForBrowseAloudAndThenRun();
                } else {
                    _toggleBA();
                }
            }

            var _baTimer;

            function waitForBrowseAloudAndThenRun() {
                if (typeof BrowseAloud == 'undefined' || BrowseAloud.panel == 'undefined' || typeof BrowseAloud.panel.toggleBar == 'undefined') {
                    _baTimer = setTimeout(function () {
                            waitForBrowseAloudAndThenRun();
                        },
                        150);
                } else {
                    clearTimeout(_baTimer);
                    _toggleBA();
                }
            }

            function _toggleBA() {
                BrowseAloud.panel.toggleBar(!0);
            }
        </script>
    @endif
</div>
