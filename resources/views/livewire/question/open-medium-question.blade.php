<div class="w-full">
    <div>
        <x-input.group wire:ignore class="w-full">
            <x-input.textarea autofocus="true" id="ckeditor" name="ckeditor"
                              wire:model="answer"
                              x-data=""
                              x-init="
                      (function() {
                            var editor = CKEDITOR.instances['ckeditor']
                            if (editor) {
                                editor.destroy(true)
                            }
                            CKEDITOR.replace( 'ckeditor', {
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
                            CKEDITOR.instances['ckeditor']
                            .on('change',function(e){
                                $dispatch('input', e.editor.getData())
                            })
                      })()
                      "
            >
                {{ $answer }}
            </x-input.textarea>
        </x-input.group>
    </div>
</div>


