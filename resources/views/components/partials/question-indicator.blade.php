<div class="question-indicator w-full" id="navigation-container">
    <div class="flex-col"
{{--         x-data="{ showSlider: false, scrollStep: 100, totalScrollWidth: 0, activeQuestion: @entangle('q') }"--}}
         x-data="questionIndicator"
         x-ref="questionindicator"
         x-global="indicatorData"
         x-init="$nextTick(() => {
                    $dispatch('current-updated', {'current': activeQuestion });
                    navScrollBar.querySelector('#active').scrollIntoView({behavior: 'smooth'});
                     totalScrollWidth = $refs.navscrollbar.offsetWidth;
                     navigationResizer.resize(indicatorData);
                    });
                 "
         x-on:resize.window.debounce.250ms="navigationResizer.resize(indicatorData);"
         x-on:current-updated.window="navScrollBar.querySelector('#active').scrollIntoView({behavior: 'smooth'});"
         x-cloak
    >
        <div class="flex">
            <div class="flex slider-buttons relative -top-px z-10" x-ref="sliderbuttons" x-show="showSlider">
                <button class="inline-flex base rotate-svg-180 w-8 h-8 rounded-full transition items-center justify-center transform focus:outline-none"
                        x-on:click="$refs.navscrollbar.scrollTo({left: 0,behavior: 'smooth'});startIntersectionCountdown()">
                    <x-icon.arrow-last/>
                </button>
                <button class="inline-flex base rotate-svg-180 w-8 h-8 rounded-full transition items-center justify-center transform focus:outline-none"
                        x-on:click="$refs.navscrollbar.scrollTo({left: $refs.navscrollbar.scrollLeft - scrollStep,behavior: 'smooth'});startIntersectionCountdown()">
                    <x-icon.chevron/>
                </button>
            </div>
            <div id="navscrollbar" class="flex pl-2.5" :class="{'overflow-x-auto' : showSlider}" x-ref="navscrollbar">
                @foreach($nav as $key => $q)
                    <div id="{!! $key === ($this->q - 1) ? 'active' : ''!!}"
                         class="flex flex-col mb-3 relative
                        @if($q['group']['id'] != 0 && !$loop->last && $nav[$key+1]['group']['id'] != 0 && $nav[$key+1]['group']['id'] === $q['group']['id'])
                                 number-divider group
                         @endif
                         @if (!$q['answered'] && ($q['group']['closed'] || $q['closed']))
                                 incomplete
                         @elseif($q['answered'])
                                 complete
                         @endif
                                 "
                         wire:key="nav_circle_for_q_{{$q['id']}}"
                    >
                        <section selid="testtake-navitem" wire:key="nav_item{{$q['id']}}"
                                 class="question-number rounded-full text-center cursor-pointer flex items-center justify-center
                                    {!! $key === ($this->q - 1) ? 'active' : ''!!}
                                 @if (!$q['answered'] && ($q['group']['closed'] || $q['closed']))
                                         incomplete
                                 @elseif($q['answered'])
                                         complete
                                 @endif
                                         "
                                 id="nav_item_{{ 1+$key}}"
                                 wire:click="goToQuestion({{ 1+$key}})"
                                 @if($this->isOverview)
                                 @click="$dispatch('show-loader')"
                                @endif
                        >
                            <span id="nav_{{$q['id']}}" wire:key="nav_{{$q['id']}}"
                                  class="align-middle px-1.5">{{ ++$key }}</span>
                        </section>
                        <div class="max-h-4 flex justify-center -ml-2 mt-1">
                            @if(($q['closeable']||$q['closeable_audio']) && !$q['closed'])
                                <x-icon.unlocked/>
                            @elseif($q['closed'])
                                <x-icon.locked/>
                            @endif
                        </div>
                    </div>

                    @if($q['group']['closeable'] && $this->lastQuestionInGroup[$q['group']['id']] === $key)
                        <div class="mr-3 @if($loop->last) pr-3 @endif">
                            @if($q['group']['closed'])
                                <x-icon.locked/>
                            @else
                                <x-icon.unlocked/>
                            @endif
                        </div>
                    @endif
                @endforeach

            </div>
            <div class="flex slider-buttons relative -top-px z-10" x-ref="sliderbuttons" x-show="showSlider">
                <button class="inline-flex base w-8 h-8 rounded-full transition items-center justify-center transform focus:outline-none"
                        x-on:click="$refs.navscrollbar.scrollTo({left: $refs.navscrollbar.scrollLeft + scrollStep,behavior: 'smooth'});startIntersectionCountdown()">
                    <x-icon.chevron/>
                </button>
                <button class="inline-flex base w-8 h-8 rounded-full transition items-center justify-center transform focus:outline-none"
                        x-on:click="$refs.navscrollbar.scrollTo({left: totalScrollWidth,behavior: 'smooth'});startIntersectionCountdown()">
                    <x-icon.arrow-last/>
                </button>
            </div>
        </div>

        @push('scripts')
            <script>
                let intersectionCountdown;
                let navScrollBar = document.getElementById('navscrollbar');
                let navScrollBarOffset = navScrollBar.getBoundingClientRect().left;

                function startIntersectionCountdown() {
                    clearInterval(intersectionCountdown);
                    let seconds = 0;
                    intersectionCountdown = setInterval(function () {
                        if (seconds === 5) {
                            clearInterval(intersectionCountdown);
                            let left = navScrollBar.querySelector('#active').offsetLeft;
                            navScrollBar.scrollTo({left: left - navScrollBarOffset, behavior: 'smooth'});
                        }
                        seconds++;
                    }, 1000)
                }

                const navigationResizer = {
                    resize: function (object) {
                        object.scrollStep = window.innerWidth / 10;
                        var sliderButtons = object.$refs.sliderbuttons.offsetWidth * 2
                        object.showSlider = (object.$refs.navscrollbar.offsetWidth + sliderButtons) >= object.$refs.questionindicator.offsetWidth;
                    }
                }
            </script>
        @endpush

        <div class="flex space-x-6 ml-auto min-w-max justify-end items-center">
            @if(Auth::user()->text2speech)
                <div id="__ba_launchpad" class="hidden"></div>
                <x-button.text-button @click="toggleBrowseAloud()">
                    <x-icon.audio/>
                    <span>{{ __('test_take.speak') }}</span>
                </x-button.text-button>
            @endif
            @if(!$isOverview)
                <x-button.text-button
                    onclick="typeof toOverview === 'function' ? toOverview({{$this->q}}) :
                        livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('toOverview', {{$this->q}})
                        "
                    @click="$dispatch('show-loader')"
                    >
                    <x-icon.preview/>
                    <span>{{ __('test_take.overview') }}</span>
                </x-button.text-button>
            @endif
        </div>
    </div>

    @if(Auth::user()->text2speech)
        @push('styling')
            <style>
                #th_toolbar{
                    display:none;
                }
            </style>
        @endpush

            @push('scripts')
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

                    var hideButtonsFound = false;
                    var hideButtonsIterator = 0;
                    function hideBrowseAloudButtons() {
                        var shadowRoot = document.querySelector('div#__bs_entryDiv').querySelector('div').shadowRoot;
                        var elementsToHide = ['th_translate', 'th_mp3Maker', 'ba-toggle-menu'];
                        var nrButtonsFound = 0;
                        elementsToHide.forEach(function (id) {
                            var el = shadowRoot.getElementById(id);
                            if (el !== null) {
                                shadowRoot.getElementById(id).setAttribute('style', 'display:none');
                                nrButtonsFound++;
                            }
                        });

                        if(nrButtonsFound === elementsToHide.length){
                            hideButtonsFound = true;
                        }

                        var toolbar = shadowRoot.getElementById('th_toolbar');
                        if (toolbar !== null) {
                            toolbar.setAttribute('style', 'background-color: #fff;display:inline-block');
                        }

                        [...shadowRoot.querySelectorAll('.th-browsealoud-toolbar-button__icon')].forEach(function (item) {
                            item.setAttribute('style', 'fill : #515151');
                        });
                        if(!hideButtonsFound && hideButtonsIterator < 20){
                            setTimeout(function(){
                                hideButtonsIterator++;
                                hideBrowseAloudButtons();
                            },250);
                        }
                    }

                    var _baTimer;
                    var tryIterator = 0;

                    function waitForBrowseAloudAndThenRun() {
                        if (typeof BrowseAloud == 'undefined' || BrowseAloud.panel == 'undefined' || typeof BrowseAloud.panel.toggleBar == 'undefined') {
                            _baTimer = setTimeout(function () {
                                    waitForBrowseAloudAndThenRun();
                                },
                                150);
                        } else {
                            clearTimeout(_baTimer);
                            try {
                                _toggleBA();
                            } catch (e) {
                                tryIterator++;
                                if (tryIterator < 20) { // just stop when it still fails after 20 tries;
                                    setTimeout(function () {
                                            waitForBrowseAloudAndThenRun();
                                        },
                                        150);
                                }
                            }
                        }
                    }

                    function _toggleBA() {
                        BrowseAloud.panel.toggleBar(!0);
                        setTimeout(function () {
                            hideBrowseAloudButtons();
                        }, 1000);
                    }

                </script>
            @endpush
    @endif
    
</div>
