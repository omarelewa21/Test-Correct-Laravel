import Alpine from 'alpinejs';
import Choices from "choices.js";
import Intersect from '@alpinejs/intersect';

window.Alpine = Alpine;
Alpine.plugin(Intersect);

document.addEventListener('alpine:init', () => {
    Alpine.data('questionIndicator', () => ({
        showSlider: false,
        scrollStep: 100,
        totalScrollWidth: 0,
        activeQuestion: window.Livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).entangle('q')
    }));
    Alpine.data('tagManager', () => ({
        tags: [],
        remove: function (index) {
            this.tags.splice(index, 1)
        },
        add: function (inputElement) {
            if (inputElement.value) {
                this.tags.push(inputElement.value);
                inputElement.value = '';
            }
        },
    }));
    Alpine.data('selectSearch', (config) => ({

        data: config.data,

        emptyOptionsMessage: config.emptyOptionsMessage ?? 'No results match your search.',

        focusedOptionIndex: null,

        name: config.name,

        open: false,

        options: {},

        placeholder: config.placeholder ?? 'Select an option',

        search: '',

        value: config.value,

        closeListbox: function () {
            this.open = false

            this.focusedOptionIndex = null

            this.search = ''
        },

        focusNextOption: function () {
            if (this.focusedOptionIndex === null) return this.focusedOptionIndex = Object.keys(this.options).length - 1

            if (this.focusedOptionIndex + 1 >= Object.keys(this.options).length) return

            this.focusedOptionIndex++

            this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                block: "center",
            })
        },

        focusPreviousOption: function () {
            if (this.focusedOptionIndex === null) return this.focusedOptionIndex = 0

            if (this.focusedOptionIndex <= 0) return

            this.focusedOptionIndex--

            this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                block: "center",
            })
        },

        init: function () {
            this.options = this.data

            if (!(this.value in this.options)) this.value = null

            this.$watch('search', ((value) => {
                if (!this.open || !value) return this.options = this.data

                this.options = Object.keys(this.data)
                    .filter((key) => this.data[key].toLowerCase().includes(value.toLowerCase()))
                    .reduce((options, key) => {
                        options[key] = this.data[key]
                        return options
                    }, {})
            }))
        },

        selectOption: function () {
            if (!this.open) return this.toggleListboxVisibility()

            this.value = Object.keys(this.options)[this.focusedOptionIndex]

            this.closeListbox()
        },

        toggleListboxVisibility: function () {
            if (this.open) return this.closeListbox()

            this.focusedOptionIndex = Object.keys(this.options).indexOf(this.value)

            if (this.focusedOptionIndex < 0) this.focusedOptionIndex = 0

            this.open = true

            // this.$nextTick(() => {
            setTimeout(() => {
                this.$refs.search.focus()

                this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                    block: "center"
                })
            }, 10);
            // })
        },
    }));
    Alpine.data('selectionOptions', (entangle) => ({
        showPopup: entangle.value,
        editorId: entangle.editorId,
        hasError: {empty: [], false: []},
        data: {
            elements: [],

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
                .replace('[', '')
                .replace(']', '');

            let content = text;
            if (text.contains('|')) {
                content = text.split("|");
            }

            let currentDataRows = this.data.elements.length;
            this.data.elements[0].checked = 'true';

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
            })
        },

        addRow(value = '', checked = 'false') {
            let component = {
                id: this.data.elements.length,
                checked: checked,
                value: value,
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
                if (element.checked == 'true') {
                    this.data.elements = this.data.elements.map(item => {
                        item.checked = item.id == element.id ? 'true' : 'false';
                        return item;
                    })
                }
            });
        },

        insertDataInEditor: function () {
            let correct = this.data.elements.find(el => el.value != '' && el.checked == 'true');
            let result = this.data.elements.filter(el => el.value != '' && el.checked == 'false').map(el => el.value);

            result.unshift(correct.value)
            result = '[' + result.join('|') + ']';
            let lw = livewire.find(document.getElementById('cms').getAttribute('wire:id'));
            lw.set('showSelectionOptionsModal', true)

            window.editor.insertText(result);

            setTimeout(() => {
                this.$wire.setQuestionProperty('question', window.editor.getData());
            }, 300);
        },
        validateInput: function () {
            const emptyFields = this.data.elements.filter(element => element.value === '')
            const falseValues = this.data.elements.filter(element => element.checked === 'false')

            if (emptyFields.length !== 0 || this.data.elements.length === falseValues.length) {
                this.hasError.empty = emptyFields.map(item => item.id);

                if (this.data.elements.length === falseValues.length) {
                    this.hasError.false = falseValues.map(item => item.id);
                }

                Notify.notify('Niet alle velden zijn (correct) ingevuld', 'error');
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
                return true
            }
            return !!this.data.elements.find(element => element.value === '');
        },
        closePopup() {
            this.showPopup = false;
            this.data.elements = [];
            this.init();
        },
        canDelete() {
            return this.data.elements.length <= 2
        },
        resetHasError() {
            this.hasError.empty = [];
            this.hasError.false = [];
        }
    }));
    Alpine.data('badge', (videoUrl = null) => ({
        options: false,
        videoTitle: videoUrl,
        resolvingTitle: true,
        index: 1,
        async init() {
            this.setIndex();

            this.$watch('options', value => {
                if (value) {
                    let pWidth = this.$refs.optionscontainer.parentElement.offsetWidth;
                    let pPos = this.$refs.optionscontainer.parentElement.getBoundingClientRect().left;
                    if ((pWidth + pPos) < 288) {
                        this.$refs.optionscontainer.classList.remove('right-0');
                    }
                }
            })
            if (videoUrl) {
                const fetchedTitle = await getTitleForVideoUrl(videoUrl);
                this.videoTitle = fetchedTitle || videoUrl;
                this.resolvingTitle = false;
                this.$wire.setVideoTitle(videoUrl, this.videoTitle);
            }
        },
        setIndex() {
            const parent = document.getElementById('attachment-badges')
            this.index = Array.prototype.indexOf.call(parent.children, this.$el) + 1;
        }
    }));

    Alpine.data('drawingTool', (questionId, entanglements, isTeacher, isPreview = false) => ({
        show: false,
        questionId: questionId,
        answerSvg: entanglements.answerSvg,
        questionSvg: entanglements.questionSvg,
        gridSvg: entanglements.gridSvg,
        isTeacher: isTeacher,
        toolName: null,
        isPreview: isPreview,
        init() {
            this.toolName = `drawingTool_${questionId}`;
            if (Object.getOwnPropertyNames(window).includes(this.toolName)) {
                delete window[this.toolName];
            }
            const toolName = window[this.toolName] = initDrawingQuestion(this.$root, this.isTeacher, this.isPreview);

            if(this.isTeacher) {
                this.makeGridIfNecessary(toolName);
            }

            this.$watch('show', show => {
                if (show) {
                    toolName.Canvas.data.answer = this.answerSvg;
                    toolName.Canvas.data.question = this.questionSvg;

                    this.handleGrid(toolName);

                    toolName.drawingApp.init();
                } else {
                    const component = getClosestLivewireComponentByAttribute(this.$root, 'questionComponent');
                    component.call('render');
                }
            })

            toolName.Canvas.layers.answer.enable();
            if(this.isTeacher){
                toolName.Canvas.setCurrentLayer("question");
            } else {
                toolName.Canvas.setCurrentLayer("answer");
            }

        },
        handleGrid(toolName) {
            if (this.gridSvg !== '0.00' && this.gridSvg !== '') {
                let parsedGrid = parseFloat(this.gridSvg);
                toolName.UI.gridSize.value = parsedGrid;
                toolName.UI.gridToggle.checked = true;
                toolName.drawingApp.params.gridSize = parsedGrid;
                toolName.Canvas.layers.grid.params.hidden = false;

                if(!this.isTeacher) {
                    // this.$root.querySelector('#grid-background')?.remove();
                }
            }
        },
        makeGridIfNecessary(toolName) {
            if (this.gridSvg !== '' && this.gridSvg !== '0.00') {
                makePreviewGrid(toolName.drawingApp, this.gridSvg);
            }
        }
    }));
    Alpine.data('questionEditorSidebar', () => ({
        slideWidth: 300,
        drawer: null,
        init() {
            this.slideWidth = this.$root.offsetWidth;
            this.drawer = this.$root.closest('.drawer');
            setTimeout(() => {
                this.handleVerticalScroll(this.$root.firstElementChild);
                //To enable questionbank on startup :
                // this.showQuestionBank();
                // setTimeout(() => {
                //     this.$refs.questionEditorSidebar.scrollTo({
                //         left: this.$refs.questionEditorSidebar.scrollLeft - 300,
                //         behavior: 'smooth'
                //     });
                // },1000)

            }, 400);
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
        home() {
            this.scroll(0);
            this.$dispatch('backdrop');
            this.handleVerticalScroll(this.$refs.container1);
        },
        scroll(position) {
            this.drawer.scrollTo({top: 0, behavior: 'smooth'});
            this.$refs.questionEditorSidebar.scrollTo({
                left: position >= 0 ? position : 0,
                behavior: 'smooth'
            });
            this.$store.cms.scrollPos = 0
        },
        handleVerticalScroll(el) {

            this.$refs.questionEditorSidebar.style.minHeight = 'auto';
            this.$refs.questionEditorSidebar.style.height = 'auto';

            if (el.offsetHeight > this.drawer.offsetHeight) {
                this.drawer.classList.add('overflow-auto');
                this.drawer.classList.remove('overflow-hidden');
            } else {
                this.drawer.classList.add('overflow-hidden');
                this.drawer.classList.remove('overflow-auto');
            }
            
            this.$nextTick(() => {
                this.$refs.questionEditorSidebar.style.minHeight = this.drawer.offsetHeight+'px';
                this.$refs.questionEditorSidebar.style.height = el.offsetHeight+'px';
            })
        },
        setNextSlide(toInsert) {
            this.$root.insertBefore(toInsert, this.$root.querySelector('.slide-container[x-ref="container2"]').nextElementSibling);
        },
        showNewQuestion(container) {
            this.setNextSlide(this.$refs.newquestion);
            this.next(container);
        },
        showQuestionBank() {
            this.setNextSlide(this.$refs.questionbank);
            this.drawer.classList.add('fullscreen');
            const boundingRect = this.$refs.questionbank.getBoundingClientRect();
            this.scroll(boundingRect.x + boundingRect.width);
            this.$store.questionBank.active = true;
        },
        hideQuestionBank(container) {
            this.$root.querySelectorAll('.slide-container').forEach((slide) => {
                slide.classList.add('opacity-0')
            })
            this.$store.questionBank.active = false;
            this.$nextTick(() => {
                this.drawer.classList.remove('fullscreen');
                this.home();
                // this.scroll(container.parentElement.firstElementChild.offsetWidth);

                setTimeout(() => {
                    this.$root.querySelectorAll('.slide-container').forEach((slide) => {
                        slide.classList.remove('opacity-0')
                    })
                    this.$wire.emitTo('drawer.cms', 'refreshDrawer');
                }, 400)
                this.$wire.emitTo('drawer.cms', 'refreshDrawer');
            })
        },
        addQuestionToGroup(uuid) {
            this.showAddQuestionSlide()
            this.$store.questionBank.inGroup = uuid;
            this.$dispatch('backdrop');
        },
        addGroup(shouldCheckDirty = true) {
            if (shouldCheckDirty && this.$store.cms.dirty) {
                this.$wire.emitTo('teacher.questions.open-short', 'addQuestionFromDirty', {'group': true})
                return;
            }
            this.$wire.addGroup();
        },
        showAddQuestionSlide(shouldCheckDirty = true) {
            if (shouldCheckDirty && this.$store.cms.dirty) {
                this.$wire.emitTo('teacher.questions.open-short', 'addQuestionFromDirty', {'group': false})
                return;
            }

            this.next(this.$refs.container1);
        },
        backToQuestionOverview(container) {
            this.prev(container);
            this.$store.questionBank.inGroup = false;
        },
        handleResizing() {
            if (this.$store.questionBank.active) {
                this.$root.scrollLeft = this.$refs.questionbank.offsetLeft;
            }
        }
    }));
    Alpine.data('choices', (wireModel, multiple, options, config, filterContainer) => ({
        multiple: multiple,
        value: wireModel,
        options: options,
        config: config,
        wireModel: wireModel,
        activeFiltersContainer: null,
        choices: null,
        init() {
            // some new fancy way of setting a value when undefined
            window.registeredEventHandlers ??= []



            this.activeFiltersContainer = document.getElementById(filterContainer);
            this.multiple = multiple === 1;
            this.$nextTick(() => {
                let choices = new Choices(this.$refs.select, this.config);

                let refreshChoices = () => {
                    let selection = this.multiple ? this.value : [this.value]
                    choices.clearStore()
                    choices.setChoices(this.options.map(({value, label}) => ({
                        value,
                        label,
                        selected: selection.includes(value)
                    })))

                    this.handleActiveFilters(choices.getValue());
                }

                refreshChoices()

                this.$refs.select.addEventListener('choice', (event) => {
                    if (this.value.includes(parseInt(event.detail.choice.value))) {
                        this.removeFilterItem(choices.getValue().find(value => value.value === event.detail.choice.value));
                    }
                })
                this.$refs.select.addEventListener('change', () => {
                    this.value = choices.getValue(true)
                    // This causes 2 update calls:
                    // this.wireModel = this.value;
                })

                let eventName = 'removeFrom'+this.$root.dataset.modelName;
                if (!window.registeredEventHandlers.includes(eventName)) {
                    window.registeredEventHandlers.push(eventName)
                    window.addEventListener(eventName,(event) => {
                        this.removeFilterItem(event.detail);
                    })
                }

                this.$watch('value', () => refreshChoices());
                this.$watch('options', () => refreshChoices());
            });
        },

        removeFilterItem(item) {
            this.value = this.wireModel = this.value.filter(itemValue => itemValue !== item.value);
            this.clearFilterPill(item.value);
        },

        getDataSelector(item) {
            return `[data-filter="${this.$root.dataset.modelName}"][data-filter-value="${item}"]`
        },

        handleActiveFilters(choicesValues) {
            this.value.forEach(item => {
                if(this.needsFilterPill(item)) {
                    const cItem = choicesValues.find(value => value.value === item);
                    if(typeof cItem !== 'undefined'){
                        this.createFilterPill(cItem);
                    }
                }
            })
        },

        createFilterPill(item) {
            const element = document.getElementById('filter-pill-template').content.firstElementChild.cloneNode(true);
            // const element = document.createElement('span')
            element.id = `filter-${this.$root.dataset.modelName}-${item.value}`;
            element.classList.add('filter-pill');
            element.dataset.filter = this.$root.dataset.modelName;
            element.dataset.filterValue = item.value;
            element.firstElementChild.innerHTML = item.label;

            return this.activeFiltersContainer.appendChild(element);
        },

        needsFilterPill(item) {
            return this.activeFiltersContainer.querySelector(this.getDataSelector(item)) === null
        },

        clearFilterPill(item) {
            return this.activeFiltersContainer.querySelector(this.getDataSelector(item))?.remove();
        }

    }));

    Alpine.directive('global', function (el, {expression}) {
        let f = new Function('_', '$data', '_.' + expression + ' = $data;return;');
        f(window, el._x_dataStack[0]);
    });

    Alpine.store('cms', {
        loading: false,
        processing: false,
        dirty: false,
        scrollPos: 0,
        reinitOnClose: false,
    });
    Alpine.store('questionBank', {
        active: false,
        inGroup: false
    });
});

function getTitleForVideoUrl(videoUrl) {
    return fetch('https://noembed.com/embed?url=' + videoUrl)
        .then((response) => response.json())
        .then((data) => {
            if (!data.error) {
                return data.title;
            }
            return null;
        });
}