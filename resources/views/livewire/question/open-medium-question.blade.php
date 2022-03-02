<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full" >
        <div class="mb-4" questionHtml wire:ignore>
            {!! $question->converted_question_html  !!}
        </div>
        <div wire:ignore>
            <span>{!! __('test_take.instruction_open_question') !!}</span>
            <x-input.group class="w-full" label="">
                <textarea id="{{ $editorId }}" name="{{ $editorId }}" wire:model.debounce.1000ms="answer">{!! $this->answer !!}</textarea>
            </x-input.group>
        </div>
        <div id="word-count-{{ $editorId }}" wire:ignore></div>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                var editor = ClassicEditors['{{ $editorId }}'];
                if (editor) {
                    editor.destroy(true);
                }
                ClassicEditor
                    .create( document.querySelector( '#{{ $editorId }}' ),{
                        autosave: {
                            waitingTime: 300,
                            save( editor ) {
                                editor.updateSourceElement();
                                editor.sourceElement.dispatchEvent(new Event('input'));
                            }
                        }
                    } )
                    .then( editor => {
                        ClassicEditors['{{ $editorId }}'] = editor;
                        const wordCountPlugin = editor.plugins.get( 'WordCount' );
                        const wordCountWrapper = document.getElementById( 'word-count-{{ $editorId }}' );
                        wordCountWrapper.appendChild( wordCountPlugin.wordCountContainer );
                        disableContextMenuOnCkeditor();
                    } )
                    .catch( error => {
                        console.error( error );
                    } );
            });
            document.addEventListener('readspeaker_closed', () => {
                if(window.classicEditorReplaced){
                    return;
                }
                window.classicEditorReplaced = true;
                var editor = ClassicEditors['{{ $editorId }}'];
                if (editor) {
                    var element = document.getElementsByClassName('ck-editor__editable_inline')[0];
                    if(element){
                        element.replaceWith(editor.currentElement);
                        editor.destroy(true);
                    }
                }
                ClassicEditor
                    .create( document.querySelector( '#{{ $editorId }}' ),{
                        autosave: {
                            waitingTime: 300,
                            save( editor ) {
                                editor.updateSourceElement();
                                editor.sourceElement.dispatchEvent(new Event('input'));
                            }
                        }
                    } )
                    .then( editor => {
                        ClassicEditors['{{ $editorId }}'] = editor;
                        const wordCountPlugin = editor.plugins.get( 'WordCount' );
                        const wordCountWrapper = document.getElementById( 'word-count-{{ $editorId }}' );
                        wordCountWrapper.appendChild( wordCountPlugin.wordCountContainer );
                        disableContextMenuOnCkeditor();
                    } )
                    .catch( error => {
                        console.error( error );
                    } );
            })
            document.addEventListener('readspeaker_started', () => {
                var editor = ClassicEditors['{{ $editorId }}'];
                editor.currentElement  = document.getElementsByClassName('ck-editor__editable_inline')[0];
                var element = document.getElementsByClassName('ck-editor__editable_inline')[0];
                if(element) {
                    var elementClone = element.cloneNode(true);
                    element.replaceWith(elementClone);
                }
                window.classicEditorReplaced = false;
            })

        </script>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>