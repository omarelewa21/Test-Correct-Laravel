@props(['answerStruct', 'questionStruct'])

<div class="grid grid-cols-1">
    @foreach($answerStruct as $wordId => $data)
        <div class="flex">
            <input :id="'relation-question-' . $wordId" type="text" disabled value="{{ $data['answer'] }}" />
            <label :for="'relation-question-' . $wordId" class="border border-bluegrey p-2 my-2 rounded">{{ $data['question'] }}</label>
        </div>
    @endforeach
</div>