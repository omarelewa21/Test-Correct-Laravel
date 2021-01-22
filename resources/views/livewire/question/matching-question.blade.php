<div class="flex flex-col p-8 sm:p-10 content-section" x-show="'{{ $question->uuid }}' == current">
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6"> {!! __($question->caption) !!} </h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
    </div>

    <div class="flex flex-1">

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
                    <x-drag-item state="onDrag" wFull="true">RangschikRangschikRangschikRangschikRangschikRangschik
                    </x-drag-item>
                    <x-drag-item state="onDragEnter" wFull="true">Rangschik</x-drag-item>
                    <x-drag-item state="onDrop" wFull="true">Rangschik</x-drag-item>
                </div>
            </div>
        </div>
    </div>
</div>
