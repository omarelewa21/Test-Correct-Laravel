RichTextEditor = {
    initStudent: function (editorId) {
        var editor = CKEDITOR.instances[editorId]
        if (editor) {
            editor.destroy(true)
        }
        CKEDITOR.replace(editorId, {
            removePlugins: 'pastefromword,advanced,simpleuploads,dropoff,copyformatting,image,pastetext,uploadwidget,uploadimage',
            extraPlugins: 'blockimagepaste,quicktable,ckeditor_wiris,autogrow',
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
                var textarea = document.getElementById(editorId);
                setTimeout(function () {
                    textarea.value = e.editor.getData();
                }, 300);
                textarea.dispatchEvent(new Event('input'))
            });
    },

    initCMS: function (editorId) {
        var editor = CKEDITOR.instances[editorId]
        if (editor) {
            editor.destroy(true)
        }
        CKEDITOR.replace(editorId, {
            removePlugins: '',

        })
        CKEDITOR.instances[editorId]
            .on('change', function (e) {
                var textarea = document.getElementById(editorId);
                setTimeout(function () {
                    textarea.value = e.editor.getData();
                }, 300);
                textarea.dispatchEvent(new Event('input'))
            });
    },
    initSelectionCMS:function(editorId) {
        var editor = CKEDITOR.instances[editorId]
        if (editor) {
            editor.destroy(true)
        }

        CKEDITOR.replace(editorId, {
            removePlugins: 'pastefromword,simpleuploads,dropoff,copyformatting,image,pastetext,uploadwidget,uploadimage',
            extraPlugins: 'selection,blockimagepaste,quicktable,ckeditor_wiris,autogrow',
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
                var textarea = document.getElementById(editorId);
                setTimeout(function () {
                    textarea.value = e.editor.getData();
                }, 300);
                textarea.dispatchEvent(new Event('input'))
            });
    },
    initCompletionCMS: function (editorId) {

        var editor = CKEDITOR.instances[editorId]
        if (editor) {
            editor.destroy(true)
        }

        CKEDITOR.replace(editorId, {
            removePlugins: 'pastefromword,simpleuploads,dropoff,copyformatting,image,pastetext,uploadwidget,uploadimage',
            extraPlugins: 'completion,blockimagepaste,quicktable,ckeditor_wiris,autogrow',
            toolbar: [
                {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript']},
                {name: 'paragraph', items: ['NumberedList', 'BulletedList']},
                {name: 'insert', items: ['Table']},
                {name: 'styles', items: ['Font', 'FontSize']},
                {name: 'wirisplugins', items: ['ckeditor_wiris_formulaEditor', 'ckeditor_wiris_formulaEditorChemistry']},
                {name: 'extra', items: ['completion']}
            ]
        })
        CKEDITOR.instances[editorId]
            .on('change', function (e) {
                var textarea = document.getElementById(editorId);
                setTimeout(function () {
                    textarea.value = e.editor.getData();
                }, 300);
                textarea.dispatchEvent(new Event('input'))
            });
    }
}
