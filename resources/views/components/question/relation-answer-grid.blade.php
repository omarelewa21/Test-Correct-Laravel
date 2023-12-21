@props(['answerStruct', 'studentAnswer', 'showToggles' => false, 'togglesDisabled' => false])

<div @class([
    "question-relation-answer-grid",
    "relation-student-answer" => $studentAnswer,
])>
    @foreach($answerStruct as $wordId => $data)
        <div class="flex">
            @if($studentAnswer && $showToggles)
                <span class="mr-2">
                    <x-button.evaluation-toggle
                            :disabled="$data['not_answered'] || $togglesDisabled"
                            :initialStatus="$data['initial_value']"
                            :toggleValue="$data['toggle_value']"
                            :identifier="$wordId"
                    />
                </span>
            @endif

            <input id="{{'relation-question-' . $wordId}}" type="text" disabled value="{{ $data['answer'] }}" class="form-input" />
            <span for="{{'relation-question-' . $wordId}}" class="relation-question-label">
                @isset($data['question_prefix'])
                    <span class="mr-1">{{ $data['question_prefix'] }}:</span>
                @endif
                <b>{{ $data['question'] }}</b>
            </span>



        </div>
    @endforeach
</div>