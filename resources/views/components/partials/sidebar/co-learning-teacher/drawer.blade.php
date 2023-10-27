<div class="drawer flex z-[20] overflow-hidden flex-shrink-0"
     selid="co-learning-teacher-drawer"
     x-init="
        collapse = window.innerWidth < 1000;
     "
     x-data="{
        collapse: false,
        fillSpaceBetweenElementsHorizontal: (xref1, xref2) => {
            element1 = document.querySelector('[x-ref=\'' + xref1 + '\']');
            element2 = document.querySelector('[x-ref=\'' + xref2 + '\']');

            remainingSpace = element2.offsetLeft - (element1.offsetLeft + element1.offsetWidth);
            element2.style.marginLeft = remainingSpace + 'px';
        },
        showToolTip: (tooltip) => {
            tooltip.style.display = 'block';
        },
        hideToolTip: (tooltip) => {
            tooltip.style.display = 'none';
        },
        setPositionToolTip: (tooltip, event) => {
            if(tooltip.style.display === 'none') {
                return false;
            }

            if(event.y + 100 >= window.innerHeight) {
                tooltip.style.top = (event.y - 75) + 'px';
                tooltip.style.left = (event.x + 20)  + 'px';
            } else {
                tooltip.style.top = (event.y + 20) + 'px';
                tooltip.style.left = (event.x + 20)  + 'px';
            }
        }
     }"
     x-cloak
     :class="{'collapsed': collapse}"
>
    <div class="collapse-toggle vertical white z-10 cursor-pointer"
         @click="collapse = !collapse "
    >
        <button class="relative"
                :class="{'rotate-svg-180 -left-px': !collapse}"
        >
            <x-icon.chevron class="-top-px relative"/>
        </button>
    </div>
    <div class="flex flex-col w-full justify-between h-[calc(100vh-var(--header-height))] drawer-width"
         wire:key="{{ $attributes->get('wire:key') }}"
    >
        <div class="flex flex-col ">
            <div class="flex justify-between drawer-content-head border-b border-bluegrey"
                 x-init="$nextTick(() => {fillSpaceBetweenElementsHorizontal('drawerContentHeadText1', 'drawerContentHeadText2');})"
                 wire:key="{{ now()->timestamp }}"
            >
                <div x-ref="drawerContentHeadText1" class="flex">
                    <span class="bold">@lang('co-learning.present') {{ $this->testParticipantCountActive }}</span>
                    <span>/{{ $this->testParticipantCount }}</span>
                </div>
                <div x-ref="drawerContentHeadText2" class="flex">
                    <span class="bold">{{ strtolower(__('co-learning.question')) }} {{ $this->questionIndex }}</span>
                    <span>/{{  $this->questionCountFiltered }}</span>
                </div>
            </div>

            <div class="drawer-content overflow-auto">
                @foreach($this->testParticipants as $testParticipant)
                    @if($this->testParticipantIsActive($testParticipant))
                        <x-partials.sidebar.co-learning-teacher.student-info-container
                                :testParticipant="$testParticipant"
                                :activeAnswerRating="$activeAnswerRating"
                        />
                    @endif
                @endforeach
            </div>
        </div>

        <div class="bottom-0 drawer-footer flex justify-between items-center footer-shadow flex-shrink-0"
             x-init="$nextTick(() => {fillSpaceBetweenElementsHorizontal('footerElement1', 'footerElement2');})"
        >
            <x-button.text wire:click.prevent="goToPreviousQuestion"
                                  @click="resetToggles"
                                  wire:key="previousQuestion.{{$this->testTake->discussing_question_id}}"
                                  :disabled="$this->atFirstQuestion"
                                  class="flex-shrink-0"
                                  x-ref="footerElement1"
                                  wire:loading.attr="disabled"
            >
                <x-icon.arrow-left/>
                <span>{{ __('co-learning.previous') }}</span>
            </x-button.text>
            <x-button.primary wire:click.prevent="goToNextQuestion"
                              @click="resetToggles"
                              wire:key="lastQuestion.{{$this->testTake->discussing_question_id}}"
                              :disabled="$this->atLastQuestion"
                              x-ref="footerElement2"
                              wire:loading.attr="disabled"
            >
                <span>{{ __('co-learning.next') }}</span>
                <x-icon.arrow/>
            </x-button.primary>
        </div>
    </div>
    <!-- Well begun is half done. - Aristotle -->
</div>