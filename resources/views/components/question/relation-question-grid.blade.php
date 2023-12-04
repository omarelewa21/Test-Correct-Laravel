@props(['viewStruct', 'words'])

<div class="grid grid-cols-2">
    @foreach($viewStruct as $key)
        <div class="flex">
            <input :id="'relation-question-' . $key" type="text" wire:model="answerStruct.{{$key}}" />
            <label :for="'relation-question-' . $key" class="border border-bluegrey p-2 my-2 rounded">word: {{ $key }} - text: {{ $words[$key]['text'] }}</label>
        </div>
    @endforeach
</div>