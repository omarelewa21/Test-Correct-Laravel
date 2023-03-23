import Alpine from "alpinejs";
import Choices from "choices.js";
import Intersect from "@alpinejs/intersect";
import Clipboard from "@ryangjchandler/alpine-clipboard";
import collapse from "@alpinejs/collapse";
import { isString } from "lodash";

window.Alpine = Alpine;
Alpine.plugin(Clipboard);
Alpine.plugin(Intersect);
Alpine.plugin(collapse);

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
            let text = window.editor.getSelectedHtml().$.textContent
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

            window.editor.insertText(result);

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
                this.$dispatch("backdrop");
            }
        },
        addSubQuestionToNewGroup(shouldCheckDirty = true) {
            this.emitAddToOpenShortIfNecessary(shouldCheckDirty, false, true);
        },
        emitAddToOpenShortIfNecessary(shouldCheckDirty = true, group, newSubQuestion) {
            this.$dispatch("store-current-question");
            if (shouldCheckDirty && this.$store.cms.dirty) {
                this.$wire.emitTo("teacher.questions.open-short", "addQuestionFromDirty", {
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
                    let cnt = index + 1;
                    let mapping = table.mapAs();
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

                    series.normal().stroke(this.colors[index], 2);
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

    Alpine.data("sliderToggle", (model, sources, initialStatus) => ({
        buttonPosition: "0px",
        buttonWidth: "auto",
        value: model,
        sources: sources,
        handle: null,
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
            const oldValue = this.value;
            this.value = target.firstElementChild.dataset.id;

            if (oldValue !== this.value) {
                this.$dispatch("slider-toggle-value-updated", {
                    value: this.$root.dataset.toggleValue,
                    state: parseInt(this.value) === 1 ? "on" : "off"
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
                target.firstElementChild.classList.add("text-primary");
                this.handle.classList.remove("hidden");
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
            this.$root.style.top = (detail.coords.top + this.menuOffsetMarginTop) + "px";
            this.$root.style.left = (detail.coords.left - this.menuOffsetMarginLeft) + "px";

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
        },
        get expanded() {
            return this.active === this.id;
        },
        set expanded(value) {
            this.active = value ? this.id : null;
            if (value) {
                this.$root.querySelectorAll(".slider-button-container").forEach(toggle => toggle.dispatchEvent(new CustomEvent("slider-toggle-rerender")));
                this.$el.classList.remove("hover:shadow-hover");
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
    Alpine.data("assessment", (score, maxScore, halfPoints) => ({
        score,
        shadowScore: score,
        maxScore,
        halfPoints,
        toggleCount() {
            return this.$root.querySelectorAll(".student-answer .slider-button-container").length;
        },
        dispatchUpdateToNavigator(navigator, updates) {
            let navigatorElement = this.$root.querySelector(`#${navigator}-navigator`);
            if (navigatorElement) {
                return navigatorElement.dispatchEvent(new CustomEvent("update-navigator", { detail: { ...updates } }));
            }
            console.warn("No navigation component found for the specified name.");
        },
        toggleTicked(event) {
            const parsedValue = this.isFloat(event.value) ? parseFloat(event.value) : parseInt(event.value);
            this.calculateNewScore(parsedValue, event.state);
            this.$root.querySelector(".score-slider-container")
                .dispatchEvent(new CustomEvent(
                    "new-score",
                    { detail: { score: this.score } }
                ));

        },
        isFloat(value) {
            return parseFloat(value.match(/^-?\d*(\.\d+)?$/)) > 0;
        },
        calculateNewScore(score, state) {
            let newScore = this.shadowScore = state === "on" ? this.shadowScore + score : this.shadowScore - score;

            if (!this.halfPoints) {
                newScore = state === "on" ? Math.ceil(newScore) : Math.floor(newScore);
            }
            if (newScore < 0) {
                this.score = this.shadowScore = 0;
                return;
            }

            if (newScore > this.maxScore) {
                this.score = this.shadowScore = this.maxScore;
                return;
            }

            this.score = newScore;
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
    Alpine.data("assessmentDrawer", () => ({
        activeTab: 1,
        tabs: [1, 2, 3],
        collapse: false,
        container: null,
        init() {
            this.container = this.$root.querySelector("#slide-container");
        },
        tab(index) {
            if (!this.tabs.includes(index)) return;
            this.activeTab = index;
            const slide = this.$root.querySelector(".slide-" + index);
            this.container.scroll({ left: slide.offsetLeft, behavior: "smooth" });
        }
    }));
    Alpine.data("scoreSlider", (score, model, maxScore, halfPoints, disabled, stack) => ({
        score,
        model,
        maxScore,
        timeOut: null,
        halfPoints,
        disabled,
        skipSync: false,
        persistantScore: null,
        getSliderBackgroundSize(el) {
            if (this.score === null) return 0;

            const min = el.min || 0;
            const max = el.max || 100;
            const value = el.value;

            return (value - min) / (max - min) * 100;
        },
        setSliderBackgroundSize(el) {
            el.style.setProperty("--slider-thumb-offset", `${25 / 100 * this.getSliderBackgroundSize(el) - 12.5}px`);
            el.style.setProperty("--slider-background-size", `${this.getSliderBackgroundSize(el)}%`);
        },
        syncInput() {
            // Don't update if the value is the same;
            if (this.$wire[this.model] === this.score) return;
            this.$wire.sync(this.model, this.score);
        },
        noChangeEventFallback() {
            if (this.score === null) {
                this.score = this.halfPoints ? this.maxScore / 2 : Math.round(this.maxScore / 2);
                this.syncInput();
            }
        },
        init() {
            // This echos custom JS from the template and for some reason it actually works;
            stack;

            this.$watch("score", (value, oldValue) => {
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

                const numberInput = this.$root.querySelector("[x-ref='score_slider_continuous_input']");
                if (numberInput !== null) {
                    this.setSliderBackgroundSize(numberInput);
                }
            });
            if (!this.disabled) {
                this.$nextTick(() => {
                    this.$root.querySelector("[x-ref='scoreInput']").focus();
                });
            }
        }
    }));
    Alpine.data("fastScoring", (scoreOptions, currentScore, disabled) => ({
        fastOption: null,
        scoreOptions,
        disabled,
        setOption(key) {
            this.fastOption = key;
            this.$dispatch("updated-score", { score: scoreOptions[key] });
        },
        updatedScore(score) {
            this.fastOption = score ? this.scoreOptions.indexOf(score) : null;
        },
        init() {
            this.fastOption = currentScore !== null ? this.scoreOptions.indexOf(currentScore) : null;
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
        emptyState: false
    });
    Alpine.store("questionBank", {
        active: false,
        inGroup: false
    });
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
