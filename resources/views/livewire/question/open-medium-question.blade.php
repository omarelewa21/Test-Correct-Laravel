<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full" >
        <div class="mb-4" questionHtml wire:ignore>
            {!! $question->converted_question_html  !!}
        </div>
        <div wire:ignore>
            <span>{!! __('test_take.instruction_open_question') !!}</span>
            <x-input.group class="w-full" label="" style="position: relative;">
                <textarea id="{{ $editorId }}" name="{{ $editorId }}" wire:model.debounce.1000ms="answer">{!! $this->answer !!}</textarea>
                @if(Auth::user()->text2speech)
                    <div wire:ignore class="rspopup_tlc hidden rsbtn_popup_tlc_{{$question->id}}"  ><div class="rspopup_play rspopup_btn " role="button" tabindex="0" aria-label="Lees voor" data-rslang="title/arialabel:listen" data-rsevent-id="rs_340375" title="Lees voor"></div></div>
                @endif
            </x-input.group>
        </div>
        <div id="word-count-{{ $editorId }}" wire:ignore></div>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                var editor = ClassicEditors['{{ $editorId }}'];
                if (editor) {
                    editor.destroy(true);
                }
                RichTextEditor.initClassicEditorForStudentplayer('{{$editorId}}','{{ $question->getKey() }}');
            });
            document.addEventListener('readspeaker_closed', () => {
                if(shouldNotReinitCkeditor(document.querySelector( '#{{ $editorId }}' ))){
                    return;
                }
                if(window.classicEditorReplaced){
                    return;
                }
                ReadspeakerTlc.ckeditor.reattachReadableAreaAndDestroy('{{ $editorId }}');
                RichTextEditor.initClassicEditorForStudentplayer('{{$editorId}}','{{ $question->getKey() }}');
            })
            document.addEventListener('readspeaker_started', () => {
                ReadspeakerTlc.ckeditor.detachReadableAreaFromCkeditor('{{ $editorId }}');
            })

        </script>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>