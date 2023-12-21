<x-layouts.pdf-test-print>
    <div class="w-full flex flex-col mb-5 overview test-print-opgaven-pdf">
        <div class="w-full space-y-8 mt-10">
            @push('styling')
                <style>
                    {!! $styling !!}
                </style>
            @endpush
            @php $currentGroupQuestion = null; $questionFollowUpNumber = 1;@endphp
            @foreach($data as  $key => $testQuestion)
                <div class="flex flex-col space-y-4">
                    @if($testQuestion->type === 'MultipleChoiceQuestion' && $testQuestion->selectable_answers > 1 && $testQuestion->subtype != 'ARQ')
                        <livewire:test-opgaven-print.multiple-select-question
                                :question="$testQuestion"
                                :number="$questionFollowUpNumber++"
                                :test="$test"
                                :attachment_counters="$attachment_counters"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MultipleChoiceQuestion')
                        <livewire:test-opgaven-print.multiple-choice-question
                                :question="$testQuestion"
                                :number="$questionFollowUpNumber++"
                                :test="$test"
                                :attachment_counters="$attachment_counters"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'OpenQuestion')
                        <livewire:test-opgaven-print.open-question
                                :question="$testQuestion"
                                :number="$questionFollowUpNumber++"
                                :test="$test"
                                :attachment_counters="$attachment_counters"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MatchingQuestion')
                        <livewire:test-opgaven-print.matching-question
                                :question="$testQuestion"
                                :number="$questionFollowUpNumber++"
                                :test="$test"
                                :attachment_counters="$attachment_counters"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'CompletionQuestion')
                        <livewire:test-opgaven-print.completion-question
                                :question="$testQuestion"
                                :number="$questionFollowUpNumber++"
                                :test="$test"
                                :attachment_counters="$attachment_counters"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'RankingQuestion')
                        <livewire:test-opgaven-print.ranking-question
                                :question="$testQuestion"
                                :number="$questionFollowUpNumber++"
                                :test="$test"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'InfoscreenQuestion')
                        <livewire:test-opgaven-print.info-screen-question
                                :question="$testQuestion"
                                :number="$questionFollowUpNumber++"
                                :test="$test"
                                :attachment_counters="$attachment_counters"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'DrawingQuestion')
                        <livewire:test-opgaven-print.drawing-question
                                :question="$testQuestion"
                                :number="$questionFollowUpNumber++"
                                :test="$test"
                                :attachment_counters="$attachment_counters"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'MatrixQuestion')
                        <livewire:test-opgaven-print.matrix-question
                                :question="$testQuestion"
                                :number="$questionFollowUpNumber++"
                                :test="$test"
                                :attachment_counters="$attachment_counters"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'RelationQuestion')
                        <livewire:test-opgaven-print.relation-question
                                :question="$testQuestion"
                                :number="$questionFollowUpNumber++"
                                :test="$test"
                                :attachment_counters="$attachment_counters"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'GroupQuestion' && $currentGroupQuestion !== $testQuestion->getKey())
                        @php $currentGroupQuestion = $testQuestion->getKey() @endphp
                        <livewire:test-opgaven-print.group-question
                                :question="$testQuestion"
                                :group-start="true"
                                :attachment_counters="$attachment_counters"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'GroupQuestion' && $currentGroupQuestion === $testQuestion->getKey())
                        <livewire:test-opgaven-print.group-question
                                :question="$testQuestion"
                                :group-start="false"
                                :attachment_counters="$attachment_counters"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @endif
                </div>
            @endforeach
        </div>
    </div>

{{--    <span id="citation">--}}
{{--        <div id="extraFooterLine" class="footer-line" style=""></div>--}}
{{--        <table class="citation-table">--}}
{{--            <tr>--}}
{{--                <th>--}}
{{--                    {{ __('test-pdf.citation') }}--}}
{{--                </th>--}}
{{--            </tr>--}}
{{--            <tr>--}}
{{--                <td>--}}
{{--                    {{ __('test-pdf.citation_text') }}--}}
{{--                </td>--}}
{{--            </tr>--}}
{{--        </table>--}}
{{--    </span>--}}

</x-layouts.pdf-test-print>
