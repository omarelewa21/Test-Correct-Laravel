<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full">
        <div class="mb-4">
            {!! $question->getQuestionHtml()  !!}
        </div>
        <div  wire:ignore >


        <div x-data=""
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
                                .on('change',function(e){
                                    var textarea = document.getElementById('ta_{{ $editorId }}')
                                    textarea.value =  e.editor.getData()
                                    textarea.dispatchEvent(new Event('input'))
                                })
                          })()

              ">
            <x-input.group class="w-full" label="{!! __('test_take.instruction_open_question') !!}">
                <div>
                    <div id="{{ $editorId }}">
                        {!! $answer !!}
                    </div>
                </div>
            </x-input.group>
        </div>
        </div>
                    <textarea id="ta_{{$editorId}}" name="{{ $editorId }}" wire:model="answer"
                              > {!! $answer !!}</textarea>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>