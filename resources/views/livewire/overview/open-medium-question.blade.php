<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="w-full"
         x-data="{ }"
         x-init="
                      (function() {
                            var editor = CKEDITOR.instances['{{ $editorId }}']
                            if (editor) {
                                editor.destroy(true)
                            }
                            CKEDITOR.replace( '{{ $editorId }}', {
                                removePlugins : 'pastefromword,advanced,simpleuploads,dropoff,copyformatting,image,pastetext,uploadwidget,uploadimage',
                                extraPlugins : 'blockimagepaste,quicktable,ckeditor_wiris,autogrow',
                                toolbar: [
                                    { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript' ] },
                                    { name: 'paragraph', items: [ 'NumberedList', 'BulletedList' ] },
                                    { name: 'insert', items: [ 'Table' ] },
                                    { name: 'styles', items: ['Font', 'FontSize' ] },
                                    { name: 'wirisplugins', items: ['ckeditor_wiris_formulaEditor', 'ckeditor_wiris_formulaEditorChemistry']}
                                ]
                            })
                            CKEDITOR.config.readOnly = true
                            CKEDITOR.instances['{{ $editorId }}']
                            .on('change',function(e){
                                $dispatch('input', e.editor.getData())
                            })
                      })()
                      ">

        <div class="flex-col space-y-3">
            <div>
                {!! $question->getQuestionHtml() !!}
            </div>
            <x-input.group wire:ignore class="w-full">
                <x-input.textarea autofocus="true" id="{{ $editorId }}" name="{{ $editorId }}"
                                  wire:model="answer">

                </x-input.textarea>
            </x-input.group>
        </div>
    </div>
</x-partials.overview-question-container>


