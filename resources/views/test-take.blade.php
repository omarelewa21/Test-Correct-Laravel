<x-layouts.app>
    <div class="w-full flex flex-col mb-5"  selid="testtake-layout">
        @if($testParticipant->intense)
            <livewire:student.intense-observer :deviceId="$testParticipant->user_id" :sessionId="$testParticipant->id"></livewire:student.intense-observer>
        @endif
        <livewire:question.navigation  :nav="$nav" :testTakeUuid="$uuid"/>
        <div >
            @push('styling')
                <style>
                    {!! $styling !!}
                </style>
            @endpush
            @foreach($data as  $key => $testQuestion)
                <div selid="testtake-question">
                    @if($testQuestion->type === 'MultipleChoiceQuestion' && $testQuestion->selectable_answers > 1 && $testQuestion->subtype != 'ARQ')
                        <livewire:question.multiple-select-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MultipleChoiceQuestion')
                        <livewire:question.multiple-choice-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'OpenQuestion')
                        <livewire:question.open-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'q-'"
                        />
                    @elseif($testQuestion->type === 'MatchingQuestion')
                        <livewire:question.matching-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'CompletionQuestion')
                        <livewire:question.completion-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'RankingQuestion')
                        <livewire:question.ranking-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'InfoscreenQuestion')
                        <livewire:question.info-screen-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'DrawingQuestion')
                        <livewire:question.drawing-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'MatrixQuestion')
                        <livewire:question.matrix-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid"
                        />
                    @endif
                </div>
            @endforeach
        </div>
        <x-slot name="footerbuttons">
            <div x-cloak x-data="{display :footerButtonData({{ $current }}, {{$nav->count()}})}" @update-footer-navigation.window="display= $event.detail.data" class="space-x-3">
                <x-button.text-button x-show="display.prev"
                        onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('previousQuestion')"
                        href="#" rotateIcon="180">
                    <x-icon.chevron/>
                    <span>{{ __('test_take.previous_question') }}</span>
                </x-button.text-button>
                <x-button.cta x-show="display.turnin"
                        size="sm"
                        onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('toOverview', {{ $nav->count() }})"
                        @click="$dispatch('show-loader')"
                >
                    <span>{{ __('test_take.overview') }}</span>
                </x-button.cta>
                <x-button.primary x-show="display.next"
                        onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('nextQuestion')"
                        size="sm">
                    <span>{{ __('test_take.next_question') }}</span>
                    <x-icon.chevron/>
                </x-button.primary>
            </div>
        </x-slot>
        <x-slot name="testTakeManager">
            <livewire:student.test-take :testTakeUuid="$uuid" :testParticipantId="$testParticipant->getKey()" :testParticipantUuid="$testParticipant->uuid"/>
        </x-slot>
        <x-slot name="fraudDetection">
            <livewire:student.fraud-detection :testParticipantId="$testParticipant->getKey()" :testParticipantUuid="$testParticipant->uuid" :testTakeUuid="$uuid"/>
        </x-slot>
    </div>
    @push('scripts')
    <script>
        function footerButtonData(q, last) {
            if (q === 1) {
                if (q === last) {
                    data = {
                        prev: false,
                        next: false,
                        turnin: true
                    }
                } else {
                    data = {
                        prev: false,
                        next: true,
                        turnin: false
                    }
                }
            } else if(q === last) {
                data = {
                    prev: true,
                    next: false,
                    turnin: true
                }
            } else {
                data = {
                    prev: true,
                    next: true,
                    turnin: false
                }
            }
            return data;
        }
    </script>
    @endpush
</x-layouts.app>

