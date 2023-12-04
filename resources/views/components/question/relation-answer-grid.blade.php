@props(['answerStruct', 'questionStruct'])

<div class="grid grid-cols-1">
    @foreach($answerStruct as $key => $value)
        <div class="flex">
            <input :id="'relation-question-' . $key" type="text" disabled value="{{ $value }}" />
            <label :for="'relation-question-' . $key" class="border border-bluegrey p-2 my-2 rounded">{{ $questionStruct[$key]['text'] }}</label>
        </div>
    @endforeach
</div>