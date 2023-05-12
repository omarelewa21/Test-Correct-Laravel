<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full" >
        <div class="mb-4" questionHtml wire:ignore>
            {!! $question->converted_question_html  !!}
        </div>
        <div wire:ignore x-data="{
                allowWsc: @entangle('allowWsc'),
                initializeEditor: async function(el) {
                    let editor = ClassicEditors[el.id];
                    let isFocused = editor && editor.ui.focusTracker.isFocused;                                                 // detect if the editor is in focus
                    if (editor) editor.destroy(true);
                    await RichTextEditor.initClassicEditorForStudentplayer(el.id,'{{ $question->getKey() }}', this.allowWsc)   // await for the editor to be initialized
                    editor = ClassicEditors[el.id]                                                                              // get the new editor
                    if (isFocused) editor.focus();                                                                              // if the editor was in focus, focus it again   
                    editor.model.change( writer => {
                        writer.setSelection( editor.model.document.getRoot(), 'end' );                                          // set the cursor to the end of the editor
                    } );
                }
            }"
        >
            <span>{!! __('test_take.instruction_open_question') !!}</span>
            <x-input.group class="w-full" label="" style="position: relative;">
                <textarea
                    id="{{ $editorId }}"
                    name="{{ $editorId }}"
                    wire:model.debounce.1000ms="answer"
                    x-init="!allowWsc && initializeEditor($el)"
                    x-effect="allowWsc && initializeEditor($el)"
                >
                    {!! $this->answer !!}
                </textarea>
                @if(Auth::user()->text2speech)
                    <div wire:ignore class="rspopup_tlc hidden rsbtn_popup_tlc_{{$question->id}}"  ><div class="rspopup_play rspopup_btn rs_skip" role="button" tabindex="0" aria-label="Lees voor" data-rslang="title/arialabel:listen" data-rsevent-id="rs_340375" title="Lees voor"></div></div>
                @endif
            </x-input.group>
        </div>
        <div id="word-count-{{ $editorId }}" wire:ignore></div>

        <script>
            @if(!is_null(Auth::user())&&Auth::user()->text2speech)
                document.addEventListener('readspeaker_closed', () => {
                    if(ReadspeakerTlc.guard.shouldNotReinitCkeditor(document.querySelector( '#{{ $editorId }}' ))){
                        return;
                    }
                    ReadspeakerTlc.ckeditor.reattachReadableAreaAndDestroy('{{ $editorId }}');
                    // RichTextEditor.initClassicEditorForStudentplayer('{{$editorId}}','{{ $question->getKey() }}', @this.allowWsc);
                })
                document.addEventListener('readspeaker_started', () => {
                    if(ReadspeakerTlc.guard.shouldNotDetachCkEditor(document.querySelector( '#{{ $editorId }}' ))){
                        return;
                    }
                    RichTextEditor.writeContentToTexarea('{{ $editorId }}');
                    ReadspeakerTlc.ckeditor.detachReadableAreaFromCkeditor('{{ $editorId }}');
                })
            @endif
        </script>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>