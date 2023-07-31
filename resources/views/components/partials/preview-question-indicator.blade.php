<div class="question-indicator w-full">
    <div class="flex-col"
         x-data="questionIndicator"
         x-ref="questionindicator"
         x-global="indicatorData"
         x-init="$nextTick(() => {
                    $dispatch('current-updated', {'current': activeQuestion });
                    navScrollBar.querySelector('#active').scrollIntoView({behavior: 'smooth'});
                    totalScrollWidth = $refs.navscrollbar.offsetWidth;
                    navigationResizer.resize(indicatorData);
                    });"
         x-on:resize.window.debounce.250ms="navigationResizer.resize(indicatorData);"
         x-on:current-updated.window="navScrollBar.querySelector('#active').scrollIntoView({behavior: 'smooth'});"
         x-cloak
    >
        <div class="flex">
            <div class="flex slider-buttons relative -top-px z-10" x-ref="sliderbuttons" x-show="showSlider">
                <button class="inline-flex base rotate-svg-180 w-8 h-8 hover:bg-white rounded-full transition items-center justify-center transform hover:scale-110"
                        @click="$refs.navscrollbar.scrollTo({left: 0,behavior: 'smooth'});startIntersectionCountdown()">
                    <x-icon.arrow-last/>
                </button>
                <button class="inline-flex base rotate-svg-180 w-8 h-8 hover:bg-white rounded-full transition items-center justify-center transform hover:scale-110"
                        @click="$refs.navscrollbar.scrollTo({left: $refs.navscrollbar.scrollLeft - scrollStep,behavior: 'smooth'});startIntersectionCountdown()">
                    <x-icon.chevron/>
                </button>
            </div>
            <div id="navscrollbar" class="flex pl-2.5" :class="{'overflow-x-auto' : showSlider}" x-ref="navscrollbar">

                @foreach($nav as $key => $q)
                    <div id="{!! $key === ($this->q - 1) ? 'active' : ''!!}"
                         class="flex flex-col mb-3 relative @if(!$loop->last && $q['is_subquestion'] == 1 && $this->groupQuestionIdsForQuestions[$q['id']] == $this->groupQuestionIdsForQuestions[$nav[$key+1]['id']]) number-divider group @endif">

                        <section wire:key="nav_{{$key}}"
                                 class="question-number rounded-full text-center cursor-pointer flex items-center justify-center
                                    {!! $key === ($this->q - 1) ? 'active' : ''!!}"
                                 x-on:click="$store.studentPlayer.to({{ $key + 1 }}, activeQuestion)"
                        >
                            <span class="align-middle px-1.5">{{ ++$key }}</span>
                        </section>
                        <div class="max-h-4 flex justify-center -ml-2 mt-1">
                            @if($q['closeable']||$q['closeable_audio'])
                                <x-icon.unlocked/>
                            @endif
                        </div>
                    </div>
                    @if($this->groupQuestionIdsForQuestions[$q['id']] != 0
                        && array_key_exists($this->groupQuestionIdsForQuestions[$q['id']], $this->closeableGroups)
                        && $q['id'] == $this->lastQuestionInGroup[$this->groupQuestionIdsForQuestions[$q['id']]]
                        && $this->closeableGroups[$this->groupQuestionIdsForQuestions[$q['id']]]
                        )
                        <div class="mr-3 @if($loop->last) pr-3 @endif">
                            <x-icon.unlocked/>
                        </div>
                    @endif
                @endforeach

            </div>
            <div class="flex slider-buttons relative -top-px z-10" x-ref="sliderbuttons" x-show="showSlider">
                <button class="inline-flex base w-8 h-8 hover:bg-white rounded-full transition items-center justify-center transform hover:scale-110"
                        @click="$refs.navscrollbar.scrollTo({left: $refs.navscrollbar.scrollLeft + scrollStep,behavior: 'smooth'});startIntersectionCountdown()">
                    <x-icon.chevron/>
                </button>
                <button class="inline-flex base w-8 h-8 hover:bg-white rounded-full transition items-center justify-center transform hover:scale-110"
                        @click="$refs.navscrollbar.scrollTo({left: $refs.navscrollbar.offsetWidth,behavior: 'smooth'});startIntersectionCountdown()">
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
    </div>
</div>
