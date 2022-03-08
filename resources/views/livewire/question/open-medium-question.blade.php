<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full">
        <div class="mb-4" questionHtml wire:ignore>
            {!! $question->converted_question_html  !!}
        </div>
        <div wire:ignore>
            <x-input.group class="w-full" label="{!! __('test_take.instruction_open_question') !!}">
                <textarea id="{{ $editorId }}" name="{{ $editorId }}" wire:model.debounce.1000ms="answer">{!! $this->answer !!}</textarea>
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
                    extraPlugins : 'blockimagepaste,quicktable,ckeditor_wiris,autogrow,wordcount,notification',
                    toolbar: [
                        { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript' ] },
                        { name: 'paragraph', items: [ 'NumberedList', 'BulletedList' ] },
                        { name: 'insert', items: [ 'Table' ] },
                        { name: 'styles', items: ['Font', 'FontSize' ] },
                        { name: 'wirisplugins', items: ['ckeditor_wiris_formulaEditor', 'ckeditor_wiris_formulaEditorChemistry']}
                    ],
                    fontSize_sizes : '1/1.000em;2/1.1250em;3/1.250em;4/1.375em;5/1.4375em;6/1.5em;7/1.625em;8/1.750em;9/2.250em;10/3em;11/4.5em',
                    contentsCss : '/ckeditor/student.css'
                })
                CKEDITOR.instances['{{ $editorId }}']
                    .on('change', function (e) {
                        var textarea = document.getElementById('{{ $editorId }}');
                        textarea.value = e.editor.getData();
                        textarea.dispatchEvent(new Event('input'))
                    });
                CKEDITOR.instances['{{ $editorId }}']
                    .on('contentDom', function () {
                        var editor = CKEDITOR.instances['{{ $editorId }}'];
                        editor.editable().attachListener(editor.document, 'touchstart', function () {
                            if (Core.appType === 'ipad') {
                                document.querySelector('header').classList.remove('fixed');
                                document.querySelector('footer').classList.remove('fixed');
                            }
                        });
                    });
            })();
        </script>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>