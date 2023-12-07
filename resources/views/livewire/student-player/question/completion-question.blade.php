<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full space-y-3 completion-question-container"
         x-data="completionQuestion()"
         x-init="truncateOptionsIfTooLong($el); setTitlesOnLoad($el)"
         @resize.window.debounce.250ms="truncateOptionsIfTooLong($el)"
         id="completion_{{ $question->id }}"
    >
        <div questionHtml wire:ignore>
            <div class="flex flex-wrap completion-question-question-container">
                @if($this->question->isSubType('multi'))
                    <div class="flex flex-wrap items-center">
                        @foreach($questionTextPartials as $answerIndex => $textPartialArray)
                            @foreach($textPartialArray as $textPartial){{--
                        --}}{!!$textPartial!!}{{-- Do not format this file. It causes unfixable/unwanted whitespaces.
                    --}}@endforeach
                            <x-input.select class="!w-fit mb-1 mr-1 text-base" wire:model="answer.{{ $answerIndex+1 }}">
                                @foreach($options[$answerIndex + 1] as $key => $option)
                                    <x-input.option :value="$option" :label="$option" />
                                @endforeach
                            </x-input.select>
                        @endforeach
                        @foreach($questionTextPartialFinal as $textPartial){{--
                    --}}{!!$textPartial!!}{{--
                 --}}@endforeach
                    </div>
                @else
                    <x-completion-question-converted-html :question="$this->question"/>
                @endif
            </div>
            <div wire:ignore class="rspopup_tlc hidden rsbtn_popup_tlc_{{$question->id}}"  ><div class="rspopup_play rspopup_btn rs_skip" role="button" tabindex="0" aria-label="Lees voor" data-rslang="title/arialabel:listen" data-rsevent-id="rs_340375" title="Lees voor"></div></div>
        </div>
        @push('scripts')
            <script>
                @if(!is_null(Auth::user())&&Auth::user()->text2speech)
                    @if($question->subtype=='completion')
                    document.addEventListener('readspeaker_opened', () => {
                        if(ReadspeakerTlc.guard.shouldNotCreateHiddenDivsForTextboxesCompletion('completion_{{ $question->id }}')){
                            return;
                        }
                        ReadspeakerTlc.hiddenElement.createHiddenDivsForTextboxesCompletion('completion_{{ $question->id }}');
                    })
                    document.addEventListener('readspeaker_closed', () => {
                        //removeHiddenDivsForTextboxesCompletion('completion_{{ $question->id }}');
                    })
                    @endif
                    @if($question->subtype=='multi')
                    document.addEventListener('readspeaker_opened', () => {
                        if(ReadspeakerTlc.guard.shouldNotCreateHiddenDivsForSelects('completion_{{ $question->id }}')){
                            return;
                        }
                        ReadspeakerTlc.hiddenElement.createHiddenDivsForSelects('completion_{{ $question->id }}');
                    })
                    document.addEventListener('readspeaker_closed', () => {
                        //removeHiddenDivsForSelect('completion_{{ $question->id }}');
                    })
                    @endif
                @endif
            </script>
        @endpush
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>
