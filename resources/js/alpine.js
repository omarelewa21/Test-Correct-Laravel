import Alpine from 'alpinejs';

window.Alpine = Alpine;

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
    Alpine.data('selectionOptions', () => ({
        data: {
            elements: [],

        },

        init() {
            for (let i = 0; i < 3; i++) {
                this.addRow();
            }
        },

        initWithSelection() {
           let text = window.editor.getSelection();

        },



        addRow() {
            let component = {
                id: this.data.elements.length,
                checked: 'false',
                value: '',
            };
            this.data.elements.push(component);
        },

        trash(event, element) {
            event.stopPropagation();
            this.data.elements = this.data.elements.filter(el => el.id != element.id);
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

        save() {
            let correct = this.data.elements.find(el => el.value != '' && el.checked == 'true');
            let result = this.data.elements.filter(el => el.value != '' && el.checked == 'false').map(el => el.value);

            if (correct) {
                result.unshift(correct.value)
                result = '[' + result.join('|') + ']';
                let lw = livewire.find(document.getElementById('cms').getAttribute('wire:id'));
                lw.set('showSelectionOptionsModal', true)

                window.editor.insertText(result);

            } else {
                alert('none correct');
            }
        },
    }));


    Alpine.directive('global', function (el, {expression}) {
        let f = new Function('_', '$data', '_.' + expression + ' = $data;return;');
        f(window, el._x_dataStack[0]);
    });
});
