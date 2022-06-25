

        <div x-data="{showMe: true}"
             x-show="showMe"
             x-on:force-taken-away-blur.window="showMe = !$event.detail.shouldBlur;"
             class="w-full space-y-8 mt-40" :style="calculateMarginTop()">
            <h1 class="mb-7">{{ $studentName }}</h1>
            @push('styling')
                <style>
                    {!! $styling !!}
                </style>
            @endpush
            @foreach($data as  $key => $testQuestion)
                <div class="flex flex-col space-y-4">
                    @if($testQuestion->type === 'MultipleChoiceQuestion' && $testQuestion->selectable_answers > 1 && $testQuestion->subtype != 'ARQ')
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


                </div>
            @endforeach
        </div>







