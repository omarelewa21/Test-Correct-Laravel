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

    initStudentCoLearning: function (editorId,lang= 'nl_NL',wsc = false) {
        var editor = CKEDITOR.instances[editorId];
        if (editor) {
            editor.destroy(true)
        }
        if(wsc){
            CKEDITOR.disableAutoInline = true;
            CKEDITOR.config.removePlugins = 'scayt,wsc';
        }
        CKEDITOR.on('instanceReady', function(event) {
            var editor = event.editor;
            WebspellcheckerTlc.forTeacherQuestion(editor,lang,wsc);
        });
        CKEDITOR.replace(editorId, {
            removePlugins: 'pastefromword,pastefromgdocs,advanced,simpleuploads,dropoff,copyformatting,image,pastetext,uploadwidget,uploadimage,elementspath',
            extraPlugins: 'quicktable,ckeditor_wiris,autogrow,wordcount,notification',
            wordcount: {
                showWordCount: true,
                showParagraphs: false,
                showCharCount: true,
            },
            toolbar: []
        });
        CKEDITOR.config.wordCount = {
            showWordCount: true,
            showParagraphs: false,
            showCharCount: true,
        }
        editor = CKEDITOR.instances[editorId];
        editor.on('change', function (e) {
            RichTextEditor.sendInputEventToEditor(editorId, e);
        });
        editor.on('instanceReady', function (e) {
            setTimeout(() => {
                document.getElementById('word-count-'+editorId).textContent = editor.wordCount.wordCount;
                document.getElementById('char-count-'+editorId).textContent = editor.wordCount.charCount;
            },300)
            window.addEventListener('wsc-problems-count-updated-'+editorId, (e) => {
                let problemCountSpan = document.getElementById('problem-count-'+editorId);
                if(problemCountSpan){
                    problemCountSpan.textContent = e.detail.problemsCount;
                }
            });
            document.getElementById('cke_wordcount_'+editorId).classList.add('hidden');
            document.querySelector('.cke_top').style.display = 'none !important';
            document.querySelector('.cke_bottom').style.display = 'none !important';
        });

    },

    initCMS: function (editorId,lang= 'nl_NL',wsc = false) {
        var editor = CKEDITOR.instances[editorId];
        if (editor) {
            editor.destroy(true)
        }
        if(wsc){
            CKEDITOR.disableAutoInline = true;
            CKEDITOR.config.removePlugins = 'scayt,wsc';
        }
        CKEDITOR.on('instanceReady', function(event) {
            var editor = event.editor;
            WebspellcheckerTlc.forTeacherQuestion(editor,lang,wsc);

        });
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
    initSelectionCMS:function(editorId,lang= 'nl_NL',wsc = false) {
        var editor = CKEDITOR.instances[editorId]
        if (editor) {
            editor.destroy(true)
        }
        if(wsc){
            CKEDITOR.disableAutoInline = true;
            CKEDITOR.config.removePlugins = 'scayt,wsc';
        }
        CKEDITOR.on('instanceReady', function(event) {
            var editor = event.editor;
            WebspellcheckerTlc.forTeacherQuestion(editor,lang,wsc);

        });
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
    initCompletionCMS: function (editorId,lang= 'nl_NL',wsc = false) {

        var editor = CKEDITOR.instances[editorId]
        if (editor) {
            editor.destroy(true)
        }
        if(wsc){
            CKEDITOR.disableAutoInline = true;
            CKEDITOR.config.removePlugins = 'scayt,wsc';
        }
        CKEDITOR.on('instanceReady', function(event) {
            var editor = event.editor;
            WebspellcheckerTlc.forTeacherQuestion(editor,lang,wsc);
        });
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
        return ClassicEditor
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
    initClassicEditorForTeacherplayerWsc: function (editorId,lang) {
        var editor = ClassicEditors[editorId];
        if (editor) {
            editor.destroy(true);
        }
        return ClassicEditor
            .create( document.getElementById( editorId ),{
                autosave: {
                    waitingTime: 300,
                    save( editor ) {
                        editor.updateSourceElement();
                        editor.sourceElement.dispatchEvent(new Event('input'));
                    }
                },
                wproofreader: {
                    lang: lang,
                    serviceProtocol: 'https',
                    servicePort: '80',
                    serviceHost: 'testwsc.test-correct.nl',
                    servicePath: 'wscservice/api',
                    srcUrl: 'https://testwsc.test-correct.nl/wscservice/wscbundle/wscbundle.js'
                }
            } )
            .then( editor => {
                ClassicEditors[editorId] = editor;
                WebspellcheckerTlc.lang(editor, lang);
                // WebspellcheckerTlc.setEditorToReadOnly(editor);
            } )
            .catch( error => {
                console.error( error );
            } );
    },
    initClassicEditorForTeacherplayer: function (editorId) {
        var editor = ClassicEditors[editorId];
        if (editor) {
            editor.destroy(true);
        }
        return ClassicEditor
            .create( document.getElementById( editorId ),{
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
