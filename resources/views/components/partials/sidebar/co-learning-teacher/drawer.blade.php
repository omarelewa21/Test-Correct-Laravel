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
        console.log('hide');
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
     wire:key="{{ $attributes->get('wire:key') }}"
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
    <div class="flex flex-col w-full justify-between h-[calc(100vh-70px)] drawer-width">
        <div class="flex flex-col ">

            <div class="flex justify-between drawer-content-head border-b border-bluegrey"
                 x-init="$nextTick(() => {fillSpaceBetweenElementsHorizontal('drawerContentHeadText1', 'drawerContentHeadText2');})"
                 wire:key="{{ now()->timestamp }}"
            >
                <div x-ref="drawerContentHeadText1" class="flex">
                    <span class="bold">aanwezig {{ $this->testParticipantCountActive }}</span>
                    <span>/{{ $this->testParticipantCount }}</span>
                </div>
                <div x-ref="drawerContentHeadText2" class="flex">
                    <span class="bold">vraag {{ $this->openOnly ? $this->questionIndexOpenOnly : $this->questionIndex }}</span>
                    <span>/{{  $this->openOnly ? $this->questionCountOpenOnly : $this->questionCount }}</span>
                </div>
            </div>

            <div class="drawer-content divide-y divide-bluegrey overflow-auto">
                @foreach($this->testParticipants as $testParticipant)
                    @if($testParticipant->active)
                        <x-partials.sidebar.co-learning-teacher.student-info-container
                                :testParticipant="$testParticipant"
                        ></x-partials.sidebar.co-learning-teacher.student-info-container>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="bottom-0 drawer-footer flex justify-between items-center footer-shadow flex-shrink-0"
             x-init="$nextTick(() => {fillSpaceBetweenElementsHorizontal('footerElement1', 'footerElement2');})"
        >
            <x-button.text-button wire:click.prevent="goToPreviousQuestion"
                                  @click="resetToggles"
                                  wire:key="previousQuestion.{{$this->testTake->discussing_question_id}}"
                                  :disabled="$this->atFirstQuestion"
                                  class="flex-shrink-0"
                                  x-ref="footerElement1"
            >
                <x-icon.arrow-left/>
                <span class="ml-2">{{ __('co-learning.previous') }}</span>
            </x-button.text-button>
            <x-button.primary wire:click.prevent="goToNextQuestion"
                              @click="resetToggles"
                              wire:key="lastQuestion.{{$this->testTake->discussing_question_id}}"
                              :disabled="$this->atLastQuestion"
                              class="px-4 flex-0 flex-shrink-0"
                              x-ref="footerElement2"
            >
                <span class="mr-2">{{ __('co-learning.next') }}</span>
                <x-icon.arrow/>
            </x-button.primary>
        </div>
    </div>
    <!-- Well begun is half done. - Aristotle -->
</div>