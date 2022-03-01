<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full" oncontextmenu="return false;">
        <div class="mb-4" questionHtml wire:ignore>
            {!! $question->converted_question_html  !!}
        </div>
        <div wire:ignore>
            <x-input.group class="w-full" label="{!! __('test_take.instruction_open_question') !!}">
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
                        editor.editing.view.change( writer => {
                            writer.setAttribute( 'spellcheck', 'false', editor.editing.view.document.getRoot() );
                        } );
                    } )
                    .catch( error => {
                        console.error( error );
                    } );
            });
        </script>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>