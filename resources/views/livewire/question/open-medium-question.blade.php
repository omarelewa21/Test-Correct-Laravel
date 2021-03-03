<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full" wire:ignore>
        <div class="mb-4">
            {!! $question->getQuestionHtml()  !!}
        </div>
        <div
                x-data=""
                x-init="
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
                    .on('blur',function(e){
                        var textarea = document.getElementById('{{ $editorId }}')
                        textarea.value =  e.editor.getData()
                        textarea.dispatchEvent(new Event('input'));
                        $dispatch('current-question-answered')
                    })
              })()
              ">
            <x-input.group wire:ignore class="w-full">
                <x-input.textarea autofocus="true" id="{{ $editorId }}" name="{{ $editorId }}" wire:model="answer">
                </x-input.textarea>
            </x-input.group>
        </div>
    </div>
</x-partials.question-container>