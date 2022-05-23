<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <x-slot name="answer_model">
        {!! $question->answer !!}
    </x-slot>
    <div class="w-full"
         x-data="{ }"
         x-init="
                    (function() {
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
                                    editor.isReadOnly = true;
                                    const wordCountPlugin = editor.plugins.get( 'WordCount' );
                                    const wordCountWrapper = document.getElementById( 'word-count-{{ $editorId }}' );
                                    wordCountWrapper.appendChild( wordCountPlugin.wordCountContainer );
                                } )
                                .catch( error => {
                                    console.error( error );
                                } );
                      })()
                      ">

        <div class="flex-col space-y-3">
            <div>
                {!! $question->converted_question_html !!}
            </div>
            <x-input.group wire:ignore class="w-full">
                <x-input.textarea autofocus="true" id="{{ $editorId }}" name="{{ $editorId }}"
                                  wire:model="answer">

                </x-input.textarea>
            </x-input.group>
        </div>
        <div id="word-count-{{ $editorId }}" wire:ignore></div>
    </div>
</x-partials.answer-model-question-container>


