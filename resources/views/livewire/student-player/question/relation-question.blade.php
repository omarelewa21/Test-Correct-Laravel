<x-partials.question-container :number="$number" :question="$question">
    <div>Lekker relateren (test take)</div>

    <div>
        @foreach($firstHalfAnswerStruct as $key => $answerOption)
            <div class="flex flex-col">
                <label :for="'relation-question-' . $key" class="border border-bluegrey p-2 my-2 rounded">word: {{ $key }} - text: {{ $this->words[$key]['text'] }}</label>
                <input :id="'relation-question-' . $key" type="text" wire:model="answerStruct.{{$key}}" />
            </div>
        @endforeach
        @foreach($secondHalfAnswerStruct as $key => $answerOption)
            <div class="flex flex-col">
                <label :for="'relation-question-' . $key" class="border border-bluegrey p-2 my-2 rounded">word: {{ $key }} - text: {{ $this->words[$key]['text'] }}</label>
                <input :id="'relation-question-' . $key" type="text" wire:model="answerStruct.{{$key}}" />
            </div>
        @endforeach
    </div>


    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId" />
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
