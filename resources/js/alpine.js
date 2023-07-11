import Alpine from "alpinejs";
import Choices from "choices.js";
import Intersect from "@alpinejs/intersect";
import focus from "@alpinejs/focus";
import Clipboard from "@ryangjchandler/alpine-clipboard";
import collapse from "@alpinejs/collapse";
import { isString } from "lodash";

window.Alpine = Alpine;
Alpine.plugin(Clipboard);
Alpine.plugin(Intersect);
Alpine.plugin(collapse);
Alpine.plugin(focus);

document.addEventListener("alpine:init", () => {
    Alpine.data("questionIndicator", () => ({
        showSlider: false,
        scrollStep: 100,
        totalScrollWidth: 0,
        activeQuestion: window.Livewire.find(document.querySelector("[test-take-player]").getAttribute("wire:id")).entangle("q")
    }));
    Alpine.data("tagManager", () => ({
        tags: [],
        remove: function(index) {
            this.tags.splice(index, 1);
        },
        add: function(inputElement) {
            if (inputElement.value) {
                this.tags.push(inputElement.value);
                inputElement.value = "";
            }
        }
    }));
    Alpine.data("selectSearch", (config) => ({

        data: config.data,

        emptyOptionsMessage: config.emptyOptionsMessage ?? "No results match your search.",

        focusedOptionIndex: null,

        name: config.name,

        open: false,

        options: {},

        placeholder: config.placeholder ?? "Select an option",

        search: "",

        value: config.value,

        closeListbox: function() {
            this.open = false;

            this.focusedOptionIndex = null;

            this.search = "";
        },

        focusNextOption: function() {
            if (this.focusedOptionIndex === null) return this.focusedOptionIndex = Object.keys(this.options).length - 1;

            if (this.focusedOptionIndex + 1 >= Object.keys(this.options).length) return;

            this.focusedOptionIndex++;

            this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                block: "center"
            });
        },

        focusPreviousOption: function() {
            if (this.focusedOptionIndex === null) return this.focusedOptionIndex = 0;

            if (this.focusedOptionIndex <= 0) return;

            this.focusedOptionIndex--;

            this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                block: "center"
            });
        },

        init: function() {
            this.options = this.data;

            if (!(this.value in this.options)) this.value = null;

            this.$watch("search", ((value) => {
                if (!this.open || !value) return this.options = this.data;

                this.options = Object.keys(this.data)
                    .filter((key) => this.data[key].toLowerCase().includes(value.toLowerCase()))
                    .reduce((options, key) => {
                        options[key] = this.data[key];
                        return options;
                    }, {});
            }));
        },

        selectOption: function() {
            if (!this.open) return this.toggleListboxVisibility();

            this.value = Object.keys(this.options)[this.focusedOptionIndex];

            this.closeListbox();
        },

        toggleListboxVisibility: function() {
            if (this.open) return this.closeListbox();

            this.focusedOptionIndex = Object.keys(this.options).indexOf(this.value);

            if (this.focusedOptionIndex < 0) this.focusedOptionIndex = 0;

            this.open = true;

            // this.$nextTick(() => {
            setTimeout(() => {
                this.$refs.search.focus();

                this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                    block: "center"
                });
            }, 10);
            // })
        }
    }));
    Alpine.data("completionOptions", (entangle) => ({
        showPopup: entangle.value,
        editorId: entangle.editorId,
        hasError: { empty: [] },
        data: {
            elements: []
        },
        maxOptions: 10,
        minOptions: 1,

        init() {
            for (let i = 0; i < this.minOptions; i++) {
                this.addRow();
            }
        },

        initWithCompletion() {
            let editor = window.editor;
            // let selection = editor.data.stringify(editor.model.getSelectedContent(editor.model.document.selection));

            let selection = "";
            let range = editor.model.document.selection.getFirstRange();
            for (const value of range.getItems()) {
                selection = selection + value.data;
            }
            let text = selection
                .trim()
                .replace("[", "")
                .replace("]", "");


            let content = text;
            if (text.contains("|")) {
                content = text.split("|");
            }

            let currentDataRows = this.data.elements.length;

            if (!Array.isArray(content)) {
                this.data.elements[0].value = content;
                return;
            }

            content.forEach((word, key) => {
                if (key === currentDataRows) {
                    this.addRow();
                    currentDataRows++;
                }
                this.data.elements[key].value = word.trim();
            });
        },

        addRow(value = "" ) {
            let component = {
                id: this.data.elements.length,
                value: value,
                correct: true
            };
            this.data.elements.push(component);
        },

        trash(event, element) {
            event.stopPropagation();
            this.data.elements = this.data.elements.filter(el => el.id != element.id);
            this.data.elements.forEach((el, key) => el.id = key);
        },

        insertDataInEditor: function() {

            let result = "[" + this.data.elements.map( (item) => item.value).join("|") + "]";

            let lw = livewire.find(document.getElementById("cms").getAttribute("wire:id"));
            lw.set("showSelectionOptionsModal", true);

            window.editor.model.change(writer => {
                window.editor.model.insertContent(
                    writer.createText(result)
                );
            });

            setTimeout(() => {
                this.$wire.setQuestionProperty("question", window.editor.getData());
            }, 300);
        },
        validateInput: function() {
            const emptyFields = this.data.elements.filter(element => element.value === "");

            if (emptyFields.length !== 0 ) {
                this.hasError.empty = emptyFields.map(item => item.id);

                Notify.notify("Niet alle velden zijn (correct) ingevuld", "error");
                return false;
            }

            return true;
        },
        save() {
            if (!this.validateInput()) {
                return;
            }

            this.insertDataInEditor();

            this.closePopup();
        },
        disabled() {
            if (this.data.elements.length >= this.maxOptions) {
                return true;
            }
            return !!this.data.elements.find(element => element.value === "");
        },
        closePopup() {
            this.showPopup = false;
            this.data.elements = [];
            this.init();
        },
        canDelete() {
            return this.data.elements.length <= 1;
        },
        resetHasError() {
            this.hasError.empty = [];
        }
    }));

    Alpine.data("selectionOptions", (entangle) => ({
        showPopup: entangle.value,
        editorId: entangle.editorId,
        hasError: { empty: [], false: [] },
        data: {
            elements: []
        },
        maxOptions: 10,
        minOptions: 2,

        init() {
            for (let i = 0; i < this.minOptions; i++) {
                this.addRow();
            }
        },

        initWithSelection() {
            let editor = window.editor;
            // let selection = editor.data.stringify(editor.model.getSelectedContent(editor.model.document.selection));

            let selection = "";
            let range = editor.model.document.selection.getFirstRange();
            for (const value of range.getItems()) {
                selection = selection + value.data;
            }
            let text = selection
                .trim()
                .replace("[", "")
                .replace("]", "");


            let content = text;
            if (text.contains("|")) {
                content = text.split("|");
            }

            let currentDataRows = this.data.elements.length;
            this.data.elements[0].checked = "true";

            if (!Array.isArray(content)) {
                this.data.elements[0].value = content;
                return;
            }

            content.forEach((word, key) => {
                if (key === currentDataRows) {
                    this.addRow();
                    currentDataRows++;
                }
                this.data.elements[key].value = word.trim();
            });
        },

        addRow(value = "", checked = "false") {
            let component = {
                id: this.data.elements.length,
                checked: checked, value: value
            };
            this.data.elements.push(component);
        },

        trash(event, element) {
            event.stopPropagation();
            this.data.elements = this.data.elements.filter(el => el.id != element.id);
            this.data.elements.forEach((el, key) => el.id = key);
        },

        toggleChecked(event, element) {
            this.$nextTick(() => {
                if (element.checked == "true") {
                    this.data.elements = this.data.elements.map(item => {
                        item.checked = item.id == element.id ? "true" : "false";
                        return item;
                    });
                }
            });
        },

        insertDataInEditor: function() {
            let correct = this.data.elements.find(el => el.value != "" && el.checked == "true");
            let result = this.data.elements.filter(el => el.value != "" && el.checked == "false").map(el => el.value);

            result.unshift(correct.value);
            result = "[" + result.join("|") + "]";
            let lw = livewire.find(document.getElementById("cms").getAttribute("wire:id"));
            lw.set("showSelectionOptionsModal", true);

            window.editor.model.change(writer => {
                window.editor.model.insertContent(
                    writer.createText(result)
                );
            });

            setTimeout(() => {
                this.$wire.setQuestionProperty("question", window.editor.getData());
            }, 300);
        },
        validateInput: function() {
            const emptyFields = this.data.elements.filter(element => element.value === "");
            const falseValues = this.data.elements.filter(element => element.checked === "false");

            if (emptyFields.length !== 0 || this.data.elements.length === falseValues.length) {
                this.hasError.empty = emptyFields.map(item => item.id);

                if (this.data.elements.length === falseValues.length) {
                    this.hasError.false = falseValues.map(item => item.id);
                }

                Notify.notify("Niet alle velden zijn (correct) ingevuld", "error");
                return false;
            }

            return true;
        },
        save() {
            if (!this.validateInput()) {
                return;
            }

            this.insertDataInEditor();

            this.closePopup();
        },
        disabled() {
            if (this.data.elements.length >= this.maxOptions) {
                return true;
            }
            return !!this.data.elements.find(element => element.value === "");
        },
        closePopup() {
            this.showPopup = false;
            this.data.elements = [];
            this.init();
        },
        canDelete() {
            return this.data.elements.length <= 2;
        },
        resetHasError() {
            this.hasError.empty = [];
            this.hasError.false = [];
        }
    }));
    Alpine.data("badge", (videoUrl = null, mode = "edit") => ({
        options: false,
        videoUrl: videoUrl,
        videoTitle: videoUrl,
        resolvingTitle: true,
        index: 1,
        mode: mode,
        attachmentLoading: false,
        async init() {
            this.setIndex();

            this.$watch("options", value => {
                if (value) {
                    let pWidth = this.$refs.optionscontainer.parentElement.offsetWidth;
                    let pPos = this.$refs.optionscontainer.parentElement.getBoundingClientRect().left;
                    if ((pWidth + pPos) < 288) {
                        this.$refs.optionscontainer.classList.remove("right-0");
                    }
                }
            });
            if (videoUrl) {
                const fetchedTitle = await getTitleForVideoUrl(videoUrl);
                this.videoTitle = fetchedTitle || videoUrl;
                this.resolvingTitle = false;
                if (mode === "edit") {
                    this.$wire.setVideoTitle(videoUrl, this.videoTitle);
                }
            }
        },
        setIndex() {
            const parent = this.$root.parentElement;
            if (parent === null) return;
            this.index = Array.prototype.indexOf.call(parent.children, this.$el) + 1;
        },
        dispatchAttachmentLoading() {
            window.dispatchEvent(new CustomEvent('attachment-preview-loading'))
        }
    }));

    Alpine.data("drawingTool", (questionId, entanglements, isTeacher, isPreview = false) => ({
        show: false,
        questionId: questionId,
        answerSvg: entanglements.answerSvg,
        questionSvg: entanglements.questionSvg,
        gridSvg: entanglements.gridSvg,
        grid: entanglements.grid,
        isOldDrawing: entanglements.isOldDrawing,
        showWarning: false,
        clearSlate: false,
        isTeacher: isTeacher,
        toolName: null,
        isPreview: isPreview,
        init() {
            this.toolName = `drawingTool_${questionId}`;
            if (Object.getOwnPropertyNames(window).includes(this.toolName)) {
                delete window[this.toolName];
            }
            const toolName = window[this.toolName] = initDrawingQuestion(this.$root, this.isTeacher, this.isPreview, this.grid, this.isOldDrawing);

            if (this.isTeacher) {
                this.makeGridIfNecessary(toolName);
            }

            this.$watch("show", show => {
                if (show) {
                    toolName.Canvas.data.answer = this.answerSvg;
                    toolName.Canvas.data.question = this.questionSvg;

                    this.handleGrid(toolName);

                    toolName.drawingApp.init();
                } else {
                    const component = getClosestLivewireComponentByAttribute(this.$root, "questionComponent");
                    component.call("render");
                }
            });

            toolName.Canvas.layers.answer.enable();
            if (this.isTeacher) {
                toolName.Canvas.setCurrentLayer("question");
            } else {
                toolName.Canvas.setCurrentLayer("answer");
            }

        },
        handleGrid(toolName) {
            if (this.gridSvg !== "0.00" && this.gridSvg !== "") {
                let parsedGrid = parseFloat(this.gridSvg);
                toolName.UI.gridSize.value = parsedGrid;
                toolName.UI.gridToggle.checked = true;
                toolName.drawingApp.params.gridSize = parsedGrid;
                toolName.Canvas.layers.grid.params.hidden = false;

                if (!this.isTeacher) {
                    // this.$root.querySelector('#grid-background')?.remove();
                }
            }
        },
        makeGridIfNecessary(toolName) {
            let gridSize = false;

            if (this.gridSvg !== "" && this.gridSvg !== "0.00") {
                gridSize = this.gridSvg;

            } else if (this.isOldDrawing == false && (this.grid && this.grid !== "0")) {
                gridSize = 1 / parseInt(this.grid) * 14;    // This calculation is based on try and change to reach the closest formula that makes grid visualization same as old drawing
            }
            if (gridSize) {
                makePreviewGrid(toolName.drawingApp, gridSize);
                setTimeout(() => {
                    makePreviewGrid(toolName.drawingApp, gridSize);
                }, 2000);
            }
        }
    }));
    Alpine.data("questionEditorSidebar", () => ({
        slideWidth: 300,
        drawer: null,
        resizing: false,
        resizeTimout: null,
        slides: ["home", "type", "newquestion", "questionbank"],
        activeSlide: null,
        scrollTimeout: null,
        pollingInterval: 2500, // Milliseconds;
        init() {
            this.slideWidth = this.$root.offsetWidth;
            this.drawer = this.$root.closest(".drawer");
            this.setActiveSlideProperty(this.$root.scrollLeft);
            setTimeout(() => {
                this.handleVerticalScroll(this.$root.firstElementChild);
                this.scrollActiveQuestionIntoView();
            }, 400);
            this.poll(this.pollingInterval);
            this.$watch("$store.cms.handledAllRequests", (value) => {
                if (value) {
                    this.checkActiveSlide();
                }
            });
        },
        checkActiveSlide() {
            if (!["newquestion", "questionbank"].includes(this.activeSlide)) {
                return;
            }
            if (this.$root.children[2].getAttribute("x-ref") === this.activeSlide) {
                return;
            }
            if (this.activeSlide === "newquestion") {
                return this.setNextSlide(this.$refs.newquestion);
            }
            if (this.activeSlide === "newquestion") {
                return this.setNextSlide(this.$refs.questionbank);
            }
            this.prev(this.$root.children[2]);

        },
        next(currentEl) {
            const left = this.$refs.questionEditorSidebar.scrollLeft + this.slideWidth;
            this.scroll(left);
            this.handleVerticalScroll(currentEl.nextElementSibling);
        },
        prev(currentEl) {
            const left = this.$refs.questionEditorSidebar.scrollLeft - this.slideWidth;
            this.scroll(left);
            this.handleVerticalScroll(currentEl.previousElementSibling);
        },
        home(scrollActiveIntoView = true) {
            this.scroll(0, scrollActiveIntoView);
            if (!this.$store.cms.emptyState) this.$dispatch("backdrop");
            this.handleVerticalScroll(this.$refs.home);
            this.$dispatch("closed-with-backdrop", false);
        },
        scroll(position, scrollActiveIntoView = true) {
            this.setActiveSlideProperty(position);
            if (scrollActiveIntoView) this.scrollActiveQuestionIntoView();
            this.$refs.questionEditorSidebar.scrollTo(this.getScrollToProperties(position));
            this.$store.cms.scrollPos = 0;
        },
        handleVerticalScroll(el) {
            if (el.getAttribute("x-ref") !== this.activeSlide) return;

            if (!this.$store.questionBank.active) {
                this.$refs.questionEditorSidebar.style.minHeight = "auto";
                this.$refs.questionEditorSidebar.style.height = "auto";
            }

            if (el.offsetHeight > this.drawer.offsetHeight) {
                this.drawer.classList.add("overflow-auto");
                this.drawer.classList.remove("overflow-hidden");
            } else {
                this.drawer.classList.add("overflow-hidden");
                this.drawer.classList.remove("overflow-auto");
            }
            this.$nextTick(() => {
                this.$refs.questionEditorSidebar.style.minHeight = this.drawer.offsetHeight + "px";
                this.$refs.questionEditorSidebar.style.height = el.offsetHeight + "px";
            });
        },
        setNextSlide(toInsert) {
            this.$root.insertBefore(toInsert, this.$refs.type.nextElementSibling);
        },
        showNewQuestion(container) {
            this.setNextSlide(this.$refs.newquestion);
            this.$nextTick(() => {
                this.next(container);
            });
        },
        showQuestionBank() {
            this.setNextSlide(this.$refs.questionbank);
            this.$nextTick(() => {
                this.drawer.classList.add("fullscreen");
                const boundingRect = this.$refs.questionbank.getBoundingClientRect();
                this.scroll(boundingRect.x + boundingRect.width);
                this.$store.questionBank.active = true;
            });
        },
        hideQuestionBank() {
            this.$root.querySelectorAll(".slide-container").forEach((slide) => {
                slide.classList.add("opacity-0");
            });
            this.$store.questionBank.active = false;

            if (this.$store.questionBank.inGroup) {
                let drawerComponent = getClosestLivewireComponentByAttribute(this.$el, "cms-drawer");
                drawerComponent.set("groupId", null);
                this.$store.questionBank.inGroup = false;
            }
            this.$nextTick(() => {
                this.drawer.classList.remove("fullscreen");
                this.home();
                // this.scroll(container.parentElement.firstElementChild.offsetWidth);
                setTimeout(() => {
                    this.$root.querySelectorAll(".slide-container").forEach((slide) => {
                        slide.classList.remove("opacity-0");
                    });
                    this.$wire.emitTo("drawer.cms", "refreshDrawer");
                    this.$dispatch("resize");
                }, 400);
                this.$wire.emitTo("drawer.cms", "refreshDrawer");
            });
        },
        addQuestionToGroup(uuid) {
            this.$store.questionBank.inGroup = uuid;
            this.showAddQuestionSlide(true, false);
        },
        addGroup(shouldCheckDirty = true) {
            if (this.emitAddToOpenShortIfNecessary(shouldCheckDirty, true, false)) {
                this.$wire.addGroup();
            }
        },
        async showAddQuestionSlide(shouldCheckDirty = true, clearGroupUuid = true) {
            if (this.emitAddToOpenShortIfNecessary(shouldCheckDirty, false, false)) {
                if (clearGroupUuid) {
                    let questionBankLivewireComponent = Livewire.find(this.drawer.querySelector("#question-bank").getAttribute("wire:id"));
                    await questionBankLivewireComponent.clearInGroupProperty();
                    this.$store.questionBank.inGroup = false;
                }
                this.next(this.$refs.home);
                if(!this.$store.cms.emptyState) {
                    this.$dispatch("backdrop");
                }
            }
        },
        addSubQuestionToNewGroup(shouldCheckDirty = true) {
            this.emitAddToOpenShortIfNecessary(shouldCheckDirty, false, true);
        },
        emitAddToOpenShortIfNecessary(shouldCheckDirty = true, group, newSubQuestion) {
            this.$dispatch("store-current-question");
            if (shouldCheckDirty && this.$store.cms.dirty) {
                this.$wire.emitTo("teacher.cms.constructor", "addQuestionFromDirty", {
                    group,
                    newSubQuestion,
                    groupUuid: this.$store.questionBank.inGroup
                });
                return false;
            }
            return true;
        },
        backToQuestionOverview(container) {
            this.home(false);
            this.$store.questionBank.inGroup = false;
        },
        handleResizing() {
            clearTimeout(this.resizeTimout);
            if (this.$store.questionBank.active) {
                if (!this.resizing) this.resizing = true;

                this.resizeTimout = setTimeout(() => {
                    this.$root.scrollLeft = this.$refs.questionbank.offsetLeft;
                    this.resizing = false;
                }, 500);
            }
        },
        scrollActiveQuestionIntoView() {
            if (this.activeSlide !== "home") return;
            clearTimeout(this.scrollTimeout);
            this.scrollTimeout = setTimeout(() => {
                let activeQuestion = this.$refs.home.querySelector(".question-button.question-active");
                activeQuestion ||= this.$refs.home.querySelector(".group-active");
                if (activeQuestion === null) return clearTimeout(this.scrollTimeout);

                const top = activeQuestion.getBoundingClientRect().top;
                const screenWithBottomMargin = (window.screen.height - 200);

                if (top >= screenWithBottomMargin) {
                    this.drawer.scrollTo({ top: (top - screenWithBottomMargin / 2), behavior: "smooth" });
                }

                clearTimeout(this.scrollTimeout);
            }, 750);
        },
        setActiveSlideProperty(position) {
            let index = position / this.slideWidth > 2 ? 3 : Math.round(position / this.slideWidth);
            this.activeSlide = this.slides[index];
        },
        poll(interval) {
            setTimeout(() => {
                if (this.activeSlide !== "questionbank") {
                    let el = this.$root.querySelector(`[x-ref="${this.activeSlide}"]`);
                    if (el !== null) this.handleVerticalScroll(el);
                }
                this.poll(interval);
            }, interval);
        },
        getScrollToProperties(position) {
            let safariAgent = navigator.userAgent.indexOf("Safari") > -1;
            let chromeAgent = navigator.userAgent.indexOf("Chrome") > -1;
            if ((chromeAgent) && (safariAgent)) safariAgent = false;

            let scrollToSettings = {
                left: position >= 0 ? position : 0
            };
            /* RR: Smooth scrolling breaks entirely on Safari 15.4 so I only add it in non-safari browsers just so it doesn't break anything..*/
            if (!safariAgent) {
                scrollToSettings.behavior = "smooth";
            }
            return scrollToSettings;
        }
    }));
    Alpine.data("choices", (wireModel, multiple, options, config, filterContainer) => ({
        multiple: multiple,
        value: wireModel,
        options: options,
        config: config,
        wireModel: wireModel,
        activeFiltersContainer: null,
        choices: null,
        activeGroups: [],
        init() {
            // some new fancy way of setting a value when undefined
            window.registeredEventHandlers ??= [];

            this.activeFiltersContainer = document.getElementById(filterContainer);
            this.multiple = multiple === 1;
            this.$nextTick(() => {
                let choices = new Choices(
                    this.$root.querySelector("select"),
                    this.getChoicesConfig()
                );

                let refreshChoices = () => {
                    let selection = this.multiple ? this.value : [this.value];
                    let options = typeof this.options === "object" ? Object.values(this.options) : this.options;
                    this.setActiveGroupsOnInit();
                    choices.clearStore();
                    this.addPlaceholderValues(choices);

                    options = options.map(({ value, label, customProperties }) => ({
                        value,
                        label,
                        customProperties: customProperties ?? {},
                        selected: selection.includes(value)
                    }));
                    choices.setChoices(options);

                    this.handleActiveFilters(choices.getValue());
                };

                refreshChoices();

                this.$refs.select.addEventListener("choice", (event) => {
                    let eventValue = this.getValidatedEventValue(event);
                    let choice = event.detail.choice;

                    if (!Array.isArray(this.value)) {
                        this.value = eventValue;
                        return;
                    }
                    if (this.isAParentChoice(choice)) {
                        this.handleGroupItemChoice(choice);
                    }

                    if (isUnselectedRegularOrChildChoice.call(this)) {
                        this.removeFilterItem(choices.getValue().find(value => value.value === choice.value));

                        if (this.value.includes(choice.customProperties?.parentId)) {
                            this.removeFilterItemByValue(choice.customProperties.parentId);
                            this.activeGroups = this.activeGroups.filter(groupId => groupId !== choice.customProperties.parentId);
                        }
                    }

                    this.wireModel = this.value;
                    refreshChoices();

                    function isUnselectedRegularOrChildChoice() {
                        return this.value.includes(eventValue) && (this.isAChildChoice(choice) || this.isARegularChoice(choice));
                    }
                });
                this.$refs.select.addEventListener("change", () => {
                    if (!Array.isArray(this.value)) return;
                    this.value = choices.getValue(true);
                });

                let eventName = this.getRemoveEventName();
                if (!window.registeredEventHandlers.includes(eventName)) {
                    window.registeredEventHandlers.push(eventName);
                    window.addEventListener(eventName, (event) => {
                        /* If this yields no result, make sure the remove eventnames are unique on the page :) */
                        let choice = choices.getValue().filter(choice => choice.value === event.detail.value)[0];
                        if (this.isAParentChoice(choice)) {
                            this.handleGroupItemChoice(choice);
                        } else {
                            this.removeFilterItem(choice);
                        }
                        refreshChoices();
                    });
                }

                this.$watch("value", () => refreshChoices());
                this.$watch("options", () => refreshChoices());

                this.$refs.select.addEventListener("showDropdown", () => {
                    if (this.$root.querySelector(".is-active") && this.$root.classList.contains("super")) {
                        this.$refs.chevron.style.left = (this.$root.querySelector(".is-active").offsetWidth - 25) + "px";
                    }
                });
                this.$refs.select.addEventListener("hideDropdown", () => {
                    this.$refs.chevron.style.left = "auto";
                });

            });
        },
        setActiveGroupsOnInit() {
            if (this.activeGroups.length) {
                this.activeGroups.forEach(value => this.clearFilterPill(value));
            }
            this.activeGroups = [];
            this.options.forEach(option => {
                if (option.customProperties?.parent === true) {
                    if (this.value.includes(option.value)) {
                        this.activeGroups.push(option.value);
                    }
                }
            });
            this.activeGroups = this.activeGroups.filter((value, index, self) => self.indexOf(value) === index);
        },
        handleGroupItemChoice: function(choice) {
            let parentId = choice.customProperties.parentId;
            let childValues = this.options.filter(option => {
                return option.customProperties.parent === false && parentId === option.customProperties.parentId;
            }).map(value => value.value);

            if (!this.value.includes(choice.value)) {
                this.value = _.union(this.value, childValues, [choice.value]);
                this.activeGroups.push(choice.value);
                return;
            }

            let valuesToSplice = _.union(childValues, [choice.value]);
            valuesToSplice.forEach(val => {
                if (this.value.includes(val)) {
                    this.removeFilterItemByValue(val);
                }
            });
            this.activeGroups = this.activeGroups.filter(groupId => groupId !== choice.customProperties.parentId);
        },
        isAParentChoice(choice) {
            return choice.customProperties?.parent === true;
        },
        isAChildChoice(choice) {
            return choice.customProperties?.parentId !== undefined && choice.customProperties?.parent === false;
        },
        isARegularChoice(choice) {
            return choice.customProperties.parent === undefined;
        },
        removeFilterItem(item) {
            if (!Array.isArray(this.value)) return;
            this.removeFilterItemByValue(item.value);
        },
        removeFilterItemByValue(value) {
            this.value.splice(this.value.indexOf(value), 1);
            this.clearFilterPill(value);
        },
        getDataSelector(item) {
            return `[data-filter="${this.$root.dataset.modelName}"][data-filter-value="${item}"]`;
        },

        handleActiveFilters(choicesValues) {
            if (!Array.isArray(this.value)) return;

            let valuesToCreatePillsFor = this.value;
            if (this.activeGroups.length) {
                valuesToCreatePillsFor = choicesValues.filter(value => {
                    if (value.customProperties?.parent === true) {
                        return true;
                    }
                    if (!this.activeGroups.includes(value.customProperties?.parentId)) {
                        return true;
                    }
                    if (!this.needsFilterPill(value.value)) {
                        this.clearFilterPill(value.value);
                    }
                    return false;
                }).map(item => item.value);
            }

            valuesToCreatePillsFor.forEach(item => {
                if (this.needsFilterPill(item)) {
                    const cItem = choicesValues.find(value => value.value === item);
                    if (typeof cItem !== "undefined") {
                        this.createFilterPill(cItem);
                    }
                }
            });
        },

        getTextForFilterPill: function(item, element) {
            let innerHtml = item.label;
            if (this.isAChildChoice(item)) {
                innerHtml = `${item.customProperties.parentLabel}: ${item.label}`;
            }
            if (this.isAParentChoice(item)) {
                innerHtml = `${item.label}: ${element.dataset.transAny}`;
            }
            return innerHtml;
        },
        createFilterPill(item) {
            const element = document.getElementById("filter-pill-template").content.firstElementChild.cloneNode(true);

            element.id = `filter-${this.$root.dataset.modelName}-${item.value}`;
            element.classList.add("filter-pill");
            element.dataset.filter = this.$root.dataset.modelName;
            element.dataset.filterValue = item.value;
            element.dataset.removeEventName = this.getRemoveEventName();
            element.firstElementChild.innerHTML = this.getTextForFilterPill(item, element);

            return this.activeFiltersContainer.appendChild(element);
        },

        needsFilterPill(item) {
            return this.activeFiltersContainer.querySelector(this.getDataSelector(item)) === null;
        },

        clearFilterPill(item) {
            return this.activeFiltersContainer.querySelector(this.getDataSelector(item))?.remove();
        },
        getValidatedEventValue: function(event) {
            let eventValue = event.detail.choice.value;
            // UUID values can be parseInt'd but then the value is only the first integers until a letter occurs. So this checks the length of the event value vs the parsed value;
            if (Number.isInteger(parseInt(event.detail.choice.value)) && JSON.stringify(parseInt(event.detail.choice.value)).length === event.detail.choice.value.length) {
                eventValue = parseInt(event.detail.choice.value);
            }
            return eventValue;
        },
        getChoicesConfig: function() {
            return {
                ...this.config,
                callbackOnCreateTemplates: () => {
                    return {
                        choice(classes, attr) {
                            const el = Choices.defaults.templates.choice.call(this, classes, attr, "");
                            if (attr.customProperties?.parent === false) {
                                el.classList.add("child");
                            }
                            return el;
                        }
                    };
                }
            };
        },
        addPlaceholderValues: function(choices) {
            if (!this.config.placeholderValue.length || !this.$root.classList.contains("super")) {
                return;
            }
            let placeholderItem = choices._getTemplate("placeholder", this.config.placeholderValue);
            placeholderItem.classList.add("truncate", "min-w-[1rem]", "placeholder");
            this.$root.querySelector(".choices__placeholder.placeholder")?.remove();
            choices.itemList.append(placeholderItem);
        },
        getRemoveEventName: function() {
            return "removeFrom" + this.$root.getAttribute("wire:key");
        }
    }));

    Alpine.data("analysesSubjectsGraph", (modelId) => ({
            modelId,
            data: [],
            colors: [
                "#30BC51",
                "#5043F6",
                "#ECEE7D",
                "#6820CE",
                "#CB110E",
                "#F79D25",
                "#1B6112",
                "#43ACF5",
                "#E12576",
                "#24D2C5"
            ],
            showEmptyState: false,
            init() {
                this.updateGraph();
            },
            async updateGraph() {
                [this.showEmptyState, this.data] = await this.$wire.call("getDataForGraph");
                this.renderGraph();
            },
            renderGraph() {
                var cssSelector = "#pValueChart>div:not(.empty-state)";
                this.$root.querySelectorAll(cssSelector).forEach(node => node.remove());
                var chart = anychart.column();
                var series = chart.column(this.data);
                var palette = anychart.palettes.distinctColors();
                palette.items(this.colors);

                var yScale = chart.yScale();
                yScale.minimum(0);
                yScale.maximum(1.00);
                yScale.ticks().interval(0.25);
                chart.yAxis(0).labels().format(function() {
                    return this.value == 0 ? "P 0" : "P " + this.value.toFixed(2);
                });

                chart.yGrid().enabled(true);
                chart.xAxis(0).labels()
                    .fontWeight("bold")
                    .fontColor("#041f74")
                    .rotation(-60);

                for (var i = 0; series.getPoint(i).exists(); i++)
                    series.getPoint(i).set("fill", palette.itemAt(i));

                series.selected().fill("#444");
                series.stroke(null);

                this.initTooltips(chart, this.data, series);

                var legend = chart.legend();
                // enable legend
                legend.enabled(true);
                // set source of legend items
                legend.itemsSourceMode("categories");

                legend.itemsFormatter(function(items) {
                    for (var i = 0; i < items.length; i++) {
                        items[i].iconType = "square";
                        items[i].iconFill = palette.itemAt([i]);
                        items[i].iconEnabled = true;
                        items[i].fontWeight = "bold";
                        items[i].fontColor = "#041f74";
                    }
                    return items;
                });

                legend.listen("legendItemMouseOver", function(event) {
                    // get item's index
                    var index = event["itemIndex"];
                    // enable the hover state of the series
                    series.getPoint(index).hovered(true);
                });
                legend.listen("legendItemMouseOut", function(event) {
                    // get item's index
                    var index = event["itemIndex"];
                    // disable the hover state of the series
                    series.getPoint(index).hovered(false);
                });

                legend.listen("legendItemClick", function(event) {
                    // get item's index
                    var index = event["itemIndex"];
                    // disable the hover state of the series
                    series.getPoint(index).selected(!series.getPoint(index).selected());
                    legend.itemsFormatter(function(items) {
                        for (var i = 0; i < items.length; i++) {
                            items[i].iconType = "square";
                            if (series.getPoint(i).selected())
                                items[i].iconFill = "#444";
                            else
                                items[i].iconFill = palette.itemAt([i]);
                            items[i].iconEnabled = true;
                        }
                        return items;
                    });
                });

                chart.listen("pointsSelect", function() {
                    legend.itemsFormatter(function(items) {
                        for (var i = 0; i < items.length; i++) {
                            items[i].iconType = "square";
                            if (series.getPoint(i).selected())
                                items[i].iconFill = "#444";
                            else
                                items[i].iconFill = palette.itemAt([i]);
                            items[i].iconEnabled = true;
                        }
                        return items;
                    });
                });

                chart.listen("pointsSelect", function(e) {
                    if (e.point.get("link")) {
                        window.open(e.point.get("link"), "_self");
                    }
                });

                // // set container id for the chart
                chart.container("pValueChart");
                // initiate chart drawing
                chart.draw();
            },

            initTooltips(chart, data, series) {
                chart.tooltip().useHtml(true);
                chart.tooltip().title(false);
                chart.tooltip().separator(false);
                series.tooltip().enabled(false);
                let contentElement = null;
                let dataRow = null;

                chart.listen("pointMouseOver", (e) => series.tooltip().enabled(false));
                chart.listen("pointMouseOver", function(e) {
                    // get the data for the current point
                    dataRow = data[e.pointIndex];
                    series.tooltip().enabled(true);

                    if (contentElement) {
                        fillTooltipHtml();
                    }
                });

                function fillTooltipHtml() {
                    if (!dataRow) return;

                    while (contentElement.firstChild) {
                        contentElement.firstChild.remove();
                    }
                    const attainmentHeader = document.createElement("h5");
                    attainmentHeader.style.color = "var(--system-base)";
                    attainmentHeader.appendChild(document.createTextNode(dataRow.title));
                    contentElement.appendChild(attainmentHeader);

                    const scoreElement = document.createElement("h2");
                    scoreElement.style.color = "var(--system-base)";
                    scoreElement.appendChild(document.createTextNode(`P ${dataRow.value}`));
                    contentElement.appendChild(scoreElement);

                    const basedOnElement = document.createElement("p");
                    basedOnElement.style.color = "var(--system-base)";
                    basedOnElement.appendChild(document.createTextNode(dataRow.basedOn));
                    contentElement.appendChild(basedOnElement);

                    if (dataRow.link != false) {
                        const detailElement = document.createElement("p");
                        detailElement.style.whiteSpace = "nowrap";
                        detailElement.style.color = "var(--system-base)";
                        detailElement.style.fontWeight = "900";
                        detailElement.appendChild(document.createTextNode("Bekijk analyse"));

                        const iconElement = document.createElement("img");
                        iconElement.src = "/svg/icons/arrow-small.svg";
                        iconElement.style.display = "inline-block";
                        detailElement.appendChild(iconElement);
                        contentElement.appendChild(detailElement);
                    }
                }

                chart.tooltip().onDomReady(function(e) {
                    this.parentElement.style.border = "1px solid var(--blue-grey)";
                    this.parentElement.style.background = "#FFFFFF";
                    this.parentElement.style.opacity = "0.8";
                    contentElement = this.contentElement;

                    fillTooltipHtml();

                });

                /* prevent the content of the contentElement div
                from being overridden by the default formatter */
                chart.tooltip().onBeforeContentChange(function() {
                    return false;
                });
            }
        }
    ));

    Alpine.data("analysesSubjectsTimeSeriesGraph", (modelId) => ({
            modelId,
            data: [],
            colors: [
                "#30BC51",
                "#5043F6",
                "#ECEE7D",
                "#6820CE",
                "#CB110E",
                "#F79D25",
                "#1B6112",
                "#43ACF5",
                "#E12576",
                "#24D2C5"
            ],
            subjects: [],
            showEmptyState: false,
            init() {
                this.updateGraph();
            },
            async updateGraph() {
                [this.showEmptyState, this.data, this.subjects] = await this.$wire.call("getDataForSubjectTimeSeriesGraph");
                this.renderGraph();
            },
            renderGraph() {

                var cssSelector = "#" + this.modelId + ">div:not(.empty-state)";
                this.$root.querySelectorAll(cssSelector).forEach(node => node.remove());
                // set the data
                let table = anychart.data.table();
                table.addData(this.data);

                // chart type
                var chart = anychart.stock();

                var yScale = chart.plot(0).yScale();
                yScale.minimum(0);
                yScale.maximum(1.00);
                yScale.ticks().interval(0.25);

                var line = chart.plot(0).lineMarker();
                line.value(0);
                line.stroke("2 var(--system-base)");

                chart.plot(0).yAxis(0).labels().format(function() {
                    return this.value == 0 ? "P 0" : "P " + this.value.toFixed(2);
                });

                // access labels
                let labels = chart.scroller().xAxis().labels();
                let minorLabels = chart.scroller().xAxis().minorLabels();

// set major labels text format
                labels.format(function() {
                    return "'" + anychart.format.dateTime(this.tickValue, "Y");
                });
// set labels color
                labels.fontColor("var(--system-base)");
                labels.fontWeight("bold");

// set minor labels text format
                minorLabels.format(function() {
                    return anychart.format.dateTime(this.tickValue, "MMM");
                });

// set minor color to selectedColorForScroller;
                minorLabels.fontColor("var(--system-base) 0.5");
//

                chart.scroller().selectedFill("var(--system-base) 0.1");
                chart.scroller().outlineStroke("var(--system-base)", 2);
                chart.scroller().outline;

                chart.interactivity().hoverMode("single");

                this.subjects.forEach((el, index) => {
                    const totalDefinitions = [
                        "Vak totaal",
                        "Subject total",
                        "Attainement total",
                        "Eindterm totaal",
                    ];
                    let strokeWidth = 2;
                    let strokeColor = this.colors[index];
                    let cnt = index + 1;
                    let mapping = table.mapAs();
                    if (totalDefinitions.includes(el)) {
                        strokeWidth = 3;
                        strokeColor = "var(--system-base)";
                    }
                    mapping.addField("value", cnt);

                    let series = chart.plot(0).line(mapping);
                    series.name(el);
                    series.legendItem().useHtml(true);
                    series.legendItem().format("{%seriesName}");

                    let marker = series.normal().markers();
                    marker.enabled(false);

                    let marker1 = series.hovered().markers();
                    marker1.enabled(true);
                    marker1.size(4);
                    marker1.type("circle");

                    series.normal().stroke(strokeColor, strokeWidth);
                    series.connectMissingPoints(true);
                });

                chart.title("");
                chart.plot(0).legend().titleFormat("");

                chart.container(this.modelId);
                chart.draw();
            }
        }
    ));


    Alpine.data("analysesAttainmentsGraph", (modelId) => ({
            modelId,
            data: false,
            colors: [
                "#30BC51",
                "#5043F6",
                "#ECEE7D",
                "#6820CE",
                "#CB110E",
                "#F79D25",
                "#1B6112",
                "#43ACF5",
                "#E12576",
                "#24D2C5"
            ],
            showEmptyState: false,
            init() {
                this.updateGraph();
            },
            async updateGraph() {
                [this.showEmptyState, this.data] = await this.$wire.call("getDataForGraph");
                this.renderGraph();
            },
            renderGraph() {
                var cssSelector = "#pValueChart>div:not(.empty-state)";
                this.$root.querySelectorAll(cssSelector).forEach(node => node.remove());
                var chart = anychart.column();
                var series = chart.column(this.data);
                var palette = anychart.palettes.distinctColors();
                palette.items(this.colors);

                var yScale = chart.yScale();
                yScale.minimum(0);
                yScale.maximum(1.00);
                yScale.ticks().interval(0.25);
                chart.yAxis(0).labels().format(function() {
                    return this.value == 0 ? "P 0" : "P " + this.value.toFixed(2);
                });

                chart.yGrid().enabled(true);
                chart.xAxis(0).labels()
                    .fontWeight("bold")
                    .fontColor("#041f74");


                for (var i = 0; series.getPoint(i).exists(); i++)
                    series.getPoint(i).set("fill", palette.itemAt(i));

                series.selected().fill("#444");
                series.stroke(null);

                this.initTooltips(chart, this.data, series);

                var legend = chart.legend();
                // enable legend
                legend.enabled(true);
                // set source of legend items
                legend.itemsSourceMode("categories");

                var _data = this.data;
                legend.itemsFormatter(function(items) {
                    for (var i = 0; i < items.length; i++) {
                        items[i].iconType = "square";
                        items[i].iconFill = palette.itemAt([i]);
                        items[i].iconEnabled = true;
                        items[i].text = _data[i].title;
                        items[i].fontWeight = "bold";
                        items[i].fontColor = "#041f74";
                    }
                    return items;
                });


                legend.listen("legendItemMouseOver", function(event) {
                    // get item's index
                    var index = event["itemIndex"];
                    // enable the hover state of the series
                    series.getPoint(index).hovered(true);
                });
                legend.listen("legendItemMouseOut", function(event) {
                    // get item's index
                    var index = event["itemIndex"];
                    // disable the hover state of the series
                    series.getPoint(index).hovered(false);
                });

                legend.listen("legendItemClick", function(event) {
                    // get item's index
                    var index = event["itemIndex"];
                    // disable the hover state of the series
                    series.getPoint(index).selected(!series.getPoint(index).selected());
                    legend.itemsFormatter(function(items) {
                        for (var i = 0; i < items.length; i++) {
                            items[i].iconType = "square";
                            if (series.getPoint(i).selected())
                                items[i].iconFill = "#444";
                            else
                                items[i].iconFill = palette.itemAt([i]);
                            items[i].iconEnabled = true;
                        }
                        return items;
                    });
                });

                chart.listen("pointsSelect", function() {
                    legend.itemsFormatter(function(items) {
                        for (var i = 0; i < items.length; i++) {
                            items[i].iconType = "square";
                            if (series.getPoint(i).selected())
                                items[i].iconFill = "#444";
                            else
                                items[i].iconFill = palette.itemAt([i]);
                            items[i].iconEnabled = true;
                        }
                        return items;
                    });
                });

                chart.listen("pointsSelect", function(e) {
                    if (e.point.get("link")) {
                        window.open(e.point.get("link"), "_self");
                    }
                });

                chart.interactivity("by-x");

                // set container id for the chart
                chart.container("pValueChart");
                // initiate chart drawing
                chart.draw();
            },

            initTooltips(chart, data, series) {
                chart.tooltip().useHtml(true);
                chart.tooltip().title(false);
                chart.tooltip().separator(false);
                series.tooltip().enabled(false);

                let contentElement = null;
                let dataRow = null;

                chart.listen("pointMouseOut", (e) => series.tooltip().enabled(false));

                function fillTooltipHtml() {
                    if (!dataRow) return;

                    while (contentElement.firstChild) {
                        contentElement.firstChild.remove();
                    }
                    const attainmentHeader = document.createElement("h5");
                    attainmentHeader.style.color = "var(--system-base)";
                    attainmentHeader.appendChild(document.createTextNode(dataRow.title));
                    contentElement.appendChild(attainmentHeader);

                    const scoreElement = document.createElement("h2");
                    scoreElement.style.color = "var(--system-base)";
                    scoreElement.appendChild(document.createTextNode(`P ${dataRow.value}`));
                    contentElement.appendChild(scoreElement);

                    const basedOnElement = document.createElement("p");
                    basedOnElement.style.color = "var(--system-base)";
                    basedOnElement.appendChild(document.createTextNode(dataRow.basedOn));
                    contentElement.appendChild(basedOnElement);

                    if (dataRow.count !== null) {
                        const detailElement = document.createElement("p");
                        detailElement.style.whiteSpace = "nowrap";
                        detailElement.style.color = "var(--system-base)";
                        detailElement.style.fontWeight = "900";
                        detailElement.appendChild(document.createTextNode("Bekijk analyse "));

                        const iconElement = document.createElement("img");
                        iconElement.src = "/svg/icons/arrow-small.svg";
                        iconElement.style.display = "inline-block";
                        detailElement.appendChild(iconElement);
                        contentElement.appendChild(detailElement);
                    }

                    const AttainmentTexElement = document.createElement("p");
                    AttainmentTexElement.style.color = "var(--system-base)";
                    AttainmentTexElement.appendChild(
                        document.createTextNode(dataRow.text)
                    );
                    contentElement.appendChild(AttainmentTexElement);

                }

                chart.listen("pointMouseOver", function(e) {
                    // get the data for the current point
                    series.tooltip().enabled(true);

                    dataRow = data[e.pointIndex];
                    if (contentElement) {
                        fillTooltipHtml();
                    }
                });


                chart.tooltip().onDomReady(function(e) {
                    this.parentElement.style.border = "1px solid var(--blue-grey)";
                    this.parentElement.style.background = "#FFFFFF";
                    this.parentElement.style.opacity = "0.8";
                    contentElement = this.contentElement;

                    fillTooltipHtml();
                });

                /* prevent the content of the contentElement div
                from being overridden by the default formatter */
                chart.tooltip().onBeforeContentChange(function() {
                    return false;
                });
            }
        }
    ));

    Alpine.data("sliderToggle", (model, sources, initialStatus, disabled, identifier) => ({
        buttonPosition: "0px",
        buttonWidth: "auto",
        value: model,
        sources: sources,
        handle: null,
        disabled,
        identifier,
        init() {
            this.setHandle();
            if (initialStatus !== null) {
                this.value = isString(initialStatus) ? this.sources.indexOf(initialStatus) : +initialStatus;
            }

            this.bootComponent();
        },
        rerender() {
            this.bootComponent();
        },
        bootComponent() {
            this.$root.dataset.hasValue = this.value !== null;
            if (this.value === null) {
                return;
            }
            this.$el.querySelector(".group").firstElementChild.classList.add("text-primary");

            if (this.value !== "" && Object.keys(this.sources).includes(String(this.value))) {
                this.activateButton(this.$el.querySelector("[data-id='" + this.value + "']").parentElement);
            } else {
                this.value = this.$el.querySelector(".group").firstElementChild.dataset.id;
            }
        },
        clickButton(target) {
            this.activateButton(target);
            this.markInputElementsClean();

            const oldValue = this.value;
            this.value = target.firstElementChild.dataset.id;

            this.$root.dataset.hasValue = this.value !== null;
            if (oldValue !== this.value) {
                this.$dispatch("slider-toggle-value-updated", {
                    value: this.$root.dataset.toggleValue,
                    state: parseInt(this.value) === 1 ? "on" : "off",
                    firstTick: oldValue === null,
                    identifier: this.identifier
                });
            }
        },
        hoverButton(target) {
            this.activateButton(target);
        },
        activateButton(target) {
            this.$nextTick(() => {
                this.resetButtons(target);
                this.buttonPosition = target.offsetLeft + "px";
                this.buttonWidth = target.offsetWidth + "px";
                target.dataset.active = true;
                target.firstElementChild.classList.add("text-primary");
                this.handle.classList.remove("hidden");
                this.handle.classList.add("block");
            });
        },
        resetButtons(target) {
            Array.from(target.parentElement.children).forEach(button => {
                button.firstElementChild.classList.remove("text-primary");
            });
        },
        setHandle() {
            this.handle = this.$el.querySelector(".slider-button-handle");

            /* Add transition classes later so it doesn't flicker the initial value setting */
            this.$nextTick(() => {
                setTimeout(() => {
                    this.handle.classList.add("transition-all", "ease-in-out", "duration-150");
                }, 200);
            });
        },
        markInputElementsWithError() {
            const falseOptions = this.$root.querySelectorAll(".slider-option[data-active=\"false\"]");
            if (falseOptions.length === 2) {
                falseOptions.forEach(el => el.classList.add("!border-allred"));
            }
        },
        markInputElementsClean() {
            const falseOptions = this.$root.querySelectorAll(".slider-option[data-active=\"false\"]");
            if (falseOptions.length === 2) {
                falseOptions.forEach(el => el.classList.remove("!border-allred"));
            }
        }
    }));

    Alpine.data("expandableGraphForGeneral", (id, modelId, taxonomy, component) => (
        {
            data: false,
            modelId,
            taxonomy,
            containerId: "chart-" + id + "-" + taxonomy,
            id,
            showEmptyState: false,
            init() {
                if (this.expanded) {
                    this.updateGraph();
                }
            },
            async updateGraph(forceUpdate) {
                if (!this.data || forceUpdate) {
                    var method = "getData";
                    if (component == "expandableGraphForGeneral") {
                        method = "getDataForGeneralGraph";
                    }
                    [this.showEmptyState, this.data] = await this.$wire.call(method, this.modelId, this.taxonomy);
                    this.renderGraph();
                }
            },
            get expanded() {
                return this.active === this.id;
            },
            set expanded(value) {
                if (value) {
                    this.updateGraph();
                }

                this.active = value ? this.id : null;
            },
            renderGraph: function() {
                // create bar chart
                var cssSelector = "#" + this.containerId + ">div:not(.empty-state)";
                //
                this.$root.querySelectorAll(cssSelector).forEach(node => node.remove());
                var chart = anychart.bar();
// //
// //                 var credits = chart.credits();
//                 credits.enabled(false);
                var series = chart.bar(this.data);

                series.stroke(this.getColor()).fill(this.getColor());

                var tooltip = series.tooltip();

                tooltip.title(false)
                    .separator(false)
                    .position("right")
                    .anchor("left-center")
                    .offsetX(5)
                    .offsetY(0)
                    .background("#FFFFFF")
                    .fontColor("#000000")
                    .format("{%tooltip}");

                chart.tooltip().positionMode("point");
                // set scale minimum
                chart.yScale().minimum(0);
                chart.yScale().maximum(1);

                // chart.xScale()//.maximum(100)
                chart.xAxis().stroke("#041F74");
                chart.xAxis().stroke("none");
                // set container id for the chart
                chart.container(this.containerId);
                // initiate chart drawing
                chart.draw();
            },
            getColor: function() {
                if (this.taxonomy == "Bloom") {
                    return "#E2DD10";
                }
                if (this.taxonomy == "Miller") {
                    return "#5043F6";
                }
                return "#2EBC4F";
            }
        }
    ));


    Alpine.data("contextMenuButton", (context, uuid, contextData) => ({
        menuOpen: false,
        uuid,
        contextData,
        context,
        gridCard: null,
        showEvent: context + "-context-menu-show",
        closeEvent: context + "-context-menu-close",
        init() {
            this.gridCard = this.$root.closest(".grid-card");
        },
        handle() {
            this.menuOpen = !this.menuOpen;
            if (this.menuOpen) {
                this.$dispatch(this.showEvent, {
                    uuid: this.uuid,
                    button: this.$root,
                    coords: {
                        gridCardOffsetHeight: this.gridCard.offsetHeight,
                        top: this.gridCard.offsetTop,
                        left: this.gridCard.offsetLeft + this.gridCard.offsetWidth
                    },
                    contextData: this.contextData
                });
            } else {
                this.$dispatch(this.closeEvent);
            }
        },
        closeMenu() {
            this.menuOpen = false;
        }
    }));

    Alpine.data("contextMenuHandler", () => ({
        contextMenuOpen: false,
        uuid: null,
        contextData: null,
        correspondingButton: null,
        menuOffsetMarginTop: 56,
        menuOffsetMarginLeft: 224,
        menuCard: null,
        detailCoordsTop: null,
        detailCoordsLeft: null,
        gridCardOffsetHeight: null,
        bodyPage: null,
        init() {
            this.menuCard = this.$root.closest("#context-menu-base");
            this.bodyPage = this.$root.closest(".divide-secondary");
        },
        preventMenuFallOffScreen() {
            if (this.menuCard.offsetTop + this.menuCard.offsetHeight >= this.bodyPage.offsetHeight + this.bodyPage.offsetTop) {
                this.$root.style.top = (this.detailCoordsTop + this.menuOffsetMarginTop - (this.menuCard.offsetHeight - this.gridCardOffsetHeight) - 25) + "px";
                this.$root.style.left = (this.detailCoordsLeft - this.menuCard.offsetWidth - 50) + "px";
            }
        },
        handleIncomingEvent(detail) {
            if (!this.contextMenuOpen) return this.openMenu(detail);

            this.closeMenu();
            setTimeout(() => {
                this.openMenu(detail);
            }, 150);
        },
        async openMenu(detail) {
            this.uuid = detail.uuid;
            this.correspondingButton = detail.button;
            this.contextData = detail.contextData;
            this.detailCoordsTop = detail.coords.top;
            this.detailCoordsLeft = detail.coords.left;
            this.gridCardOffsetHeight = detail.coords.gridCardOffsetHeight;

            this.$root.style.top = (this.detailCoordsTop + this.menuOffsetMarginTop) + "px";
            this.$root.style.left = (this.detailCoordsLeft - this.menuOffsetMarginLeft) + "px";

            let readyForShow = await this.$wire.setContextValues(this.uuid, this.contextData);
            if (readyForShow) this.contextMenuOpen = true;
            this.contextMenuOpen = true;
        },
        closeMenu() {
            this.correspondingButton.dispatchEvent(new CustomEvent("close-menu"));
            this.contextMenuOpen = false;
        }
    }));

    Alpine.data("accordionBlock", (key, emitWhenSet = false) => ({
        id: null,
        emitWhenSet,
        droppingFile: false,
        init() {
            this.id = this.containerId + "-" + key;
            this.$watch('expanded', (value) => {
                setTimeout(() => {
                    this.$el.querySelector('[block-body]').style.overflow = value ? 'visible' : 'hidden';
                }, 100);
            });
        },
        get expanded() {
            return this.active === this.id;
        },
        set expanded(value) {
            this.active = value ? this.id : null;
            if (value) {
                this.$dispatch('block-expanded', {id: this.id});
                this.$root.querySelectorAll(".slider-button-container").forEach(toggle => toggle.dispatchEvent(new CustomEvent("slider-toggle-rerender")));
                // this.$el.classList.remove("hover:shadow-hover");
            }
            if (this.emitWhenSet) {
                Livewire.emit("accordion-update", { key, value });
            }
        }
    }));
    Alpine.data("fileUpload", (uploadModel, rules) => ({
        isDropping: false,
        isUploading: false,
        progress: {},
        dragCounter: 0,
        uploadModel,
        rules,
        handleFileSelect(event) {
            if (event.target.files.length) {
                this.uploadFiles(event.target.files);
            }
        },
        handleFileDrop(event) {
            if (event.dataTransfer.files.length > 0) {
                this.uploadFiles(event.dataTransfer.files);
            }
        },
        uploadFiles(files) {
            const $this = this;
            this.isUploading = true;
            let dummyContainer = this.$root.querySelector("#upload-dummies");
            Array.from(files).forEach((file, key) => {
                if (!this.fileHasAllowedExtension(file)) {
                    this.handleIncorrectFileUpload(file);
                    return;
                }

                if (this.fileTooLarge(file)) {
                    this.handleTooLargeOfAfile(file);
                    return;
                }

                let badgeId = `upload-badge-${key}`;
                let loadingBadge = $this.createLoadingBadge(file, badgeId);

                dummyContainer.append(loadingBadge);
                $this.progress[badgeId] = 0;

                $this.$wire.upload(
                    this.uploadModel,
                    file,
                    success => {
                        $this.progress[badgeId] = 0;
                        dummyContainer.querySelector(`#${badgeId}`).remove();
                    },
                    error => {
                        Notify.notify(`Er is iets misgegaan met het verwerken van '${file.name}'.`, "error");
                        dummyContainer.querySelector(`#${badgeId}`).remove();
                    },
                    progress => {
                        $this.progress[badgeId] = event.detail.progress;
                    });
            });

        },
        removeUpload(filename) {
            this.$wire.removeUpload(this.uploadModel, filename);
        },
        handleDragEnter() {
            this.dragCounter++;
            this.droppingFile = true;
        },
        handleDragLeave() {
            this.dragCounter--;
            if (this.dragCounter === 0) {
                this.droppingFile = false;
            }
        },
        handleDrop() {
            this.droppingFile = false;
            this.dragCounter = 0;
        },
        createLoadingBadge(file, badgeId) {
            let template = this.$root.querySelector("template#upload-badge").content.cloneNode(true);
            template.firstElementChild.id = badgeId;
            template.querySelector(".badge-name").innerText = file.name;

            return template;
        },
        getFileExtension: function(file) {
            let filename = file.name;
            return filename.substring(filename.lastIndexOf(".") + 1, filename.length) || filename;
        },
        fileHasAllowedExtension(file) {
            return this.rules.extensions.data.includes(this.getFileExtension(file));
        },
        handleIncorrectFileUpload(file) {
            let message = this.rules.extensions.message.replace("%s", this.getFileExtension(file));
            Notify.notify(message, "error");
        },
        fileTooLarge(file) {
            return file.size > this.rules.size.data;
        },
        handleTooLargeOfAfile(file) {
            let message = this.rules.size.message.replace("%s", file.name);
            Notify.notify(message, "error");
        }
    }));
    Alpine.data("loginScreen", (openTab, activeOverlay,device, hasErrors) => ({
        openTab,
        showPassword: false,
        hoverPassword: false,
        initialPreviewIconState: true,
        showEntreePassword: false,
        activeOverlay,
        device,
        hasErrors,
        init() {
            setTimeout(() => {
                this.$wire.checkLoginFieldsForInput();
            }, 250);
            this.setCurrentFocusInput();

            this.$watch('hasErrors', value => {
                this.setCurrentFocusInput();
            });
            this.$watch("activeOverlay", value => {
                this.setCurrentFocusInput();
            });
            this.$watch("openTab", value => {
                this.setCurrentFocusInput();
            });
        },
        setCurrentFocusInput (){
            let name = ('' != this.activeOverlay) ? this.activeOverlay : this.openTab;
            var finder = ('' != hasErrors) ? `[data-focus-tab-error = '${name}-${hasErrors[0]}']` :`[data-focus-tab = '${name}']`
            setTimeout(() => this.$root.querySelector(finder)?.focus(), 250);
        },
        changeActiveOverlay(activeOverlay = "") {
            this.activeOverlay = activeOverlay;
        }
    }));
    Alpine.data("assessment", (array) => ({
        score: array.initialScore,
        shadowScore: array.initialScore,
        maxScore: array.maxScore,
        halfPoints: array.halfPoints,
        drawerScoringDisabled: array.drawerScoringDisabled,
        pageUpdated: array.pageUpdated,
        isCoLearningScore: array.isCoLearningScore,
        init() {
            if (this.pageUpdated) {
                this.resetStoredData();
            }
            if (isString(this.shadowScore)) {
                this.shadowScore = isFloat(initialScore) ? parseFloat(initialScore) : parseInt(initialScore);
            }
            this.$nextTick(() => this.$dispatch("slider-score-updated", { score: this.score }));
        },
        toggleCount() {
            return document.querySelectorAll(".student-answer .slider-button-container:not(.disabled)").length;
        },
        dispatchUpdateToNavigator(navigator, updates) {
            this.resetStoredData();
            let navigatorElement = document.querySelector(`#${navigator}-navigator`);
            if (navigatorElement) {
                return navigatorElement.dispatchEvent(new CustomEvent("update-navigator", { detail: { ...updates } }));
            }
            console.warn("No navigation component found for the specified name.");
        },
        toggleTicked(event) {
            const parsedValue = isFloat(event.value) ? parseFloat(event.value) : parseInt(event.value);
            this.setNewScore(parsedValue, event.state, event.firstTick);

            this.updateAssessmentStore();

            this.dispatchNewScoreToSlider();

            this.updateLivewireComponent(event);
        },
        getCurrentScore() {
            return this.halfPoints
                ? Math.round(this.shadowScore * 2) / 2
                : Math.round(this.shadowScore);
        },
        setNewScore(newScore, state, firstTick) {
            if (firstTick && this.isCoLearningScore) {
                this.isCoLearningScore = false
                this.shadowScore = 0;
            }
            if (firstTick && state === "off") {
                this.shadowScore ??= 0;
            } else {
                this.shadowScore = state === "on"
                    ? this.shadowScore + newScore
                    : this.shadowScore - newScore;
            }

            if (this.shadowScore < 0) this.shadowScore = 0;
            if (this.shadowScore > this.maxScore) this.shadowScore = this.maxScore;
            this.score = this.getCurrentScore();
        },
        updateAssessmentStore() {
            this.$store.assessment.currentScore = this.score;
        },
        dispatchNewScoreToSlider() {
            document.querySelector(".score-slider-container")
                .dispatchEvent(new CustomEvent(
                    "new-score",
                    { detail: { score: this.score } }
                ));
        },
        updateLivewireComponent(event) {
            if (this.drawerScoringDisabled) {
                this.$wire.set("score", this.score);
            }
            if (event.hasOwnProperty("identifier")) {
                this.$wire.toggleValueUpdated(event.identifier, event.state);
            }
        },
        resetStoredData() {
            this.$store.assessment.resetData(this.score, this.toggleCount());
            this.$nextTick(() => {
                this.$store.assessment.toggleCount = this.toggleCount();
            });
        },
        updateScoringData(data) {
            Object.assign(this, data);
            this.score = this.shadowScore = data.initialScore
            this.$nextTick(() => this.$dispatch("slider-score-updated", { score: this.score }));
        }
    }));
    Alpine.data("assessmentNavigator", (current, total, methodCall, lastValue, firstValue) => ({
        current,
        total,
        methodCall,
        lastValue,
        firstValue,
        skipWatch: false,
        async first() {
            await this.updateCurrent(this.firstValue, "first");
        },
        async last() {
            await this.updateCurrent(this.lastValue, "last");
        },
        async next() {
            if (this.current >= this.lastValue) return;
            await this.updateCurrent(this.current + 1, "incr");
        },
        async previous() {
            if (this.current <= this.firstValue) return;
            await this.updateCurrent(this.current - 1, "decr");
        },
        async updateCurrent(value, action) {
            this.$dispatch("assessment-drawer-tab-update", { tab: 1 });
            let response = await this.$wire[this.methodCall](value, action);
            if (response) {
                this.updateProperties(response);
            }
        },
        updateProperties(updates) {
            this.current = parseInt(updates.index);
            this.lastValue = parseInt(updates.last);
            this.firstValue = parseInt(updates.first);
        }
    }));
    Alpine.data("multipleChoiceAllOrNothingLines", (activeItems, withToggle) => ({
        activeItems,
        withToggle,
        fixLineHeightCount: 0,
        fixInterval: null,
        init() {
            this.placeAllOrNothingLines();
            this.fixLineHeight();
            this.$watch("expanded", (value) => this.placeAllOrNothingLines());
        },
        fixLineHeight() {
            this.fixInterval = setInterval(() => {
                this.placeAllOrNothingLines();
                this.fixLineHeightCount++;
                if (this.fixLineHeightCount >= 5) {
                    clearInterval(this.fixInterval);
                }
            }, 200);
        },
        placeAllOrNothingLines() {
            this.$nextTick(() => {
                const parent = this.$root.parentElement;
                this.activeItems.map(item => {
                    const el = parent.querySelector(`[data-active-item='${item}']`);
                    let height = (el.offsetTop + (el.offsetHeight / 2) - this.$root.offsetHeight / 2);
                    if (this.$root !== parent.firstElementChild) {
                        height -= this.$root.offsetTop;
                    }
                    this.$root.querySelector(`[data-line='${item}']`).style.height = height + "px";
                });

                if (this.withToggle) {
                    const toggleEl = parent.parentElement.querySelector(".all-or-nothing-toggle");
                    const firstEl = this.$root;
                    const lastEl = parent.querySelector(`[data-active-item="${this.activeItems.slice(-1)}"]`);
                    let middle = this.middleOfElement(firstEl);
                    if (lastEl) {
                        middle = (this.middleOfElement(firstEl) + this.middleOfElement(lastEl)) / 2;
                    }
                    toggleEl.style.top = middle + "px";
                }
            });
        },
        middleOfElement(element) {
            return element.offsetTop + (element.offsetHeight / 2);
        }
    }));
    Alpine.data("assessmentDrawer", (inReview = false) => ({
        activeTab: 1,
        tabs: [1, 2, 3],
        collapse: false,
        container: null,
        clickedNext: false,
        tooltipTimeout: null,
        inReview,
        init() {
            this.container = this.$root.querySelector("#slide-container");
            this.tab(1);
            this.$watch("collapse", (value) => {
                document.documentElement.style.setProperty("--active-sidebar-width", value ? "var(--collapsed-sidebar-width)" : "var(--sidebar-width)");
            });
        },
        tab(index) {
            if (!this.tabs.includes(index)) return;
            this.activeTab = index;
            this.closeTooltips();
            const slide = this.$root.querySelector(".slide-" + index);
            this.handleSlideHeight(slide);
            this.$nextTick(() => {
                this.container.scroll({ top: 0, left: slide.offsetLeft, behavior: "smooth" });
                setTimeout(() => {
                    const position = (this.container.scrollLeft / 300) + 1;
                    if (!this.tabs.includes(position)) {
                        this.container.scroll({ left: slide.offsetLeft });
                    }
                }, 500);
            });
        },
        async next() {
            if (this.needsToPerformActionsStill()) {
                this.$dispatch("scoring-elements-error");
                this.clickedNext = true;
                return;
            }

            this.tab(1);
            await this.$nextTick(async () => {
                this.$store.assessment.resetData();
                await this.$wire.next();
                this.clickedNext = false;
            });
        },
        async previous() {
            this.tab(1);
            await this.$nextTick(async () => {
                this.$store.assessment.resetData();
                await this.$wire.previous();
                this.clickedNext = false;
            });
        },
        handleSlideHeight(slide) {
            if (slide.offsetHeight > this.container.offsetHeight) {
                this.container.classList.add("overflow-y-auto");
                this.container.classList.remove("overflow-y-hidden");
            } else {
                this.container.classList.remove("overflow-y-auto");
                this.container.classList.add("overflow-y-hidden");
            }
        },
        handleResize() {
            const slide = this.$root.querySelector(".slide-" + this.activeTab);
            this.handleSlideHeight(slide);
        },
        closeTooltips() {
            const previousDate = new Date(this.tooltipTimeout);
            previousDate.setMilliseconds(previousDate.getMilliseconds() + 1000);
            if (Date.parse(previousDate) > Date.now()) {
                return;
            }
            this.tooltipTimeout = Date.now();
            this.$root.querySelectorAll(".tooltip-container").forEach((el) => {
                el.dispatchEvent(new CustomEvent("close"));
            });
        },
        needsToPerformActionsStill() {
            return !this.inReview && !this.$store.assessment.clearToProceed() && !this.clickedNext;
        },
        openFeedbackTab() {
            this.tab(2);
            this.$nextTick(() => {
                let editorDiv = this.$root.querySelector(".feedback textarea");
                if (editorDiv) {
                    let editor = ClassicEditors[editorDiv.getAttribute("name")];
                    if (editor) {
                        setTimeout(() => editor.focus(), 320); // Await slide animation, otherwise it breaks;
                    }
                }
            });
        }
    }));
    Alpine.data("scoreSlider", (score, model, maxScore, halfPoints, disabled, coLearning, focusInput, continuousSlider) => ({
        score,
        model,
        maxScore,
        timeOut: null,
        halfPoints,
        disabled,
        skipSync: false,
        persistantScore: null,
        inputBox: null,
        focusInput,
        continuousSlider,
        bars: [],
        halfTotal: false,
        getSliderBackgroundSize(el) {
            if (this.score === null) return 0;

            const min = el.min || 0;
            const max = el.max || 100;
            const value = el.value;
            return (value - min) / (max - min) * 100;
        },
        setThumbOffset() {
            if(continuousSlider) {
                return;
            }
            if(this.score > this.maxScore) {
                this.score = this.maxScore;
            }
            if (this.score < 0) {
                this.score = 0;
            }


            let el = document.querySelector('.score-slider-input');

            var offsetFromCenter = -40;
            offsetFromCenter += (this.score/this.maxScore) * 80;

            el.style.setProperty("--slider-thumb-offset", `calc(${offsetFromCenter}% + 1px)`);
        },
        setSliderBackgroundSize(el) {
            this.$nextTick(() => {
                el.style.setProperty("--slider-thumb-offset", `${25 / 100 * this.getSliderBackgroundSize(el) - 12.5}px`);
                el.style.setProperty("--slider-background-size", `${this.getSliderBackgroundSize(el)}%`);
            })
        },
        syncInput() {
            // Don't update if the value is the same;
            if (this.$wire[this.model] === this.score) return;
            this.$wire.sync(this.model, this.score);
            this.$store.assessment.currentScore = this.score;
            this.$dispatch("slider-score-updated", { score: this.score });
        },
        noChangeEventFallback() {
            if (this.score === null) {
                this.score = this.halfPoints ? this.maxScore / 2 : Math.round(this.maxScore / 2);
                this.syncInput();
            }
        },
        init() {
            if (coLearning) {
                Livewire.hook("message.received", (message, component) => {
                    if (component.name === "student.co-learning" && message.updateQueue[0]?.method === "updateHeartbeat") {
                        let scoreInputElement = this.$root.querySelector("[x-ref='scoreInput']");
                        this.persistentScore = (scoreInputElement !== null && scoreInputElement.value !== "") ? scoreInputElement.value : null;
                    }
                });
                Livewire.hook("message.processed", (message, component) => {
                    if (component.name === "student.co-learning" && message.updateQueue[0]?.method === "updateHeartbeat") {
                        this.skipSync = true;
                        this.score = this.persistentScore;
                    }
                });
            }

            this.inputBox = this.$root.querySelector("[x-ref='scoreInput']");
            this.$watch("score", (value, oldValue) => {
                this.markInputElementsClean();
                if (this.disabled || value === oldValue || this.skipSync) {
                    this.skipSync = false;
                    return;
                }

                if (value >= this.maxScore) {
                    this.score = value = this.maxScore;
                }
                if (value <= 0) {
                    this.score = value = 0;
                }

                this.score = value = this.halfPoints ? Math.round(value * 2) / 2 : Math.round(value);

                this.updateContinuousSlider();
            });
            if (focusInput) {
                this.$nextTick(() => {
                    this.inputBox.focus();
                });
            }

            this.bars = this.maxScore;
            if (this.halfPoints) {
                this.halfTotal = this.hasMaxDecimalScoreWithHalfPoint();
                this.bars = this.maxScore / 0.5;
            }
        },
        markInputElementsWithError() {
            if (this.disabled) return;
            this.inputBox.style.border = "1px solid var(--all-red)";
        },
        markInputElementsClean() {
            if (this.disabled) return;
            this.inputBox.style.border = null;
        },
        getContinuousInput(){
            return this.$root.querySelector("[x-ref='score_slider_continuous_input']");
        },
        updateContinuousSlider() {
            const numberInput = this.getContinuousInput();
            if (numberInput !== null) {
                this.setSliderBackgroundSize(numberInput);
            }
        },
        sliderPillClasses(value) {
            const score = this.halfTotal || this.halfPoints ? this.score * 2 : this.score;
            const first = ((value/2) + "").split(".")[1] === '5';
            return value <= score
                ? `bg-primary border-primary highlight ${first ? 'first' : 'second'}`
                : `border-bluegrey opacity-100 ${first ? 'first' : 'second'}`;
        },
        hasMaxDecimalScoreWithHalfPoint() {
            return isFloat(this.maxScore);
        }
    }));

    Alpine.data("completionQuestion", () => ({
        minWidth: 120,
        maxWidth: 1000,
        setInputWidth(input, init = false, preview = false) {

            if (!init || preview) {
                this.calculateInputWidth(input);
                return;
            }

            this.$watch("showMe", (value) => {
                if (!value) {
                    return;
                }
                this.$nextTick(() => {
                    this.calculateInputWidth(input);
                });
            });
        },
        calculateInputWidth(input) {
            this.minWidth = 120;
            this.maxWidth = input.closest("div.input-group").parentElement.offsetWidth;

            this.span = input.parentElement.querySelector(".absolute");

            this.span.innerText = input.value;
            this.newWidth = this.span.offsetWidth + 27;

            if (this.newWidth < this.minWidth) {
                this.newWidth = this.minWidth;
            }
            if (this.newWidth > this.maxWidth) {
                this.newWidth = this.maxWidth;
            }

            input.style.width = this.newWidth + "px";
        }
    }));
    Alpine.data("fastScoring", (scoreOptions, currentScore, disabled) => ({
        fastOption: null,
        scoreOptions,
        disabled,
        setOption(key) {
            this.fastOption = key;
            this.$dispatch("updated-score", { score: scoreOptions[key] });
            this.$store.assessment.currentScore = scoreOptions[key];
        },
        updatedScore(score) {
            this.fastOption = this.scoreOptions.indexOf(score);
        },
        init() {
            if (currentScore === null) {
                return;
            }
            if (currentScore.toString().indexOf(".0") !== -1) {
                const parsedScore = parseInt(currentScore);
                this.fastOption = this.scoreOptions.indexOf(parsedScore);
            }
        }
    }));
    Alpine.data("tooltip", (alwaysLeft) => ({
        alwaysLeft,
        tooltip: false,
        maxToolTipWidth: 384,
        height: 0,
        inModal: false,
        show: false,
        init() {
            this.setHeightProperty();
            this.inModal = this.$root.closest("#modal-container") !== null;
            this.$watch("tooltip", value => {
                if (value) {
                    let ignoreLeft = false;
                    if (alwaysLeft || this.tooltipTooWideForPosition()) {
                        this.$refs.tooltipdiv.classList.remove("left-1/2", "-translate-x-1/2");
                        this.$refs.tooltipdiv.classList.add("right-0");
                        ignoreLeft = true;
                    }
                    this.$refs.tooltipdiv.style.top = this.getTop();
                    this.$refs.tooltipdiv.style.left = this.getLeft(ignoreLeft);
                }
            });
            this.$nextTick(() => this.show = true);
        },
        getTop() {
            let top = ((this.$root.getBoundingClientRect().y + this.$root.offsetHeight + 8));

            if (this.inModal) {
                top -= this.getModalDimensions().top;
            }

            const bottom = top + this.height;
            if (bottom > window.innerHeight) {
                top = top - (bottom - window.innerHeight);
            }
            return top + "px";
        },
        getLeft(ignoreLeft = false) {
            if (ignoreLeft) return "auto";
            let left = this.$root.getBoundingClientRect().x + (this.$root.offsetWidth / 2);
            if (this.inModal) {
                left -= this.getModalDimensions().left;
            }
            return left + "px";
        },
        handleScroll() {
            this.$refs.tooltipdiv.style.top = this.getTop();
        },
        handleResize() {
            this.$refs.tooltipdiv.style.top = this.getTop();
            this.$refs.tooltipdiv.style.left = this.getLeft();
        },
        setHeightProperty() {
            this.tooltip = true;
            this.$nextTick(() => {
                this.height = this.$refs.tooltipdiv.offsetHeight;
                this.tooltip = false;
                this.$refs.tooltipdiv.classList.remove("invisible");
            });
        },
        tooltipTooWideForPosition() {
            return ((this.$el.getBoundingClientRect().left + (this.maxToolTipWidth / 2)) > window.innerWidth);
        },
        getModalDimensions() {
            const modal = document.querySelector("#modal-container");
            return modal.getBoundingClientRect();
        }
    }));
    Alpine.data("reviewNavigation", (current) => ({
        showSlider: true,
        scrollStep: 100,
        totalScrollWidth: 0,
        activeQuestion: current,
        intersectionCountdown: null,
        navScrollBar: null,
        initialized: false,
        init() {
            this.navScrollBar = this.$root.querySelector('#navscrollbar');
            this.$nextTick(() => {
                this.$root.querySelector(".active").scrollIntoView({ behavior: "smooth" });
                this.totalScrollWidth = this.$root.offsetWidth;
                this.resize();
                this.initialized = true;
                this.slideToActiveQuestionBubble();
            });
        },
        resize() {
            this.scrollStep = window.innerWidth / 10;
            const sliderButtons = this.$root.querySelector(".slider-buttons").offsetWidth * 2;
            this.showSlider = (this.$root.querySelector(".question-indicator").offsetWidth + sliderButtons) >= (this.$root.offsetWidth - 120);
            if (this.showSlider) {
                this.slideToActiveQuestionBubble();
            }
        },
        scroll(position) {
            this.navScrollBar.scrollTo({ left: position, behavior: "smooth" });
            this.startIntersectionCountdown();
        },
        start() {
            this.scroll(0);
        },
        end() {
            this.scroll(this.totalScrollWidth);
        },
        left() {
            this.scroll(this.navScrollBar.scrollLeft - this.scrollStep);
        },
        right() {
            this.scroll(this.navScrollBar.scrollLeft + this.scrollStep);
        },
        slideToActiveQuestionBubble() {
            let left = this.$root.querySelector(".active").offsetLeft;
            this.navScrollBar.scrollTo({
                left: left - (this.$root.getBoundingClientRect().left + 16),
                behavior: "smooth"
            });
        },
        startIntersectionCountdown() {
            clearTimeout(this.intersectionCountdown);
            this.intersectionCountdown = setTimeout(() => {
                clearTimeout(this.intersectionCountdown);
                this.slideToActiveQuestionBubble();
            }, 5000);
        },
        async loadQuestion(number) {
            this.$dispatch('assessment-drawer-tab-update', {tab: 1})
            await this.$wire.loadQuestionFromNav(number);
        }
    }));
    Alpine.data("accountSettings", (language) => ({
        openTab: 'account',
        changing: false,
        language,
        async startLanguageChange(event, wireModelName) {
            this.$dispatch('language-loading-start');
            this.changing = true;
            await this.$wire.set(wireModelName, this.language)
            this.$nextTick(() => {
                setTimeout(() => {
                    this.changing = false;
                    this.$dispatch('language-loading-end');
                }, 1500)
            })

        }
    }));

    Alpine.data("drawingQuestionImagePreview", () => ({
        maxTries: 10,
        currentTry: 0,
        init() {
            this.setHeightToAspectRatio(this.$el);
        },
        setHeightToAspectRatio(element) {
            const aspectRatioWidth = 940;
            const aspectRatioHeight = 500;
            const aspectRatio = (aspectRatioHeight / aspectRatioWidth);
            const container = element.closest("#accordion-block, #answer-container");
            if (!container) {
                console.error('Trying to set drawing image preview aspect ratio on without valid container.');
                return;
            }

            const newHeight = (container.clientWidth-82) * aspectRatio;

            if (newHeight <= 0) {
                if (this.currentTry <= this.maxTries) {
                    setTimeout(() => this.setHeightToAspectRatio(element), 50);
                    this.currentTry++;
                }
                return;
            }

            element.style.height = newHeight + "px";
        }
    }))
    Alpine.data("CompletionInput", () => ({
        previousValue: "",
        minWidth: 120,
        getInputWidth(el) {
            let maxWidth = el.parentNode.closest("div").offsetWidth;
            maxWidth = maxWidth > 1000 ? 1000 : maxWidth;

            if (el.scrollWidth > maxWidth) return maxWidth + "px";
            if (el.value.length === 0 || el.value.length <= 10) return this.minWidth + "px";

            const safari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
            let newWidth = (el.value.length >= this.previousValue.length)
                ? el.scrollWidth + (safari ? 25 : 2)
                : el.scrollWidth - 5;

            this.previousValue = el.value;
            return (newWidth < this.minWidth ? this.minWidth : newWidth ) + 'px';
        }
    }))

    Alpine.data("writeDownCms", (editorId, restrict_word_amount, maxWords) => ({
        editor: null,
        wordCounter: restrict_word_amount,
        maxWords: maxWords,
        wordContainer: null,
        init() {
            this.$nextTick(() => {
                this.editor = ClassicEditors[editorId];
                this.wordContainer = this.$root.querySelector(".ck-word-count__words");
                this.wordContainer.style.display = "flex";
                this.wordContainer.parentElement.style.display = "flex";

                this.addMaxWordsToWordCounter(this.maxWords);
            });

            this.$watch("maxWords", (value) => {
                this.addMaxWordsToWordCounter(value);
            });
        },
        addMaxWordsToWordCounter(value) {
            const spanId = 'max-word-span';
            this.$root.querySelector(`#${spanId}`)?.remove();

            let element = document.createElement("span");
            element.id = spanId;
            element.innerHTML = `/${value ?? 0}`;

            this.wordContainer.parentNode.append(element);

            this.editor.maxWords = value;
        }
    }));
    Alpine.data("openQuestionStudentPlayer", (editorId) => ({
        editorId,
        init() {
            this.$watch("showMe", value => {
                if (!value) return;

                this.$nextTick(() => {
                    var editor = ClassicEditors[editorId];
                    if (!editor) {
                        return;
                    }
                    this.setFocus(editor);
                    if (!editor.ui.focusTracker.isFocused) {
                        setTimeout(() => this.setFocus(editor), 100);
                    }
                });
            });
        },
        setFocus(editor) {
            editor.focus();
            editor.model.change(writer => {
                writer.setSelection(editor.model.document.getRoot(), 'end');
            });
        }
    }));

    Alpine.directive("global", function(el, { expression }) {
        let f = new Function("_", "$data", "_." + expression + " = $data;return;");
        f(window, el._x_dataStack[0]);
    });

    Alpine.store("cms", {
        loading: false,
        processing: false,
        dirty: false,
        scrollPos: 0,
        reinitOnClose: false,
        emptyState: false,
        pendingRequestTimeout: null,
        pendingRequestTally: 0,
        handledAllRequests: true
    });
    Alpine.store("questionBank", {
        active: false,
        inGroup: false
    });
    Alpine.store("assessment", {
        currentScore: null,
        toggleCount: 0,
        clearToProceed() {
            const valuedToggles = document.querySelectorAll('.student-answer .slider-button-container:not(disabled)[data-has-value="true"]').length;
            return this.currentScore !== null && valuedToggles >= this.toggleCount;
        },
        resetData(score = null, toggleCount = 0) {
            this.currentScore = score;
            this.toggleCount = toggleCount;
        }
    });
    Alpine.store("editorMaxWords", {});
});

function getTitleForVideoUrl(videoUrl) {
    return fetch("https://noembed.com/embed?url=" + videoUrl)
        .then((response) => response.json())
        .then((data) => {
            if (!data.error) {
                return data.title;
            }
            return null;
        });
}
