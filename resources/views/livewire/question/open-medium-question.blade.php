<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full"
         x-on:leaving-open-question.window="
          if('{{ $this->question->uuid }}' == $event.detail){
            var editorData = CKEDITOR.instances['{{ $editorId }}'].getData();
            console.log(editorData);
            CKEDITOR.instances['{{ $editorId }}'].setData(editorData);
         }
         "
    >
        <div class="mb-4">
            {!! $question->getQuestionHtml()  !!}
        </div>
        <div wire:ignore>
            <x-input.group class="w-full" label="{!! __('test_take.instruction_open_question') !!}">
                <textarea id="{{ $editorId }}" name="{{ $editorId }}" wire:model.debounce.1000ms="answer"></textarea>
            </x-input.group>
        </div>


        <script>

            (function() {
                var editor = CKEDITOR.instances['{{ $editorId }}']
                if (editor) {
                    editor.destroy(true)
                }
                CKEDITOR.replace( '{{ $editorId }}', {
                    removePlugins : 'pastefromword,advanced,simpleuploads,dropoff,copyformatting,image,pastetext,uploadwidget,uploadimage',
                    extraPlugins : 'blockimagepaste,quicktable,ckeditor_wiris',
                    toolbar: [
                        { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript' ] },
                        { name: 'paragraph', items: [ 'NumberedList', 'BulletedList' ] },
                        { name: 'insert', items: [ 'Table' ] },
                        { name: 'styles', items: ['Font', 'FontSize' ] },
                        { name: 'wirisplugins', items: ['ckeditor_wiris_formulaEditor', 'ckeditor_wiris_formulaEditorChemistry']}
                    ]
                })
                CKEDITOR.instances['{{ $editorId }}']
                    .on('change',function(e){
                        var textarea = document.getElementById('{{ $editorId }}');
                        textarea.value = e.editor.getData();
                        textarea.dispatchEvent(new Event('input'));
                })
            })()
        </script>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>