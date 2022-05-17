RichTextEditor = {
    initStudent: function (editorId) {
        var editor = CKEDITOR.instances[editorId]
        if (editor) {
            editor.destroy(true)
        }
        CKEDITOR.replace(editorId, {
            removePlugins: 'pastefromword,pastefromgdocs,advanced,simpleuploads,dropoff,copyformatting,image,pastetext,uploadwidget,uploadimage',
            extraPlugins: 'blockimagepaste,quicktable,ckeditor_wiris,autogrow,wordcount,notification',
            toolbar: [
                {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript']},
                {name: 'paragraph', items: ['NumberedList', 'BulletedList']},
                {name: 'insert', items: ['Table']},
                {name: 'styles', items: ['Font', 'FontSize']},
                {name: 'wirisplugins', items: ['ckeditor_wiris_formulaEditor', 'ckeditor_wiris_formulaEditorChemistry']}
            ]
        })
        CKEDITOR.instances[editorId]
            .on('change', function (e) {
                RichTextEditor.sendInputEventToEditor(editorId, e);
            });
    },

    initCMS: function (editorId) {
        var editor = CKEDITOR.instances[editorId]
        if (editor) {
            editor.destroy(true)
        }
        CKEDITOR.replace(editorId, {});
        editor = CKEDITOR.instances[editorId];
        editor.shouldDispatchChange = false
        editor.on('change', function (e) {
            if(!e.editor.getData()?.includes('MathML')) {
                RichTextEditor.sendInputEventToEditor(editorId, e);
                return;
            }

            if (editor.shouldDispatchChange) {
                RichTextEditor.sendInputEventToEditor(editorId, e);
            }
            editor.shouldDispatchChange = true
        });
        editor.on('simpleuploads.startUpload', function (e) {
            e.data.extraHeaders = {
                'X-CSRF-TOKEN': document.querySelector('meta[name="_token"]').content,
            }
        });
        editor.on('simpleuploads.finishedUpload', function (e) {
            RichTextEditor.sendInputEventToEditor(editorId, e);
        });
    },
    initSelectionCMS:function(editorId) {
        var editor = CKEDITOR.instances[editorId]
        if (editor) {
            editor.destroy(true)
        }

        CKEDITOR.replace(editorId, {
            extraPlugins: 'selection,simpleuploads,quicktable,ckeditor_wiris,autogrow,wordcount,notification',
            toolbar: [
                {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript']},
                {name: 'paragraph', items: ['NumberedList', 'BulletedList']},
                {name: 'insert', items: ['addImage','Table']},
                {name: 'styles', items: ['Font', 'FontSize']},
                {name: 'wirisplugins', items: ['ckeditor_wiris_formulaEditor', 'ckeditor_wiris_formulaEditorChemistry']},
                {name: 'extra', items: ['selection']}
            ]
        })
        CKEDITOR.instances[editorId]
            .on('change', function (e) {
                RichTextEditor.sendInputEventToEditor(editorId, e);
            });
    },
    initCompletionCMS: function (editorId) {

        var editor = CKEDITOR.instances[editorId]
        if (editor) {
            editor.destroy(true)
        }
        CKEDITOR.replace(editorId, {
            extraPlugins: 'completion,simpleuploads,quicktable,ckeditor_wiris,autogrow,wordcount,notification',
            toolbar : [
                { name: 'clipboard', items: [ 'Undo', 'Redo' ] },
                { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat', 'Subscript', 'Superscript' ] },
                { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote' ] },
                { name: 'insert', items: [ 'addImage', 'Table' ] },
                { name: 'styles', items: [ 'Format', 'Font', 'FontSize' ] },
                { name: 'colors', items: [ 'TextColor', 'BGColor', 'CopyFormatting' ] },
                { name: 'align', items: [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                {name: 'wirisplugins', items: ['ckeditor_wiris_formulaEditor', 'ckeditor_wiris_formulaEditorChemistry']},
                { name: 'extra', items: ['completion']},
            ]
        })
        CKEDITOR.instances[editorId]
            .on('change', function (e) {
                RichTextEditor.sendInputEventToEditor(editorId, e);
            });
    },

    sendInputEventToEditor: function (editorId, e) {
        var textarea = document.getElementById(editorId);
        setTimeout(function () {
            textarea.value = e.editor.getData();
        }, 300);
        textarea.dispatchEvent(new Event('input'))
    },

    initClassicEditorForStudentplayer: function (editorId,questionId) {
        ClassicEditor
            .create( document.querySelector( '#'+editorId ),{
                autosave: {
                    waitingTime: 300,
                    save( editor ) {
                        editor.updateSourceElement();
                        editor.sourceElement.dispatchEvent(new Event('input'));
                    }
                }
            } )
            .then( editor => {
                ClassicEditors[editorId] = editor;
                const wordCountPlugin = editor.plugins.get( 'WordCount' );
                const wordCountWrapper = document.getElementById( 'word-count-'+editorId );
                wordCountWrapper.appendChild( wordCountPlugin.wordCountContainer );
                if(typeof ReadspeakerTlc != 'undefined') {
                    ReadspeakerTlc.ckeditor.addListenersForReadspeaker(editor, questionId, editorId);
                    ReadspeakerTlc.ckeditor.disableContextMenuOnCkeditor();
                }
            } )
            .catch( error => {
                console.error( error );
            } );
    },
    setReadOnly: function(editor)
    {
        editor.isReadOnly = true;
        var editables = editor.ui.view.editable.element.querySelectorAll("[contenteditable=true]");
        editables.forEach(function(element) {
            element.setAttribute('contenteditable',false);
        });
    },
    writeContentToTexarea: function(editorId)
    {
        var editor = ClassicEditors[editorId];
        if (editor) {
            editor.updateSourceElement();
            editor.sourceElement.dispatchEvent(new Event('input'));
        }
    }
}
