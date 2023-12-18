@props(['viewStruct'])

<div class="question-relation-question-grid">
    @foreach($viewStruct as $wordId => $data)
        <div class="flex">
            <input :id="'relation-question-' . $wordId" type="text" wire:model="answerStruct.{{$wordId}}" class="form-input"/>
            <span :for="'relation-question-' . $wordId" class="relation-question-label">
                @if($data['question_prefix'])
                    <span class="mr-1">{{ $data['question_prefix'] }}:</span>
                @endif
                <b>{{ $data['question'] }}</b>
            </span>
        </div>
    @endforeach
</div>