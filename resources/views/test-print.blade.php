<x-layouts.pdf >
    <div class="w-full flex flex-col mb-5 overview"
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
                        <livewire:test-print.multiple-select-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MultipleChoiceQuestion')
                        <livewire:test-print.multiple-choice-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'OpenQuestion')
                        <livewire:test-print.open-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MatchingQuestion')
                        <livewire:test-print.matching-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'CompletionQuestion')
                        <livewire:test-print.completion-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'RankingQuestion')
                        <livewire:test-print.ranking-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'InfoscreenQuestion')
                        <livewire:test-print.info-screen-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'DrawingQuestion')
                        <livewire:test-print.drawing-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'MatrixQuestion')
                        <livewire:test-print.matrix-question
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
