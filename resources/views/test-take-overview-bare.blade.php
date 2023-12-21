

        <div x-data="{showMe: true}"
             x-show="showMe"
             x-on:force-taken-away-blur.window="showMe = !$event.detail.shouldBlur;"
             class="w-full space-y-8 mt-40 page-break-before-pdf" :style="calculateMarginTop()">
            <h4 class="mb-7">{{ $studentName }}</h4>
            @push('styling')
                <style>
                    {!! $styling !!}
                </style>
            @endpush
            @foreach($data as  $key => $testQuestion)
                <div class="flex flex-col space-y-4">
                    @if($testQuestion->type === 'MultipleChoiceQuestion' && $testQuestion->selectable_answers > 1 && $testQuestion->subtype != 'ARQ')
                        <livewire:test-take-overview-preview.multiple-select-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                                :showQuestionText="$showQuestionText"
                        />
                    @elseif($testQuestion->type === 'MultipleChoiceQuestion')
                        <livewire:test-take-overview-preview.multiple-choice-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                                :showQuestionText="$showQuestionText"
                        />
                    @elseif($testQuestion->type === 'OpenQuestion')
                        <livewire:test-take-overview-preview.open-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'q-'"
                                :showQuestionText="$showQuestionText"
                        />
                    @elseif($testQuestion->type === 'MatchingQuestion')
                        <livewire:test-take-overview-preview.matching-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                                :showQuestionText="$showQuestionText"
                        />
                    @elseif($testQuestion->type === 'CompletionQuestion')
                        <livewire:test-take-overview-preview.completion-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                                :showQuestionText="$showQuestionText"
                        />
                    @elseif($testQuestion->type === 'RankingQuestion')
                        <livewire:test-take-overview-preview.ranking-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                                :showQuestionText="$showQuestionText"
                        />
                    @elseif($testQuestion->type === 'InfoscreenQuestion')
                        <livewire:test-take-overview-preview.info-screen-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid"
                                :showQuestionText="$showQuestionText"
                        />
                    @elseif($testQuestion->type === 'DrawingQuestion')
                        <livewire:test-take-overview-preview.drawing-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid"
                                :showQuestionText="$showQuestionText"
                        />
                    @elseif($testQuestion->type === 'MatrixQuestion')
                        <livewire:test-take-overview-preview.matrix-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid"
                                :showQuestionText="$showQuestionText"
                        />
                    @elseif($testQuestion->type === 'RelationQuestion')
                        <livewire:test-take-overview-preview.relation-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid"
                                :showQuestionText="$showQuestionText"
                        />
                    @endif


                </div>
            @endforeach
            <h2 class="student-testtake-answers-separator"><span class="student-testtake-answers-label">{{__('Einde antwoorden').' '.$studentName}}</span></h2>
        </div>







