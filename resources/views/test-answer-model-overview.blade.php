<x-layouts.pdf >
    <div class="w-full flex flex-col mb-5 overview pdf-answer-model"
         x-data="{marginTop: 0}"
         x-on:unload="(function () {window.scrollTo(0, 0);})"
         x-cloak
    >

        <div x-data="{showMe: true}"
             x-show="showMe"
             x-on:force-taken-away-blur.window="showMe = !$event.detail.shouldBlur;"
             class="w-full space-y-8 mt-10" :style="calculateMarginTop()">
            @push('styling')
                <style>
                    {!! $styling !!}
                </style>
            @endpush
            @foreach($data as  $key => $testQuestion)
                <div class="flex flex-col space-y-4">
                    @if($testQuestion->type === 'MultipleChoiceQuestion' && $testQuestion->selectable_answers > 1 && $testQuestion->subtype != 'ARQ')
                        <livewire:answer-model.multiple-select-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MultipleChoiceQuestion')
                        <livewire:answer-model.multiple-choice-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'OpenQuestion')
                        <livewire:answer-model.open-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MatchingQuestion')
                        <livewire:answer-model.matching-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'CompletionQuestion')
                        <livewire:answer-model.completion-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'RankingQuestion')
                        <livewire:answer-model.ranking-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'InfoscreenQuestion')
                        <livewire:answer-model.info-screen-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'DrawingQuestion')
                        <livewire:answer-model.drawing-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'MatrixQuestion')
                        <livewire:answer-model.matrix-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'RelationQuestion')
                        <livewire:answer-model.relation-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @endif
                </div>
            @endforeach
        </div>
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
</x-layouts.pdf>
