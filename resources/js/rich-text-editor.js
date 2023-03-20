RichTextEditor = {
    initStudent: function (editorId) {
        console.log('this should implement student init // example is open-medium-question.blase.php');
    },

    initStudentCoLearning: function (editorId, lang = 'nl_NL', wsc = false) {
        return ClassicEditor
            .create(document.querySelector('#' + editorId), {
                autosave: {
                    waitingTime: 300,
                    save(editor) {
                        editor.updateSourceElement();
                        editor.sourceElement.dispatchEvent(new Event('input'));
                    }
                },
                wordcount: {
                    showWordCount: true,
                    showParagraphs: false,
                    showCharCount: true,
                    countSpacesAsChars: true
                },
                autoGrow_maxHeight: 0,
                toolbar: [],
                wproofreader: window.WEBSPELLCHECKER_CONFIG
            })
            .then(editor => {
                ClassicEditors[editorId] = editor;
                const wordCountPlugin = editor.plugins.get('WordCount');
                const wordCountWrapper = document.getElementById('word-count-' + editorId);
                wordCountWrapper.appendChild(wordCountPlugin.wordCountContainer);

                if (typeof ReadspeakerTlc != 'undefined') {
                    ReadspeakerTlc.ckeditor.addListenersForReadspeaker(editor, questionId, editorId);
                    ReadspeakerTlc.ckeditor.disableContextMenuOnCkeditor();
                }
            })
            .catch(error => {
                console.error(error);
            });
    },


    initSelectionCMS: function (editorId, lang = 'nl_NL', allowWsc = false) {
        var editor = ClassicEditors[editorId];
        if (editor) {
            editor.destroy(true);
        }

        return ClassicEditor
            .create(
                document.getElementById(editorId),
                this.getConfigForTeacher(allowWsc, ['Selection'])
            )
            .then(editor => {
                ClassicEditors[editorId] = editor;
                WebspellcheckerTlc.lang(editor, lang);
                // WebspellcheckerTlc.setEditorToReadOnly(editor);
                this.setReadOnly(editor);
                window.editor = editor;
            })
            .catch(error => {
                console.error(error);
            });
    },
    initCompletionCMS: function (editorId, lang, allowWsc = false) {
        var editor = ClassicEditors[editorId];
        if (editor) {
            editor.destroy(true);
        }

        return ClassicEditor
            .create(
                document.getElementById(editorId),
                this.getConfigForTeacher(allowWsc, ['Completion'])
            )
            .then(editor => {
                ClassicEditors[editorId] = editor;

                WebspellcheckerTlc.lang(editor, lang);
                // WebspellcheckerTlc.setEditorToReadOnly(editor);
                this.setReadOnly(editor);
            })
            .catch(error => {
                console.error(error);
            });
    },

    sendInputEventToEditor: function (editorId, e) {
        var textarea = document.getElementById(editorId);
        setTimeout(function () {
            textarea.value = e.editor.getData();
        }, 300);
        textarea.dispatchEvent(new Event('input'))
    },

    initClassicEditorForStudentplayer: function (editorId, questionId) {
        return ClassicEditor
            .create(document.querySelector('#' + editorId),
                this.getConfigForStudent(false, [])
            )
            .then(editor => {
                ClassicEditors[editorId] = editor;
                const wordCountPlugin = editor.plugins.get('WordCount');
                const wordCountWrapper = document.getElementById('word-count-' + editorId);
                wordCountWrapper.appendChild(wordCountPlugin.wordCountContainer);
                if (typeof ReadspeakerTlc != 'undefined') {
                    ReadspeakerTlc.ckeditor.addListenersForReadspeaker(editor, questionId, editorId);
                    ReadspeakerTlc.ckeditor.disableContextMenuOnCkeditor();
                }
            })
            .catch(error => {
                console.error(error);
            });
    },
    getConfigForStudent: function (allowWsc, pluginsToAdd = []) {
        let config = {
            autosave: {
                waitingTime: 300,
                save(editor) {
                    editor.updateSourceElement();
                    editor.sourceElement.dispatchEvent(new Event('input'));
                }
            },
        };

        config.toolbar = {removeItems: []};

        if (allowWsc) {
            config.wproofreader = window.WEBSPELLCHECKER_CONFIG;
            config.removePlugins = ['Selection', 'Completion', 'ImageUpload', 'Image'];
            config.toolbar.removeItems = ['selection', 'completion', 'imageUpload', 'image'];
        } else {
            config.removePlugins = ['WProofreader', 'Selection', 'Completion', 'ImageUpload', 'Image'];
            config.toolbar.removeItems = ['wproofreader', 'selection', 'completion', 'imageUpload', 'image'];
        }
        return config;
    },
    getConfigForTeacher: function (allowWsc, pluginsToAdd = []) {
        let config = {
            autosave: {
                waitingTime: 300,
                save(editor) {
                    editor.updateSourceElement();
                    editor.sourceElement.dispatchEvent(new Event('input'));
                }
            },
            image: {
                upload: {
                    types: ['jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff']
                }
            },
            simpleUpload: {
                uploadUrl: '/cms/ckeditor_upload/images',
                // Enable the XMLHttpRequest.withCredentials property.
                withCredentials: true,
                // Headers sent along with the XMLHttpRequest to the upload server.
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="_token"]').content
                    // Authorization: 'Bearer <JSON Web Token>'
                }
            }
        };
        config.removePlugins = [];
        config.toolbar = {removeItems: []};
        if (allowWsc) {
            config.wproofreader = window.WEBSPELLCHECKER_CONFIG;
        } else {
            config.removePlugins = ['WProofreader'];
        }

        const availablePlugins = ['Selection', 'Completion'];
        const pluginsToRemove = availablePlugins.filter(plugin => !pluginsToAdd.includes(plugin));

        config.removePlugins = [...config.removePlugins, ...pluginsToRemove];
        config.toolbar.removeItems = pluginsToRemove.map(item => item.toLowerCase());

        return config;
    },
    initForTeacher: function (editorId, lang, allowWsc = false) {
        var editor = ClassicEditors[editorId];
        if (editor) {
            editor.destroy(true);
        }

        return ClassicEditor
            .create(
                document.getElementById(editorId),
                this.getConfigForTeacher(allowWsc)
            )
            .then(editor => {
                ClassicEditors[editorId] = editor;
                WebspellcheckerTlc.lang(editor, lang);
                // WebspellcheckerTlc.setEditorToReadOnly(editor);
                this.setReadOnly(editor);
            })
            .catch(error => {
                console.error(error);
            });
    },

    /** @TODO: this method should be refactored to setReadOnlyIfApplicable  but it has a reference in readspeaker_tlc.js which i dont want to test 1 day before deployment.*/
    setReadOnly: function (editor) {
        if (editor.sourceElement.hasAttribute('disabled')) {
            editor.isReadOnly = true;
            var editables = editor.ui.view.editable.element.querySelectorAll("[contenteditable=true]");
            editables.forEach(function (element) {
                element.setAttribute('contenteditable', false);
            });
        }
    },
    writeContentToTexarea: function (editorId) {
        var editor = ClassicEditors[editorId];
        if (editor) {
            editor.updateSourceElement();
            editor.sourceElement.dispatchEvent(new Event('input'));
        }
    }
}
