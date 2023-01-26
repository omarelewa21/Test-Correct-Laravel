<div class="drawer flex z-[20] overflow-hidden flex-shrink-0"
     selid="co-learning-teacher-drawer"
     x-init="
        collapse = window.innerWidth < 1000;
     "
     x-data="{
        collapse: false,
        fillSpaceBetweenElementsHorizontal: (element1, element2) => {
            remainingSpace = element2.offsetLeft - (element1.offsetLeft + element1.offsetWidth);
            element2.style.marginLeft = remainingSpace + 'px';
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
    <div class="flex flex-col w-full justify-between h-[calc(100vh-70px)] drawer-width">
        <div class="flex flex-col ">

            <div class="flex justify-between drawer-content-head border-b border-bluegrey"
                 x-init="$nextTick(() => {fillSpaceBetweenElementsHorizontal($refs.drawerContentHeadText1, $refs.drawerContentHeadText2);})"
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

                @foreach($this->testTake->testParticipants as $testParticipant)
                    @if($testParticipant->active)
                        <x-partials.sidebar.co-learning-teacher.student-info-container
                                :testParticipant="$testParticipant"
                        ></x-partials.sidebar.co-learning-teacher.student-info-container>
                    @endif
                @endforeach

                <div @click="showStudentAnswer = true"
                     wire:click.prevent="showStudentAnswer('{{ 535/*$testParticipant->discussing_answer_rating_id*/ }}')">jup</div>

            </div>
        </div>

        <div class="bottom-0 drawer-footer flex justify-between items-center footer-shadow flex-shrink-0"
             x-init="$nextTick(() => {fillSpaceBetweenElementsHorizontal($refs.footerElement1, $refs.footerElement2);})"
        >
            <x-button.text-button wire:click.prevent="goToPreviousQuestion"
                                  wire:key="previousQuestion.{{$this->testTake->discussing_question_id}}"
                                  :disabled="$this->atFirstQuestion"
                                  class="flex-shrink-0"
                                  x-ref="footerElement1"
            >
                <x-icon.arrow-left/>
                <span class="ml-2">{{ __('co-learning.previous') }}</span>
            </x-button.text-button>
            <x-button.primary wire:click.prevent="goToNextQuestion"
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