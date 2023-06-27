RichTextEditor = {
    initStudentCoLearning: function(parameterBag) {
        return this.createStudentEditor(
            parameterBag,
            (editor) => {
                this.setupWordCounter(editor, parameterBag);
                WebspellcheckerTlc.forTeacherQuestion(editor, parameterBag.lang, parameterBag.allowWsc);
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
            }
        )
    },
    initSelectionCMS: function(parameterBag) {
        parameterBag.pluginsToAdd = ['Selection'];
        return this.createTeacherEditor(
            parameterBag,
            (editor) => {
                WebspellcheckerTlc.lang(editor, parameterBag.lang);
                this.setReadOnly(editor);
                window.editor = editor;
            }
        );
    },
    initCompletionCMS: function(parameterBag) {
        parameterBag.pluginsToAdd = ["Completion"];
        return this.createTeacherEditor(
            parameterBag,
            (editor) => {
                WebspellcheckerTlc.lang(editor, parameterBag.lang);
                this.setReadOnly(editor);
            }
        );
    },
    initClassicEditorForStudentPlayer: function(parameterBag) {
        return this.createStudentEditor(
            parameterBag,
            (editor) => {
                this.setupWordCounter(editor, parameterBag);
                if (typeof ReadspeakerTlc != "undefined") {
                    ReadspeakerTlc.ckeditor.addListenersForReadspeaker(editor, parameterBag.questionId, parameterBag.editorId);
                    ReadspeakerTlc.ckeditor.disableContextMenuOnCkeditor();
                }
            });

    },
    initClassicEditorForStudentPreviewplayer: function(parameterBag) {
        return this.createStudentEditor(
            parameterBag,
            (editor) => {
                this.setupWordCounter(editor, parameterBag);
                if (typeof ReadspeakerTlc != "undefined") {
                    ReadspeakerTlc.ckeditor.replaceReadableAreaByClone(editor);
                }
                editor.isReadOnly = true;
            }
        )
    },
    initForTeacher: function(parameterBag) {
        return this.createTeacherEditor(
            parameterBag,
            (editor) => {
                WebspellcheckerTlc.lang(editor, parameterBag.lang);
                this.setupWordCounter(editor, parameterBag);
                this.setReadOnly(editor);
            }
        )
    },
    initAssessmentFeedback: function(parameterBag) {
        this.setAnswerFeedbackItemsToRemove(parameterBag);
        parameterBag.shouldNotGroupWhenFull = true;

        return this.createTeacherEditor(
            parameterBag,
        );
    },
    initUpdateAnswerFeedbackEditor: function(parameterBag) {
        this.setAnswerFeedbackItemsToRemove(parameterBag);
        parameterBag.shouldNotGroupWhenFull = true;

        return this.createTeacherEditor(
            parameterBag,
            (editor) => {

                this.hideWProofreaderChevron(parameterBag.allowWsc, editor);

            },
        );
    },
    initCreateAnswerFeedbackEditor: function(parameterBag) {
        this.setAnswerFeedbackItemsToRemove(parameterBag);
        parameterBag.shouldNotGroupWhenFull = true;

        return this.createTeacherEditor(
            parameterBag,
            (editor) => {
                window.addEventListener('answer-feedback-focus-feedback-editor', () => {
                    setTimeout(() => {
                        editor.focus();
                    }, 100)
                });
                this.hideWProofreaderChevron(parameterBag.allowWsc, editor);
            }
        );
    },
    // initInlineFeedback: function(parameterBag) {
    //     return this.createStudentEditor(
    //         parameterBag,
    //         (editor) => this.setupWordCounter(editor, parameterBag)
    //     );
    // },
    initAnswerEditorWithComments: function(parameterBag) {

        //todo:
        return this.createStudentEditor(
            parameterBag,
            (editor) => {
                WebspellcheckerTlc.lang(editor, parameterBag.lang);
                this.setupWordCounter(editor, parameterBag);
                this.setCommentsOnly(editor); //replaces read-only
                this.setAnswerFeedbackEventListeners(editor);
            }
        )
    },
    setAnswerFeedbackEventListeners: function (editor) {
        editor.ui.view.editable.element.onblur = (e) => {
            //create a temporary commentThread to mark the selection while creating a new comment
            // editor.execute( 'addCommentThread', { threadId: window.uuidv4() } );

        }
        document.addEventListener('mouseup', (e) => {
            if(window.getSelection().focusNode.parentElement.closest('.comment-editor') !== null) {
                //selection is in the answer comment editor
                if(window.getSelection().toString() !== '') {
                    dispatchEvent(new CustomEvent('assessment-drawer-tab-update', {detail: {tab: 2}}));

                    //focus the create a comment editor
                    dispatchEvent(new CustomEvent('answer-feedback-focus-feedback-editor'));

                    setTimeout(() => {
                        editor.execute( 'addCommentThread', { threadId: window.uuidv4() } );

                    }, 200)
                }
            }
        })
    },
    hideWProofreaderChevron: function (allowWsc, editor) {

        if(!allowWsc) {
            return;
        }

        const callback = (element) => {
            return element.innerHTML == 'WProofreader' && element.classList.contains('ck-tooltip__text')
        }

        // const elements = Array.from(document.getElementsByTagName('span'))
        const elements = Array.from(editor.editing.view.getDomRoot().closest('.ck-editor').getElementsByTagName('span'))

        elements.filter(callback).forEach((element) => {
            return element.parentElement.parentElement.querySelector('.ck-dropdown__arrow').style.display = 'none';
        });

    },
    getConfigForStudent: function(parameterBag) {
        parameterBag.pluginsToAdd ??= [];

        let config = {
            autosave: {
                waitingTime: 300,
                save(editor) {
                    editor.updateSourceElement();
                    editor.sourceElement.dispatchEvent(new Event("input"));
                }
            },
            wordCount: {
                displayCharacters: false
            },
            wproofreader: this.getWproofreaderConfig()
        };

        config.removePlugins = ["Selection", "Completion", "ImageUpload", "Image", "ImageToolbar"];
        config.toolbar = {
            removeItems: ["selection", "completion", "imageUpload", "image"]
        };

        if (!parameterBag.allowWsc) {
            delete config.wproofreader;
            config.removePlugins.push("WProofreader");
            config.toolbar.removeItems.push("wproofreader");
        }
        if (!parameterBag.textFormatting) {
            config.removePlugins.push(
                "Bold",
                "BlockQuote",
                "FontFamily",
                "FontSize",
                "FontBackgroundColor",
                "Heading",
                "Indent",
                "Italic",
                "List",
                "Strikethrough",
                "FontColor",
                "Subscript",
                "Superscript",
                "BlockQuote",
                "Table",
                "TableCaption",
                "TableCellProperties",
                "TableProperties",
                "TableToolbar",
                "Underline",
                "RemoveFormat"
            );
            config.toolbar.removeItems.push(
                "bold",
                "italic",
                "underline",
                "strikethrough",
                "subscript",
                "superscript",
                "bulletedList",
                "numberedList",
                "blockQuote",
                "outdent",
                "indent",
                "insertTable",
                "fontFamily",
                "fontBackgroundColor",
                "fontSize",
                "fontColor",
                "heading",
                "removeFormat"
            );
        }
        if (!parameterBag.mathmlFunctions) {
            config.removePlugins.push("MathType", "ChemType", "SpecialCharactersTLC");
            config.toolbar.removeItems.push("MathType", "ChemType", "specialCharacters");
        }

        if (parameterBag.commentThreads != undefined) {
            config.extraPlugins = [ CommentsIntegration ];

            config.commentsIntegration = {
                userId: parameterBag.userId,
                users: parameterBag.users,
                commentThreads: parameterBag.commentThreads,
            };
        }

        return config;
    },
    getConfigForTeacher: function(parameterBag) {
        parameterBag.pluginsToAdd ??= [];
        parameterBag.removeItems ??= {
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
                displayCharacters: true,
                displayWords: true
            },
            wproofreader: this.getWproofreaderConfig(),
        };
        config.removePlugins = parameterBag.removeItems?.plugins ?? [];
        config.toolbar = {
            removeItems: parameterBag.removeItems?.toolbar ?? [],
        };
        if(parameterBag.toolbar) {
            config.toolbar.items = parameterBag.toolbar;
        }

        if (parameterBag.commentThreads != undefined) {
            config.extraPlugins = [ CommentsIntegration ];

            config.commentsIntegration = {
                userId: parameterBag.userId,
                users: parameterBag.users,
                commentThreads: parameterBag.commentThreads,
            };
        }

        if (!parameterBag.allowWsc) {
            delete config.wproofreader;
            config.removePlugins.push("WProofreader");
        }
        if (parameterBag.shouldNotGroupWhenFull) {
            config.toolbar.shouldNotGroupWhenFull = true;
        }

        const availablePlugins = ["Selection", "Completion"];
        const pluginsToRemove = availablePlugins.filter(plugin => !parameterBag.pluginsToAdd.includes(plugin));

        config.removePlugins = [...config.removePlugins, ...pluginsToRemove];
        config.toolbar.removeItems = [...config.toolbar.removeItems, ...config.removePlugins.map(item => item.toLowerCase())];

        return config;
    },

    sendInputEventToEditor: function(editorId, e) {
        var textarea = document.getElementById(editorId);
        setTimeout(function() {
            textarea.value = e.editor.getData();
        }, 300);
        textarea.dispatchEvent(new Event("input"));
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
    setCommentsOnly: function(editor) {
        editor.plugins.get( 'CommentsOnly' ).isEnabled = true;
    },
    writeContentToTextarea: function(editorId) {
        var editor = ClassicEditors[editorId];
        if (editor) {
            editor.updateSourceElement();
            editor.sourceElement.dispatchEvent(new Event("input"));
        }
    },
    setupWordCounter: function(editor, parameterBag) {
        const wordCountPlugin = editor.plugins.get("WordCount");
        const wordCountWrapper = document.getElementById("word-count-" + parameterBag.editorId);
        if (wordCountWrapper) {
            wordCountWrapper.appendChild(wordCountPlugin.wordCountContainer);
            window.dispatchEvent(new CustomEvent("updated-word-count-plugin-container"));
        }

        editor.maxWords = parameterBag.maxWords;
        editor.maxWordOverride = parameterBag.maxWordOverride;
        this.handleInputWithMaxWords(editor);

        editor.updateMaxWords = (value) => {
            editor.maxWords = parseInt(value);
            this.handleInputWithMaxWords(editor);
        };
        editor.model.document.on("change:data", (event, batch) => {
            this.handleInputWithMaxWords(editor, event);
        });
        editor.editing.view.document.on("paste", (event, data) => {
            if (this.hasNoWordLimit(editor)) return;
            let wc = editor.plugins.get("WordCount");
            let maxWords = parseInt(editor.maxWords);

            if (wc.words >= maxWords) {
                data.preventDefault();
                event.stop();
            } else {
                editor.pasted = true;
                editor.prePasteData = editor.getData();
            }
        });
        editor.editing.view.document.on("keydown", (event, data) => {
            if (this.hasNoWordLimit(editor)) return;
            if (!editor.disableSpacers) return;

            /* Disable spacebar and enter inputs so new words cannot be created;*/
            if ([32, 13].includes(data.keyCode)) {
                data.preventDefault();
                event.stop();
            }
        });
    },
    handleInputWithMaxWords: function(editor) {
        if (this.hasNoWordLimit(editor)) return;
        if (editor.preventUpdateLoop) {
            editor.preventUpdateLoop = false;
            return;
        }

        const input = editor.commands.get("input");
        const wc = editor.plugins.get("WordCount");
        const maxWords = parseInt(editor.maxWords);

        editor.disableSpacers = wc.words >= maxWords;

        if (wc.words > maxWords) {
            input.forceDisabled("maxword-lock");
            handlePastedData();
        } else {
            input.clearForceDisabled("maxword-lock");
        }

        function handlePastedData() {
            if (!editor.pasted) return;

            editor.setData(editor.prePasteData);
            editor.preventUpdateLoop = true;
            editor.pasted = false;
            setTimeout(() => {
                editor.model.change(writer => {
                    writer.setSelection(editor.model.document.getRoot(), "end");
                });
            }, 1);

        }
    },
    hasNoWordLimit(editor) {
        return editor.maxWords === null || editor.maxWordOverride;
    },
    getWproofreaderConfig: function() {
        return {
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
    },

    createEditor(editorId, config, resolveCallback = null) {
        let editor = ClassicEditors[editorId];
        if (editor) editor.destroy(true);

        return ClassicEditor
            .create(
                document.getElementById(editorId),
                config
            )
            .then(editor => {
                ClassicEditors[editorId] = editor;
                if (typeof resolveCallback === "function") {
                    resolveCallback(editor);
                }
            })
            .catch(error => {
                console.error(error);
            });
    },
    async createTeacherEditor(parameterBag, resolveCallback = null) {
        return await this.createEditor(
            parameterBag.editorId,
            this.getConfigForTeacher(parameterBag),
            resolveCallback
        );

    },
    async createStudentEditor(parameterBag, resolveCallback = null) {
        return await this.createEditor(
            parameterBag.editorId,
            this.getConfigForStudent(parameterBag),
            resolveCallback
        );
    },
    setAnswerFeedbackItemsToRemove: function (parameterBag) {
        parameterBag.removeItems = {
            plugins: [
                "FontFamily",
                "FontSize",
                "FontBackgroundColor",
                "Heading",
                "Indent",
                "FontColor",
                "RemoveFormat",
                "PasteFromOffice",
                "WordCount",
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
                "fontColor",
                "heading",
                "removeFormat",
                "specialCharacters",
                "insertTable",
                "imageUpload",
                'underline',
                'strikethrough',
                'subscript',
                'superscript',
                'bulletedList',
                'numberedList',
                'blockQuote',
            ]
        };
        parameterBag.toolbar = [
            "undo",
            "redo",
            "|",
            "bold",
            "italic",
            'MathType',
            'ChemType',
            'wproofreader',
        ]
    },
};
