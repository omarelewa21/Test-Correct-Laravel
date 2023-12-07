@props(['answerStruct', 'studentAnswer', 'showToggles' => false])

<div class="grid grid-cols-1">
    @foreach($answerStruct as $wordId => $data)
        <div class="flex">
            <input :id="'relation-question-' . $wordId" type="text" disabled value="{{ $data['answer'] }}" />
            <label :for="'relation-question-' . $wordId" class="border border-bluegrey p-2 my-2 rounded">{{ $data['question'] }}</label>


            @if($studentAnswer && $showToggles)
                <x-button.evaluation-toggle
                        :disabled="$data['not_answered']"
                        :initialStatus="$data['initial_value']"
                        :toggleValue="$data['toggle_value']"
                        :identifier="$wordId"
                />
            @endif
        </div>
    @endforeach
</div>