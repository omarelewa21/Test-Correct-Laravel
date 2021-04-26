<div class="question-indicator w-full">
    <div class="flex-col"
         x-data="{ showSlider: false, scrollStep: 100 }"
         x-ref="questionindicator"
         x-init="setTimeout( function() { $dispatch('current-updated', {'current': {{ $this->q }} })}, 1);
                navigationResizer.resize($data);
                "
         x-on:resize.window.debounce.250ms="navigationResizer.resize($el.__x.$data);"
         x-on:current-updated.window="
               if(typeof objectToObserve !== 'undefined') {
                    myIntersectionObserver.unobserve(objectToObserve);
               }
                objectToObserve = document.getElementById('active');
                myIntersectionObserver.observe(objectToObserve);
                objectToObserve.scrollIntoView({behavior: 'smooth', block: 'end', inline: 'center'});
            "
         x-cloak
    >
        <div class="flex">
            <div class="flex slider-buttons relative -top-px z-10" x-ref="sliderbuttons" x-show="showSlider">
                <button class="inline-flex base rotate-svg-180 w-8 h-8 hover:bg-white rounded-full transition items-center justify-center transform hover:scale-110"
                        @click="$refs.navscrollbar.scrollTo({left: 0,behavior: 'smooth'})">
                    <x-icon.arrow-last/>
                </button>
                <button class="inline-flex base rotate-svg-180 w-8 h-8 hover:bg-white rounded-full transition items-center justify-center transform hover:scale-110"
                        @click="$refs.navscrollbar.scrollTo({left: $refs.navscrollbar.scrollLeft - scrollStep,behavior: 'smooth'});">
                    <x-icon.chevron/>
                </button>
            </div>
            <div id="navscrollbar" class="flex pl-2.5" :class="{'overflow-x-auto' : showSlider}" x-ref="navscrollbar">

                @foreach($nav as $key => $q)
                    <div id="{!! $key === ($this->q - 1) ? 'active' : ''!!}"
                         class="flex flex-col mb-3 relative @if(!$loop->last && $q['is_subquestion'] == 1 && $this->groupQuestionArray[$q['id']] == $this->groupQuestionArray[$nav[$key+1]['id']]) number-divider group @endif">

                        <section wire:key="nav_{{$key}}"
                                 class="question-number rounded-full text-center cursor-pointer flex items-center justify-center
                                    {!! $key === ($this->q - 1) ? 'active' : ''!!}"
                                 wire:click="goToQuestion({{ 1+$key}})"
                                 x-on:current-question-answered.window="$wire.updateQuestionIndicatorColor()"
                        >
                            <span class="align-middle px-1.5">{{ ++$key }}</span>
                        </section>
                        <div class="max-h-4 flex justify-center -ml-2 mt-1">
                            @if($q['closeable'])
                                <x-icon.unlocked/>
                            @endif
                        </div>
                    </div>
                    @if($this->groupQuestionArray[$q['id']] != 0 && array_key_exists($this->groupQuestionArray[$q['id']], $this->closeableGroups) && $q['id'] == $this->lastQuestionInGroup[$this->groupQuestionArray[$q['id']]])
                        <div class="mr-3 @if($loop->last) pr-3 @endif">
                                <x-icon.unlocked/>
                        </div>
                    @endif
                @endforeach

            </div>
            <div class="flex slider-buttons relative -top-px z-10" x-ref="sliderbuttons" x-show="showSlider">
                <button class="inline-flex base w-8 h-8 hover:bg-white rounded-full transition items-center justify-center transform hover:scale-110"
                        @click="$refs.navscrollbar.scrollTo({left: $refs.navscrollbar.scrollLeft + scrollStep,behavior: 'smooth'})">
                    <x-icon.chevron/>
                </button>
                <button class="inline-flex base w-8 h-8 hover:bg-white rounded-full transition items-center justify-center transform hover:scale-110"
                        @click="$refs.navscrollbar.scrollTo({left: $refs.navscrollbar.offsetWidth,behavior: 'smooth'})">
                    <x-icon.arrow-last />
                </button>
            </div>
        </div>
        @push('scripts')
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
                rootMargin: '9999px 0px 9999px 0px',
                threshold: 1
            });

            const navigationResizer = {
                resize: function(object) {
                    object.scrollStep = window.innerWidth/10;
                    var sliderButtons = object.$refs.sliderbuttons.offsetWidth*2
                    object.showSlider = (object.$refs.navscrollbar.offsetWidth + sliderButtons) >= object.$refs.questionindicator.offsetWidth;
                }
            }
        </script>
        @endpush
    </div>
</div>
