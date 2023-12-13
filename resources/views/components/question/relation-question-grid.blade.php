@props(['viewStruct', 'words'])

<div class="question-relation-question-grid">
    @foreach($viewStruct as $key)
        <div class="flex">
            <input :id="'relation-question-' . $key" type="text" wire:model="answerStruct.{{$key}}" class="form-input"/>
            <span :for="'relation-question-' . $key" class="relation-question-label">word: {{ $key }} - text: {{ $words[$key]['text'] }}</span>
        </div>
    @endforeach
</div>