<x-layouts.app>
    <div class="w-full flex flex-col mb-5 overview"
         x-data="{marginTop: 0}"
         x-on:unload="(function () {window.scrollTo(0, 0);})"
         x-cloak
    >
        <div class="fixed left-0 w-full px-8 xl:px-28 flex-col pt-4 z-10 bg-light-grey" id="overviewQuestionNav">
            <div>
                <livewire:overview.navigation :nav="$nav" :testTakeUuid="$uuid" :playerUrl="$playerUrl"/>
            </div>

            <div class="nav-overflow left-0 fixed w-full h-12"></div>
        </div>
        <div class="w-full space-y-8 mt-40" :style="calculateMarginTop()">
            <h1 class="mb-7">{{ __('test_take.overview_review_answers') }}</h1>
            @foreach($data as  $key => $testQuestion)
                <div class="flex flex-col space-y-4">
                    @if($testQuestion->type === 'MultipleChoiceQuestion' && $testQuestion->selectable_answers > 1)
                        <livewire:overview.multiple-select-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MultipleChoiceQuestion')
                        <livewire:overview.multiple-choice-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'OpenQuestion')
                        <livewire:overview.open-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'q-'"
                        />
                    @elseif($testQuestion->type === 'MatchingQuestion')
                        <livewire:overview.matching-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'CompletionQuestion')
                        <livewire:overview.completion-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'RankingQuestion')
                        <livewire:overview.ranking-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'InfoscreenQuestion')
                        <livewire:overview.info-screen-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'DrawingQuestion')
                        <livewire:overview.drawing-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'MatrixQuestion')
                        <livewire:overview.matrix-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @endif

                    @if($testQuestion->type != 'InfoscreenQuestion')
                        <div class="flex">
                            @if(!$nav[$key-1]['closed'] && !$nav[$key-1]['group']['closed'])
                                <x-button.primary onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('goToQuestion',{{ $key }})"
                                                  class="ml-auto">{!!__('test_take.adjust_answer') !!}</x-button.primary>
                            @else
                                <span class="text-sm note w-60 ml-auto text-right">{{ __('test_take.question_closed_text_short') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>


        <x-slot name="footerbuttons">
            <x-button.text-button type="link"
                                  href="{{ $playerUrl }}?q=1"
                                  rotateIcon="180"
            >
                <x-icon.chevron/>
                <span>{{ __('test_take.back_to_questions') }}</span></x-button.text-button>
            <x-button.cta size="sm"
                          onclick="livewire.find(document.querySelector('[testtakemanager]').getAttribute('wire:id')).call('turnInModal')">
                <span>{{ __('test_take.turn_in') }}</span>
            </x-button.cta>
        </x-slot>
        <x-slot name="testTakeManager">
            <livewire:student.test-take :testTakeUuid="$uuid" :testParticipantId="$testParticipant->getKey()"/>
        </x-slot>
        <x-slot name="fraudDetection">
            <livewire:student.fraud-detection :testParticipantId="$testParticipant->getKey()"/>
        </x-slot>
    </div>
    @push('scripts')
    <script>
        function calculateMarginTop() {
            var questionNav = document.getElementById('overviewQuestionNav').offsetHeight;
            var shadow = 48;
            var total = questionNav+shadow;
            return 'margin-top:' + total +'px';
        }
    </script>
    @endpush
</x-layouts.app>

