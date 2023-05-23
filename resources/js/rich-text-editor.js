RichTextEditor = {
    initStudent: function(editorId) {
        console.log("this should implement student init // example is open-medium-question.blase.php");
    },

    initStudentCoLearning: function(parameterBag) {
        return ClassicEditor
            .create(document.querySelector("#" + parameterBag.editorId),
                this.getConfigForStudent(parameterBag.wsc, [])
            )
            .then(editor => {
                ClassicEditors[parameterBag.editorId] = editor;
                this.setupWordCounter(editor, parameterBag.editorId);
                WebspellcheckerTlc.forTeacherQuestion(editor, parameterBag.lang, parameterBag.wsc);

                window.addEventListener("wsc-problems-count-updated-" + parameterBag.editorId, (e) => {
                    let problemCountSpan = document.getElementById("problem-count-" + parameterBag.editorId);
                    if (problemCountSpan) {
                        problemCountSpan.textContent = e.detail.problemsCount;
                    }
                });
                if (typeof ReadspeakerTlc != "undefined") {
                    ReadspeakerTlc.ckeditor.addListenersForReadspeaker(editor, parameterBag.questionId, parameterBag.editorId);
                    ReadspeakerTlc.ckeditor.disableContextMenuOnCkeditor();
                }
            })
            .catch(error => {
                console.error(error);
            });
    },


    initSelectionCMS: function(parameterBag) {
        var editor = ClassicEditors[parameterBag.editorId];
        if (editor) {
            editor.destroy(true);
        }

        return ClassicEditor
            .create(
                document.getElementById(parameterBag.editorId),
                this.getConfigForTeacher(parameterBag.allowWsc, ["Selection"])
            )
            .then(editor => {
                ClassicEditors[parameterBag.editorId] = editor;
                WebspellcheckerTlc.lang(editor, parameterBag.lang);
                // WebspellcheckerTlc.setEditorToReadOnly(editor);
                this.setReadOnly(editor);
                window.editor = editor;
            })
            .catch(error => {
                console.error(error);
            });
    },
    initCompletionCMS: function(parameterBag) {
        var editor = ClassicEditors[parameterBag.editorId];
        if (editor) {
            editor.destroy(true);
        }

        return ClassicEditor
            .create(
                document.getElementById(parameterBag.editorId),
                this.getConfigForTeacher(parameterBag.allowWsc, ["Completion"])
            )
            .then(editor => {
                ClassicEditors[parameterBag.editorId] = editor;

                WebspellcheckerTlc.lang(editor, parameterBag.lang);
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

    initClassicEditorForStudentplayer: function(parameterBag) {
        return ClassicEditor
            .create(document.querySelector("#" + parameterBag.editorId),
                this.getConfigForStudent(parameterBag.allowWsc, [])
            )
            .then(editor => {
                ClassicEditors[parameterBag.editorId] = editor;
                this.setupWordCounter(editor, parameterBag.editorId);
                if (typeof ReadspeakerTlc != "undefined") {
                    ReadspeakerTlc.ckeditor.addListenersForReadspeaker(editor, parameterBag.questionId, parameterBag.editorId);
                    ReadspeakerTlc.ckeditor.disableContextMenuOnCkeditor();
                }
            })
            .catch(error => {
                console.error(error);
            });
    },
    initClassicEditorForStudentPreviewplayer: function(parameterBag) {
        return ClassicEditor
            .create(document.querySelector("#" + parameterBag.editorId),
                this.getConfigForStudent(false, [])
            )
            .then(editor => {
                ClassicEditors[parameterBag.editorId] = editor;
                this.setupWordCounter(editor, parameterBag.editorId);
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
        removeItems = null,
        maxWords = -1
    ) {
        removeItems ??= {
            plugins: [],
            items: []
        };

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
                },
                toolbar: [
                    "imageTextAlternative",
                    // 'toggleImageCaption',
                    "|",
                    "imageStyle:inline",
                    {
                        // Grouping into one drop-down.
                        name: "wrapText",
                        title: "Tekstterugloop",
                        items: [
                            "imageStyle:alignLeft",
                            "imageStyle:alignRight"
                        ],
                        defaultItem: "imageStyle:alignLeft"
                    },
                    {
                        // Grouping into one drop-down.
                        name: "breakText",
                        title: "Tekst onderbreken",
                        items: [
                            "imageStyle:alignBlockLeft",
                            "imageStyle:alignCenter",
                            "imageStyle:alignBlockRight"
                        ],
                        defaultItem: "imageStyle:alignBlockLeft"
                    },
                    "imageStyle:side",
                    "|",
                    "resizeImage"
                ]
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
            wordCount: {
                showWordCount: true,
                showParagraphs: false,
                showCharCount: true,
                countSpacesAsChars: true,
                maxWordCount: maxWords
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
    initForTeacher: function(parameterBag) {

        const editor = ClassicEditors[parameterBag.editorId];
        if (editor) {
            editor.destroy(true);
        }

        return ClassicEditor
            .create(
                document.getElementById(parameterBag.editorId),
                this.getConfigForTeacher(parameterBag.allowWsc, [], null, parameterBag.maxWords)
            )
            .then(editor => {
                ClassicEditors[parameterBag.editorId] = editor;
                WebspellcheckerTlc.lang(editor, parameterBag.lang);
                this.setupWordCounter(editor, parameterBag.editorId, parameterBag.maxWords, parameterBag.maxWordOverride);
                // WebspellcheckerTlc.setEditorToReadOnly(editor);
                this.setReadOnly(editor);
            })
            .catch(error => {
                console.error(error);
            });
    },
    initAssessmentFeedback: function(parameterBag) {
        var editor = ClassicEditors[parameterBag.editorId];
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
                "Selection"
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
        let config = this.getConfigForTeacher(parameterBag.allowWsc, [], itemsToRemove, true);
        config.toolbar.shouldNotGroupWhenFull = true;

        return ClassicEditor
            .create(
                document.getElementById(parameterBag.editorId),
                config
            )
            .then(editor => {
                ClassicEditors[parameterBag.editorId] = editor;
            })
            .catch(error => {
                console.error(error);
            });
    },
    initInlineFeedback: function(parameterBag) {
        let editor = ClassicEditors[parameterBag.editorId];
        if (editor) {
            editor.destroy(true);
        }

        return ClassicEditor
            .create(
                document.getElementById(parameterBag.editorId),
                this.getConfigForTeacher(parameterBag.allowWsc)
            )
            .then(editor => {
                ClassicEditors[parameterBag.editorId] = editor;
                this.setupWordCounter(editor, parameterBag.editorId);
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
    writeContentToTextarea: function(editorId) {
        var editor = ClassicEditors[editorId];
        if (editor) {
            editor.updateSourceElement();
            editor.sourceElement.dispatchEvent(new Event("input"));
        }
    },
    setupWordCounter: function(editor, editorId, maxWords = null, override = false) {
        const wordCountPlugin = editor.plugins.get("WordCount");
        const wordCountWrapper = document.getElementById("word-count-" + editorId);
        if (wordCountWrapper) {
            wordCountWrapper.appendChild(wordCountPlugin.wordCountContainer);
        }

        if (maxWords) {
            editor.maxWords = maxWords;

            wordCountPlugin.on("update", (evt, stats) => {
                console.log(evt);
                console.log(override);
                if (override) {
                    return;
                }
                const limitExceeded = stats.words > editor.maxWords;


                console.log(`Characters: ${stats.characters}\nWords:      ${stats.words}\nmax:      ${editor.maxWords}`);
            });
        }
    }
};
