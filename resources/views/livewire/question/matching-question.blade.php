<div class="w-full space-y-3">
    {!!   $question->getQuestionHtml() !!}
    <div>
        <span>Grijp en sleep de items in het juiste vak.</span>
    </div>
    <div class="flex flex-col">
        Combineer
        <div class="drag-items">
            <x-drag-item state="idle">Idle</x-drag-item>
            <x-drag-item state="onDrag">onDrag</x-drag-item>
            <x-drag-item state="onDragEnter">onDragEnter</x-drag-item>
            <x-drag-item state="onDrop">onDrop</x-drag-item>
        </div>
        <div class="dropzones flex flex-row space-x-4">
            <x-dropzone title="Dropzone 1"></x-dropzone>
        </div>
    </div>
    <div class="flex flex-col">
        Rangschik
        <div class="flex flex-col max-w-max">
            <x-drag-item state="idle" wFull="true">Rangschik Rangschik Rangschik</x-drag-item>
            <x-drag-item state="onDrag" wFull="true">RangschikRangschikRangschikRangschikRangschikRangschik</x-drag-item>
            <x-drag-item state="onDragEnter" wFull="true">Rangschik</x-drag-item>
            <x-drag-item state="onDrop" wFull="true">Rangschik</x-drag-item>
        </div>
    </div>
</div>
