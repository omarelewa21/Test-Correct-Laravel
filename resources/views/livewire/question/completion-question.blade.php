<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full space-y-3"
         x-data="{}"
         x-init="truncateOptionsIfTooLong($el); setTitlesOnLoad($el)"
         @resize.window.debounce.250ms="truncateOptionsIfTooLong($el)"
         id="completion_{{ $question->id }}"
    >
        <div questionHtml wire:ignore>
            <x-input.group class="body1 max-w-full flex-col" for="">
                {!! $html !!}
            </x-input.group>
            <div wire:ignore class="rspopup_tlc hidden rsbtn_popup_tlc_{{$question->id}}"  ><div class="rspopup_play rspopup_btn " role="button" tabindex="0" aria-label="Lees voor" data-rslang="title/arialabel:listen" data-rsevent-id="rs_340375" title="Lees voor"></div></div>
        </div>
        @push('scripts')
            <script>
                @if($question->subtype=='completion')
                document.addEventListener('readspeaker_opened', () => {
                    if(shouldNotCreateHiddenDivsForTextboxesCompletion('completion_{{ $question->id }}')){
                        return;
                    }
                    createHiddenDivsForTextboxesCompletion('completion_{{ $question->id }}');
                })
                document.addEventListener('readspeaker_closed', () => {
                    //removeHiddenDivsForTextboxesCompletion('completion_{{ $question->id }}');
                })
                @endif
                @if($question->subtype=='multi')
                document.addEventListener('readspeaker_opened', () => {
                    if(shouldNotCreateHiddenDivsForSelects('completion_{{ $question->id }}')){
                        return;
                    }
                    createHiddenDivsForSelects('completion_{{ $question->id }}');
                })
                document.addEventListener('readspeaker_closed', () => {
                    //removeHiddenDivsForSelect('completion_{{ $question->id }}');
                })
                @endif
            </script>
        @endpush
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>
