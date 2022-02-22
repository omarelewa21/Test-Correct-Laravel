RichTextEditor = {
    initStudent: function (editorId) {
        var editor = CKEDITOR.instances[editorId]
        if (editor) {
            editor.destroy(true)
        }
        CKEDITOR.replace(editorId, {
            removePlugins: 'pastefromword,pastefromgdocs,advanced,simpleuploads,dropoff,copyformatting,image,pastetext,uploadwidget,uploadimage',
            extraPlugins: 'blockimagepaste,quicktable,ckeditor_wiris,autogrow,wordcount,notification,readspeaker',
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

        editor.on('change', function (e) {
            RichTextEditor.sendInputEventToEditor(editorId, e);
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
            removePlugins: 'pastefromword,simpleuploads,dropoff,copyformatting,image,pastetext,uploadwidget,uploadimage',
            extraPlugins: 'selection,blockimagepaste,quicktable,ckeditor_wiris,autogrow,wordcount,notification',
            toolbar: [
                {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript']},
                {name: 'paragraph', items: ['NumberedList', 'BulletedList']},
                {name: 'insert', items: ['Table']},
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
            extraPlugins: 'completion,blockimagepaste,quicktable,ckeditor_wiris,autogrow,wordcount,notification',
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
}
