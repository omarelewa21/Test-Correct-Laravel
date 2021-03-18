<div class="question-indicator w-full">
    <div class="@if($this->useSlider) flex-col @else flex flex-wrap @endif"
         x-data=""
         x-init="setTimeout( function() { $dispatch('current-updated', {'current': {{ $this->q }} })}, 1);"
         x-on:current-updated.window="
               if(typeof objectToObserve !== 'undefined') {
                    myIntersectionObserver.unobserve(objectToObserve);
               }
                objectToObserve = document.getElementById('active');
                myIntersectionObserver.observe(objectToObserve);
                objectToObserve.scrollIntoView({behavior: 'smooth', block: 'end', inline: 'center'});
            "
    >
        @if($this->useSlider)
            <div class="flex">
                <div class="flex justify-center slider-buttons relative pr-3 -right-3">
                    <button class="inline-flex base py-1 rotate-svg-180 w-8 h-8 hover:bg-white rounded-full transition items-center justify-center"
                            @click="$refs.navscrollbar.scrollTo({left: 0,behavior: 'smooth'})">
                        <x-icon.arrow/>
                    </button>
                    <button class="inline-flex base px-3 py-1 rotate-svg-180 w-8 h-8 hover:bg-white rounded-full transition items-center justify-center"
                            @click="$refs.navscrollbar.scrollTo({left: $refs.navscrollbar.scrollLeft - 100,behavior: 'smooth'})">
                        <x-icon.chevron/>
                    </button>
                </div>

                <div id="navscrollbar" class="flex overflow-hidden mx-4" x-ref="navscrollbar">

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

                </div>
                <div class="flex justify-center slider-buttons relative pl-3 -left-3">
                    <button class="inline-flex base px-3 py-1 w-8 h-8 hover:bg-white rounded-full transition items-center justify-center"
                            @click="$refs.navscrollbar.scrollTo({left: $refs.navscrollbar.scrollLeft + 100,behavior: 'smooth'})">
                        <x-icon.chevron/>
                    </button>
                    <button class="inline-flex base  py-1 w-8 h-8 hover:bg-white rounded-full transition items-center justify-center"
                            @click="$refs.navscrollbar.scrollTo({left: $refs.navscrollbar.offsetWidth,behavior: 'smooth'})">
                        <x-icon.arrow/>
                    </button>
                </div>

            </div>
        @else

            @foreach($nav as $key => $q)
                <div id="item-{{$key}}" class="flex flex-col mb-3 relative
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

        @endif

        <script>
            let timer

            function callback(entries) {

                for (const entry of entries) {
                    if (!entry.isIntersecting) {
                        timer = setTimeout(function () {
                            entry.target.scrollIntoView({behavior: 'smooth', block: 'end', inline: 'center'});
                        }, 5000)
                    } else {
                        clearTimeout(timer);
                    }
                }
            }

            const myIntersectionObserver = new IntersectionObserver(callback, {
                root: document.getElementById('navscrollbar'),
                rootMargin: '9999px 15px 9999px 15px',
                threshold: 1
            });
        </script>


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

            function hideBrowseAloudButtons() {
                var shadowRoot = document.querySelector('div#__bs_entryDiv').querySelector('div').shadowRoot;
                var elementsToHide = ['th_translate', 'th_mp3Maker', 'ba-toggle-menu'];
                elementsToHide.forEach(function (id) {
                    var el = shadowRoot.getElementById(id);
                    if (el !== null) {
                        shadowRoot.getElementById(id).setAttribute('style', 'display:none');
                    }
                });

                var toolbar = shadowRoot.getElementById('th_toolbar');
                if (toolbar !== null) {
                    toolbar.setAttribute('style', 'background-color: #fff');
                }

                [...shadowRoot.querySelectorAll('.th-browsealoud-toolbar-button__icon')].forEach(function (item) {
                    item.setAttribute('style', 'fill : #515151');
                });
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
                        if (tryIterator < 10) { // just stop when it still fails after 10 tries;
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
    @endif
</div>
