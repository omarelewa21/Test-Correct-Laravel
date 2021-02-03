<div x-data="{ showMe: {!! $number === $q ? 'true' : 'false'  !!} }"
     x-cloak
     x-show="showMe"
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

                            })
                      })()
                      "
>
    <div class="flex justify-end space-x-4">
        <x-question.note :question="$question"></x-question.note>
    </div>
    <div class="flex flex-col p-8 sm:p-10 content-section"
          x-on:current-updated.window="showMe = ({{ $number }} == $event.detail.current)"
    >
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
                    <x-input.textarea autofocus="true" id="{{ $editorId }}" name="{{ $editorId }}" wire:model="answer">
                    </x-input.textarea>
                </x-input.group>
            </div>
        </div>
    </div>
</div>
