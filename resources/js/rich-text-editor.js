RichTextEditor = {
    initStudent: function(editorId) {
        console.log("this should implement student init // example is open-medium-question.blase.php");
    },

    initStudentCoLearning: function(editorId, lang = "nl_NL", wsc = false) {
        return ClassicEditor
            .create(document.querySelector("#" + editorId),
                this.getConfigForStudent(wsc, [])
            )
            .then(editor => {
                ClassicEditors[editorId] = editor;
                this.setupWordCounter(editor, editorId);
                WebspellcheckerTlc.forTeacherQuestion(editor, lang, wsc);

                window.addEventListener("wsc-problems-count-updated-" + editorId, (e) => {
                    let problemCountSpan = document.getElementById("problem-count-" + editorId);
                    if (problemCountSpan) {
                        problemCountSpan.textContent = e.detail.problemsCount;
                    }
                });
                if (typeof ReadspeakerTlc != "undefined") {
                    ReadspeakerTlc.ckeditor.addListenersForReadspeaker(editor, questionId, editorId);
                    ReadspeakerTlc.ckeditor.disableContextMenuOnCkeditor();
                }
            })
            .catch(error => {
                console.error(error);
            });
    },


    initSelectionCMS: function(editorId, lang = "nl_NL", allowWsc = false) {
        var editor = ClassicEditors[editorId];
        if (editor) {
            editor.destroy(true);
        }

        return ClassicEditor
            .create(
                document.getElementById(editorId),
                this.getConfigForTeacher(allowWsc, ["Selection"])
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
    initCompletionCMS: function(editorId, lang, allowWsc = false) {
        var editor = ClassicEditors[editorId];
        if (editor) {
            editor.destroy(true);
        }

        return ClassicEditor
            .create(
                document.getElementById(editorId),
                this.getConfigForTeacher(allowWsc, ["Completion"])
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

    sendInputEventToEditor: function(editorId, e) {
        var textarea = document.getElementById(editorId);
        setTimeout(function() {
            textarea.value = e.editor.getData();
        }, 300);
        textarea.dispatchEvent(new Event("input"));
    },

    initClassicEditorForStudentplayer: function (editorId, questionId, allowWsc = false) {
        return ClassicEditor
            .create(document.querySelector("#" + editorId),
                this.getConfigForStudent(allowWsc, [])
            )
            .then(editor => {
                ClassicEditors[editorId] = editor;
                this.setupWordCounter(editor, editorId);
                if (typeof ReadspeakerTlc != "undefined") {
                    ReadspeakerTlc.ckeditor.addListenersForReadspeaker(editor, questionId, editorId);
                    ReadspeakerTlc.ckeditor.disableContextMenuOnCkeditor();
                }
            })
            .catch(error => {
                console.error(error);
            });
    },
    initClassicEditorForStudentPreviewplayer: function(editorId, questionId) {
        return ClassicEditor
            .create(document.querySelector("#" + editorId),
                this.getConfigForStudent(false, [])
            )
            .then(editor => {
                ClassicEditors[editorId] = editor;
                this.setupWordCounter(editor, editorId);
                if (typeof ReadspeakerTlc != "undefined") {
                    ReadspeakerTlc.ckeditor.replaceReadableAreaByClone(editor);
                }
                editor.isReadOnly = true;
            })
            .catch(error => {
                console.error(error);
            });
    },

    getConfigForStudent: function(allowWsc, pluginsToAdd = []) {
        let config = {
            autosave: {
                waitingTime: 300,
                save(editor) {
                    editor.updateSourceElement();
                    editor.sourceElement.dispatchEvent(new Event("input"));
                }
            },
            wordcount: {
                showWordCount: true,
                showParagraphs: false,
                showCharCount: true,
                countSpacesAsChars: true
            }
        };

        config.toolbar = { removeItems: [] };

        if (allowWsc) {
            config.wproofreader = {
                autoSearch: false,
                autoDestroy: true,
                autocorrect: false,
                autocomplete: false,
                actionItems: ["addWord", "ignoreAll", "ignore", "settings", "toggle", "proofreadDialog"],
                enableBadgeButton: true,
                serviceProtocol: "https",
                servicePort: "80",
                serviceHost: "wsc.test-correct.nl",
                servicePath: "wscservice/api",
                srcUrl: "https://wsc.test-correct.nl/wscservice/wscbundle/wscbundle.js"
            };

            config.removePlugins = ["Selection", "Completion", "ImageUpload", "Image"];
            config.toolbar.removeItems = ["selection", "completion", "imageUpload", "image"];
        } else {
            config.removePlugins = ["WProofreader", "Selection", "Completion", "ImageUpload", "Image"];
            config.toolbar.removeItems = ["wproofreader", "selection", "completion", "imageUpload", "image"];
        }
        return config;
    },
    getConfigForTeacher: function(
        allowWsc,
        pluginsToAdd = [],
        removeItems = {
            plugins: [],
            items: []
        }) {
        let config = {
            autosave: {
                waitingTime: 300,
                save(editor) {
                    editor.updateSourceElement();
                    editor.sourceElement.dispatchEvent(new Event("input"));
                }
            },
            image: {
                upload: {
                    types: ["jpeg", "png", "gif", "bmp", "webp", "tiff"]
                }
            },
            simpleUpload: {
                uploadUrl: "/cms/ckeditor_upload/images",
                // Enable the XMLHttpRequest.withCredentials property.
                withCredentials: true,
                // Headers sent along with the XMLHttpRequest to the upload server.
                headers: {
                    "X-CSRF-TOKEN": document.querySelector("meta[name=\"_token\"]").content
                    // Authorization: 'Bearer <JSON Web Token>'
                }
            },
            wordcount: {
                showWordCount: true,
                showParagraphs: false,
                showCharCount: true,
                countSpacesAsChars: true
            }
        };
        config.removePlugins = removeItems?.plugins ?? [];
        config.toolbar = { removeItems: removeItems?.toolbar ?? [] };
        if (allowWsc) {
            config.wproofreader = {
                autoSearch: false,
                autoDestroy: true,
                autocorrect: false,
                autocomplete: false,
                actionItems: ["addWord", "ignoreAll", "ignore", "settings", "toggle", "proofreadDialog"],
                enableBadgeButton: true,
                serviceProtocol: "https",
                servicePort: "80",
                serviceHost: "wsc.test-correct.nl",
                servicePath: "wscservice/api",
                srcUrl: "https://wsc.test-correct.nl/wscservice/wscbundle/wscbundle.js"
            };
        } else {
            config.removePlugins.push("WProofreader");
        }

        const availablePlugins = ["Selection", "Completion"];
        const pluginsToRemove = availablePlugins.filter(plugin => !pluginsToAdd.includes(plugin));

        config.removePlugins = [...config.removePlugins, ...pluginsToRemove];
        config.toolbar.removeItems = [...config.toolbar.removeItems, ...config.removePlugins.map(item => item.toLowerCase())];

        return config;
    },
    initForTeacher: function(editorId, lang, allowWsc = false) {
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
    initAssessmentFeedback: function(editorId, lang, allowWsc = false) {
        var editor = ClassicEditors[editorId];
        if (editor) {
            editor.destroy(true);
        }

        let itemsToRemove = {
            plugins: [
                "Essentials",
                "FontFamily",
                "FontSize",
                "FontBackgroundColor",
                "Heading",
                "Indent",
                "FontColor",
                "RemoveFormat",
                "PasteFromOffice",
                "WordCount",
                "WProofreader",
                "Completion",
                "Selection",
            ],
            toolbar: [
                "outdent",
                "indent",
                "completion",
                "selection",
                "fontFamily",
                "fontBackgroundColor",
                "fontSize",
                "undo",
                "redo",
                "fontColor",
                "heading",
                "removeFormat",
                "wproofreader",
                "specialCharacters"
            ]
        };
        let config = this.getConfigForTeacher(allowWsc, [], itemsToRemove, true);
        config.toolbar.shouldNotGroupWhenFull = true;

        return ClassicEditor
            .create(
                document.getElementById(editorId),
                config
            )
            .then(editor => {
                ClassicEditors[editorId] = editor;
            })
            .catch(error => {
                console.error(error);
            });
    },
    initInlineFeedback: function(editorId, lang, allowWsc = false) {
        let editor = ClassicEditors[editorId];
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
                this.setupWordCounter(editor, editorId);
            })
            .catch(error => {
                console.error(error);
            });
    },

    /** @TODO: this method should be refactored to setReadOnlyIfApplicable  but it has a reference in readspeaker_tlc.js which i dont want to test 1 day before deployment.*/
    setReadOnly: function(editor) {
        if (editor.sourceElement.hasAttribute("disabled")) {
            editor.isReadOnly = true;
            var editables = editor.ui.view.editable.element.querySelectorAll("[contenteditable=true]");
            editables.forEach(function(element) {
                element.setAttribute("contenteditable", false);
            });
        }
    },
    writeContentToTexarea: function(editorId) {
        var editor = ClassicEditors[editorId];
        if (editor) {
            editor.updateSourceElement();
            editor.sourceElement.dispatchEvent(new Event("input"));
        }
    },
    setupWordCounter: function(editor, editorId) {
        const wordCountPlugin = editor.plugins.get("WordCount");
        const wordCountWrapper = document.getElementById("word-count-" + editorId);
        wordCountWrapper.appendChild(wordCountPlugin.wordCountContainer);
    },
};
