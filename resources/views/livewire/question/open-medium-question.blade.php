<div class="flex flex-col p-8 sm:p-10 content-section"  x-data="{ showMe: false }" x-on:current-updated.window="showMe = ({{ $number }} == $event.detail.current)"  x-show="showMe"  >
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6">{!! __($question->caption) !!} </h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
    </div>
    <div class="w-full">
        <div>
            <x-input.group wire:ignore class="w-full">
                <x-input.textarea autofocus="true" id="ckeditor" name="ckeditor"
                                  wire:model="answer"

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
</div>


