RichTextEditor = {
    initStudentCoLearning: function(parameterBag) {
        return this.createStudentEditor(
            parameterBag,
            (editor) => {
                this.setupWordCounter(editor, parameterBag);
                WebspellcheckerTlc.subscribeToProblemCounter(editor);
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
                WebspellcheckerTlc.handleSpellCheckerOnOff(editor, parameterBag.isSpellCheckerEnabled);
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
                WebspellcheckerTlc.handleSpellCheckerOnOff(editor, parameterBag.isSpellCheckerEnabled);
                this.setReadOnly(editor);
                window.editor = editor;
            }
        );
    },
    initClassicEditorForStudentPlayer: function(parameterBag) {
        return this.createStudentEditor(
            parameterBag,
            (editor) => {
                WebspellcheckerTlc.lang(editor, parameterBag.lang)
                this.setupWordCounter(editor, parameterBag);
                if (typeof ReadspeakerTlc != "undefined") {
                    editor.editing.view.document.on( 'change:isFocused', ( evt, data, isFocused ) => {
                        isFocused
                            ? rsTlcEvents.handleCkeditorFocusForReadspeaker(evt.target,parameterBag.questionId, parameterBag.editorId)
                            : rsTlcEvents.handleCkeditorBlurForReadspeaker(evt.target,parameterBag.questionId, parameterBag.editorId);
                    });
                    ReadspeakerTlc.ckeditor.addListenersForReadspeaker(editor, parameterBag.questionId, parameterBag.editorId);
                    ReadspeakerTlc.ckeditor.disableContextMenuOnCkeditor();
                }
            });

    },
    initClassicEditorForStudentPreviewplayer: function(parameterBag) {
        return this.createStudentEditor(
            parameterBag,
            (editor) => {
                WebspellcheckerTlc.lang(editor, parameterBag.lang)
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
                WebspellcheckerTlc.handleSpellCheckerOnOff(editor, parameterBag.isSpellCheckerEnabled);
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

                // this.hideWProofreaderChevron(parameterBag.allowWsc, editor);

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
                // this.hideWProofreaderChevron(parameterBag.allowWsc, editor);
            }
        );
    },
    initAnswerEditorWithComments: function(parameterBag) {
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
            /*
             * selection is in the answer comment editor
             * selection is not empty
             * selection is on the assessment screen
             * */
            if (window.getSelection().focusNode?.parentElement?.closest('.comment-editor') !== null
                && document.querySelector('#assessment-page') !== null
                && window.getSelection().toString() !== ''
            ) {
                dispatchEvent(new CustomEvent('assessment-drawer-tab-update', {detail: {tab: 2}}));

                //focus the create a comment editor
                dispatchEvent(new CustomEvent('answer-feedback-focus-feedback-editor'));

                setTimeout(() => {
                    editor.execute('addCommentThread', {threadId: window.uuidv4()});

                }, 200);
            }
        })
    },
    //only needed when webspellchecker has to be re-added to the inline-feedback comment editors
    // hideWProofreaderChevron: function (allowWsc, editor) {
    //
    //     if(!allowWsc) {
    //         return;
    //     }
    //
    //     const callback = (element) => {
    //         return element.innerHTML == 'WProofreader' && element.classList.contains('ck-tooltip__text')
    //     }
    //
    //     // const elements = Array.from(document.getElementsByTagName('span'))
    //     const elements = Array.from(editor.editing.view.getDomRoot().closest('.ck-editor').getElementsByTagName('span'))
    //
    //     elements.filter(callback).forEach((element) => {
    //         return element.parentElement.parentElement.querySelector('.ck-dropdown__arrow').style.display = 'none';
    //     });
    //
    // },
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
            wproofreader: this.getWproofreaderConfig(parameterBag.enableGrammar)
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
                displayCharacters: false,
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

        if(!parameterBag.restrictWords || [null, 0].includes(parameterBag.maxWords)) {
            return;
        }

        editor.maxWords = parameterBag.maxWords;
        editor.maxWordOverride = parameterBag.maxWordOverride;
        this.handleInputWithMaxWords(editor);

        editor.updateMaxWords = (value) => {
            editor.maxWords = parseInt(value);
            this.handleInputWithMaxWords(editor);
        };
        editor.model.document.on("change:data", (event, batch) => {

            if (this.hasNoWordLimit(editor)) return;
            let wc = editor.plugins.get("WordCount");

            if (wc.words > editor.maxWords) {
                editor.execute('undo');
            }

            this.handleInputWithMaxWords(editor, event);
        });
        editor.editing.view.document.on("paste", (event, data) => {
            if (this.hasNoWordLimit(editor)) return;
            let wc = editor.plugins.get("WordCount");
            let maxWords = parseInt(editor.maxWords);

            if (wc.words >= maxWords) { //always the old number of words. never triggers when pasting at 49/50 words
                data.preventDefault();
                event.stop();
            } else {
                editor.pasted = true;
                editor.prePasteData = editor.getData();
                editor.prePasteWc = wc.words;
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
        const enterKeyCommand = editor.commands.get("enter");
        const wc = editor.plugins.get("WordCount");
        const maxWords = parseInt(editor.maxWords);

        editor.disableSpacers = wc.words >= maxWords;

        if (wc.words > maxWords) {
            input.forceDisabled("maxword-lock");
            enterKeyCommand.forceDisabled("maxword-lock");
            handlePastedData();
        } else {
            if (wc.words == maxWords ) {
                enterKeyCommand.forceDisabled("maxword-lock");
            } else {
                enterKeyCommand.clearForceDisabled("maxword-lock");
            }
            input.clearForceDisabled("maxword-lock");
            editor.pasted = false;
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
            editor.disableSpacers = editor.prePasteWc >= maxWords;

            if(editor.prePasteWc < maxWords) {
                input.clearForceDisabled('maxword-lock');
            }
        }
    },
    hasNoWordLimit(editor) {
        return editor.maxWords === null || editor.maxWordOverride;
    },
    getWproofreaderConfig: function(enableGrammar = true) {
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
            srcUrl: "https://wsc.test-correct.nl/wscservice/wscbundle/wscbundle.js",
            enableGrammar: enableGrammar
        };
    },

    async createEditor(editorId, config, resolveCallback = null) {
        let editor = ClassicEditors[editorId];
        try {
            if (editor) await editor.destroy(true);
        } catch (e) {
            console.warn('An issue occurred while destroying an existing editor.')
        }

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
    writeContentToTexarea: function(editorId) {
        var editor = ClassicEditors[editorId];
        if (editor) {
            editor.updateSourceElement();
            editor.sourceElement.dispatchEvent(new Event("input"));
        }
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
