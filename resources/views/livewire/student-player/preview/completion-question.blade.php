<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full space-y-3 completion-multi"
         x-data="{}"
         x-init="truncateOptionsIfTooLong($el); setTitlesOnLoad($el)"
         @resize.window.debounce.250ms="truncateOptionsIfTooLong($el)"
    >
        <div class="flex flex-wrap completion-question-question-container">
            @if($this->question->isSubType('multi'))
                <div class="flex flex-wrap items-center">
                    @foreach($questionTextPartials as $answerIndex => $textPartialArray)
                        @foreach($textPartialArray as $textPartial){{--
                        --}}{!!$textPartial!!}{{-- Do not format this file. It causes unfixable/unwanted whitespaces.
                    --}}@endforeach
                        <x-input.select class="!w-fit mb-1 mr-1 text-base">
                            @foreach($options[$answerIndex + 1] as $option)
                                <x-input.option :value="$option" :label="$option" />
                            @endforeach
                        </x-input.select>
                    @endforeach
                    @foreach($questionTextPartialFinal as $textPartial){{--
                    --}}{!!$textPartial!!}{{--
                 --}}@endforeach
                </div>
            @else
                <x-completion-question-converted-html :question="$this->question" context="teacher-preview" />
            @endif
        </div>
    </div>
    <x-attachment.preview-attachment-modal :attachment="$attachment" :questionId="$questionId" />
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
