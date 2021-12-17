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
                            @if($q['closeable'] && !$q['closed'])
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
                <div id="readspeaker_button1" wire:ignore class="rs_skip rsbtn rs_preserve ">
                    <a rel="nofollow" class="rsbtn_play" accesskey="L" title="{{ __('test_take.speak') }}" href="//app-eu.readspeaker.com/cgi-bin/rsent?customerid=12749&amp;lang=nl_nl&amp;readclass=rs_readable">
                        <span class="rsbtn_left rsimg rspart"><x-icon.audio/><span class="rsbtn_text"><span class="rsbtn_label">{{ __('test_take.speak') }}</span></span></span>
                        <span class="rsbtn_right rsimg rsplay rspart"></span>
                    </a>
                </div>
            @endif
            @if(!$isOverview)
                <x-button.text-button wire:click="toOverview({{ $this->q }})" @click="$dispatch('show-loader')">
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
                window.addEventListener('update-footer-navigation', event => {
                    if (typeof rspkr != 'undefined' && rspkr.ui.getActivePlayer()) {
                            rspkr.ui.getActivePlayer().close();
                    }
                });
            </script>
        @endpush
    @endif
</div>
