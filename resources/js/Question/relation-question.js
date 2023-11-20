import Alpine from "alpinejs";
import _ from "lodash";

class ListValidator {
    component = null;
    passed = true;
    errors = {};

    constructor(component) {
        this.component = component;
    }

    validate() {
        const methods = [
            "requiredTypeAmount",
            "duplicateColumns",
            "wordsWithoutType",
            "columnWithoutWords",
            "requiredSubjectWord",
            "requiredWordsPerRow"
        ];

        methods.forEach((method) => this[method]());

        return this;
    }

    failed(rule, perpetrators = []) {
        this.passed = false;
        this.errors[rule] = perpetrators;
    }

    countingRule(name, ruleCallback) {
        let errorTracker = [];
        ruleCallback(errorTracker);

        if (errorTracker.length === 0) {
            return;
        }

        this.failed(name, errorTracker);
    }

    requiredTypeAmount() {
        if (this.component.getUsedColumnHeads().length >= 2) {
            return;
        }

        this.failed("requiredTypeAmount");
    }

    duplicateColumns() {
        if (_.uniq(this.component.getUsedColumnHeads()).length === this.component.getUsedColumnHeads().length) {
            return;
        }

        const duplicates = _.filter(
            this.component.getUsedColumnHeads(),
            (value, index, iteratee) => _.includes(iteratee, value, index + 1)
        );

        this.failed("duplicateColumns", duplicates);
    }

    wordsWithoutType() {
        let unusedColumnsIndexes = this.component.cols.map((col, index) => {
            if (col === null) {
                return index;
            }
        }).filter(c => c !== undefined);

        const ruleCallback = (errorTracker) => {
            this.component.rows.forEach((row, rowIndex) => {
                row.forEach((word, index) => {
                    if (![null, ""].includes(word.text) && unusedColumnsIndexes.includes(index)) {
                        errorTracker.push([rowIndex, index]);
                    }
                });
            });
        };

        this.countingRule("wordsWithoutType", (errorTracker) => ruleCallback(errorTracker));
    }

    columnWithoutWords() {
        let usedColumnsIndexes = this.component.cols.map((col, index) => {
            if (col !== null) {
                return index;
            }
        }).filter(c => c !== undefined);

        const ruleCallback = (errorTracker) => {
            this.component.rows.forEach((row) => {
                usedColumnsIndexes.forEach(columnIndex => {
                    if (!errorTracker.includes(columnIndex)) return;
                    if (![null, ""].includes(row[columnIndex].text)) {
                        errorTracker = errorTracker.filter(column => column !== columnIndex);
                    }
                });
            });
        };

        this.countingRule("columnWithoutWords", (errorTracker) => ruleCallback(errorTracker));
    }

    requiredSubjectWord() {
        const subjectIndex = this.component.cols.findIndex((c) => c === "subject");
        if (subjectIndex === -1) {
            this.failed("requiredSubjectWord");
        }

        const ruleCallback = (errorTracker) => {
            this.component.rows.forEach((row, rowIndex) => {
                if (this.component.wordsInRow(row) === 0) return;
                if ([null, ""].includes(row[subjectIndex]?.text)) {
                    errorTracker.push([rowIndex, subjectIndex]);
                }
            });
        };

        this.countingRule("requiredSubjectWord", (errorTracker) => ruleCallback(errorTracker));
    }

    requiredWordsPerRow() {
        const ruleCallback = (errorTracker) => {
            this.component.rows.forEach((row, rowIndex) => {
                if (this.component.wordsInRow(row) === 1) {
                    errorTracker.push(rowIndex);
                }
            });
        };

        this.countingRule("requiredWordsPerRow", (errorTracker) => ruleCallback(errorTracker));
    }
}

document.addEventListener("alpine:init", () => {
    Alpine.data("relationQuestionGrid", () => ({
        rows: [],
        selectedColumn: null,
        disabledColumns: [],
        updates: [],
        updateTimer: false,
        async init() {
            this.rows = await this.$wire.retrieveWords();
            this.setDisabledColumns();

            // let activeColumn = [];
            // for (const row of this.loopRows()) {
            //      let selected = Object.values(row).filter(word => word.selected === true);
            //      if (!activeColumn.includes(selected.type)) {
            //          activeColumn.push(selected.type)
            //      }
            //      if (activeColumn.length > 1) {
            //          break;
            //      }
            // }
            //
            // if (activeColumn.length === 1) {
            //
            // }

            // this.$wire.call("openCompileListsModal");
        },
        selectColumn(column) {
            this.selectedColumn = column;
            this.loopRows().forEach((rowKey) => {
                this.deselectColumns(this.rows[rowKey], rowKey);
                let word = this.rows[rowKey][column];
                if (word?.text === null) {
                    word = this.rows[rowKey]["subject"];
                }
                this.selectWord(rowKey, word);
            });
        },
        selectWord(rowIndex, word) {
            if (word.text === null || word.selected === true) return;
            if (this.selectedColumn !== word.type) {
                this.selectedColumn = null;
            }
            this.deselectColumns(this.rows[rowIndex], rowIndex);

            this.addUpdate(rowIndex, word.word_id, true);
            word.selected = true;
        },
        deselectColumns(row, index) {
            Object.keys(row).forEach(key => {
                let word = row[key];
                if (word.word_id && word.selected === true) {
                    this.addUpdate(index, row[key].word_id, false);
                    row[key].selected = false;
                }
            });
        },
        loopRows() {
            return Object.keys(this.rows);
        },
        setDisabledColumns() {
            this.loopRows().reduce((count, key) => {
                let skipRow = [];
                Object.keys(this.rows[key]).forEach(column => {
                    let word = this.rows[key][column];
                    if (skipRow.includes(key) || (column === "subject" && word.text === null)) {
                        skipRow.push(key);
                        return;
                    }
                    if (word.text === null && !(this.disabledColumns.includes(column))) {
                        this.disabledColumns.push(column);
                    }
                });
            });
        },
        addUpdate(row, word_id, selected) {
            let existing = this.updates.find(update => update.row === parseInt(row) && update.word_id === word_id);
            if (existing) {
                existing.selected = selected;
            } else {
                this.updates.push({ row: parseInt(row), word_id, selected });
            }

            if (this.updateTimer) clearTimeout(this.updateTimer);
            this.updateTimer = setTimeout(() => {
                this.$wire.call("makeUpdates", this.updates);
                this.updates = [];
            }, 750);
        },
        getText(word, rowIndex) {
            return word?.text ?? "";
        },
        handleIncomingUpdatedRows(rows) {
            this.rows = rows;
        }
    }));

    Alpine.data("compileList", (list, columns) => ({
        expanded: true,
        cols: [],
        rows: [],
        list,
        wordCount: 0,
        selectedWordCount: 0,
        originalRows: [],
        errorstate: false,
        init() {
            this.list.rows = Object.values(this.list.rows);
            this.buildGrid();

            this.countWords();

            this.$nextTick(() => {
                this.setGridSizeProperties();

                this.selectUsedColumnHeads();

                this.setEnabledRows();
            });
        },
        buildGrid() {
            for (let i = 0; i < 7; i++) {
                this.cols[i] = this.getUsedTypes()[i] ?? null;
            }

            this.rows = this.list
                .rows
                .map(row => this.buildRow(row));

            if (this.rows.length < 10) {
                let add = 10 - this.rows.length;
                for (let i = this.rows.length; i < add; i++) {
                    this.rows[i] = this.buildRow([]);
                }
            }

            this.addEmptyRowWhenLastIsFull();
        },
        setGridSizeProperties() {
            const gridContainer = this.$root.querySelector(".relation-question-grid-container");
            const grid = this.$root.querySelector(".relation-question-grid");
            const heading = 57;
            const cell = 40;
            const gap = 1;
            const maxCellWidth = 240;
            const rows = this.rows?.length ?? 10;

            gridContainer.style.setProperty(
                "--relation-question-height",
                `calc(${heading}px + calc(${rows} * ${cell + gap}px))`
            );
            gridContainer.style.setProperty(
                "--relation-question-total-max-width",
                `calc(${this.cols.length} * ${maxCellWidth}px)`
            );

            grid.style.setProperty("--relation-grid-cols", this.cols.length);
        },
        toggleAll(element) {
            let enabled = element.checked;
            this.$root.querySelectorAll(".word-row .checkbox-container input").forEach((check, row) => {
                if (this.wordsInRow(this.rows[row]) === 0) return true;
                check.checked = enabled;
                this.toggleRow(check, row);
            });
        },
        toggleRow(checkbox, row) {
            let columnCheckbox = this.$root.querySelector(".head-checkmark .checkbox-container input");
            let availableBoxes = Array.from(this.$root.querySelectorAll(".word-row .checkbox-container input"));

            if (this.wordsInRow(this.rows[this.rows.length - 1]) === 0) {
                availableBoxes.pop();
            }

            if (checkbox.checked === false) {
                this.list.enabledRows = this.list.enabledRows.filter(value => value !== row);
                if (columnCheckbox.checked) {
                    columnCheckbox.checked = false;
                }
            }

            if (checkbox.checked === true) {
                if (!this.list.enabledRows.includes(row)) {
                    this.list.enabledRows.push(row);
                }

                let everythingChecked = availableBoxes.filter((check) => !check.checked).length === 0;
                if (columnCheckbox.checked === false && everythingChecked) {
                    columnCheckbox.checked = true;
                }
            }

            this.countWords();
        },
        selectUsedColumnHeads() {
            let usedCols = this.getUsedTypes();
            let selectBoxes = this.$root.querySelectorAll(".single-select");
            usedCols.forEach((usedCol, key) => {
                let index = this.cols.findIndex((col) => col === usedCol);
                selectBoxes[index].querySelector(`.option[data-value="${usedCol}"]`).click();
            });
        },
        getUsedTypes() {
            return _.uniq(this.list.rows.flatMap(r => Object.keys(r)));
        },
        getUsedColumnHeads() {
            return this.cols.filter(c => c !== null);
        },
        buildRow(row) {
            let newRow = [];
            for (let i = 0; i < this.cols.length; i++) {
                newRow[i] = row[this.cols[i]] ?? {
                    text: null,
                    word_id: null,
                    word_list_id: this.list.id,
                    type: null
                };
            }
            return newRow;
        },
        setEnabledRows() {
            this.list?.enabledRows.forEach(key => {
                const input = this.$root.querySelector(`.word-row.row-${key} .checkbox-container input`);
                input.checked = true;
                this.originalRows.push(key);
                this.toggleRow(input, key);
            });
        },
        countWords() {
            const oldWordCount = this.wordCount;
            const oldSelectedWordCount = this.selectedWordCount;
            this.wordCount = 0;
            this.selectedWordCount = 0;

            this.rows.forEach((row, key) => {
                const rowCount = this.wordsInRow(row);
                this.wordCount += rowCount;
                if (this.$root.querySelector(`.word-row.row-${key} .row-checkmark input:checked`)) {
                    this.selectedWordCount += rowCount;
                }
            });

            this.wordCountChanges(oldWordCount, this.wordCount);
            this.selectedWordCountChanges(oldSelectedWordCount, this.selectedWordCount);
        },
        wordsUpdated(word, rowIndex, columnIndex) {
            this.countWords();
        },
        placeCursor(element) {
            if (!element.value) return;
            let range = document.createRange();
            let sel = window.getSelection();

            range.setStart(
                element.childNodes[element.childNodes.length - 1] ?? 0,
                element.childNodes[element.childNodes.length - 1]?.length ?? 0
            );
            range.collapse(true);

            sel.removeAllRanges();
            sel.addRange(range);
        },
        move(direction, currentElement) {
            let row = parseInt(currentElement.dataset.rowValue);
            let column = parseInt(currentElement.dataset.columnValue);

            switch (direction) {
                case "up":
                    row = row - 1;
                    break;
                case "right":
                    column = column + 1;
                    break;
                case "down":
                    row = row + 1;
                    break;
                case "left":
                    column = column - 1;
                    break;
            }

            this.$root.querySelector(locator(row, column))?.focus();

            function locator(newRow, newColumn) {
                return `.word-row span[data-row-value="${newRow}"][data-column-value="${newColumn}"]`;
            }
        },
        addEmptyRowWhenLastIsFull() {
            if (this.wordsInRow(this.rows[this.rows.length - 1]) > 0) {
                this.rows[this.rows.length] = this.buildRow([]);
            }
        },
        wordsInRow(row) {
            return row.filter(item => ![null, ""].includes(item.text)).length;
        },
        columnValueUpdated(headerIndex, value) {
            if (typeof value === "string" && value === "") {
                value = null;
            }
            this.cols[headerIndex] = value;
            this.handleDisabledHeaders();
        },
        handleDisabledHeaders() {
            if (this.getUsedColumnHeads().length === Object.keys(columns).length) {
                this.$root
                    .querySelectorAll(`.grid-head .single-select`)
                    .forEach((select, index) => {
                        if (this.cols[index] === null) {
                            select.dispatchEvent(new CustomEvent(
                                "disable-single-select",
                                { detail: {} }
                            ));
                        }
                    });
                return;
            }

            this.$root
                .querySelectorAll(`.grid-head .single-select.disabled`)
                .forEach((select, index) => {
                    select.dispatchEvent(new CustomEvent(
                        "enable-single-select",
                        { detail: {} }
                    ));
                });
        },
        validate() {
            const validator = new ListValidator(this).validate();

            this.errorstate = !validator.passed;

            return validator;
        },
        getUpdatesForCompiling() {
            return {
                name: this.list.name,
                rows: this.rows.map((row, rowIndex) => {
                    if (this.wordsInRow(row) === 0) {
                        return null;
                    }

                    return row.map((word, index) => {
                        if (word.text === null && word.word_id === null) {
                            return null;
                        }

                        word.type = this.cols[index];
                        if (word.word_list_id === null) {
                            word.word_list_id = this.list.id;
                        }

                        return word;
                    }).filter(Boolean);
                }).filter(Boolean),
                enabled: Array.from(this.list.enabledRows)
            };
        },
        addFromWordListBank() {
            this.openVersionablePanel({ sliderButtonSelected: "lists" });
        },
        addFromWordBank() {
            this.openVersionablePanel({ sliderButtonSelected: "words" });
        },
        addFromUpload() {
            console.log("uploodjes trekken");
        }
    }));

    Alpine.data("compileWordListContainer", (wordLists) => ({
        wordLists,
        globalWordCount: 0,
        globalSelectedWordCount: 0,
        compiling: false,
        showAddListModal: false,
        blueprint() {
            return {
                name: ``,
                id: ``,
                rows: {},
                enabledRows: []
            };
        },
        wordCountChanges(old, newCount) {
            this.globalWordCount = this.handleGlobalChanges(this.globalWordCount, old, newCount);
        },
        selectedWordCountChanges(old, newCount) {
            this.globalSelectedWordCount = this.handleGlobalChanges(this.globalSelectedWordCount, old, newCount);
        },
        handleGlobalChanges(property, old, newCount) {
            property -= old;
            property += newCount;
            return property;
        },
        addWordList() {
            this.$root.closest(".compile-list-modal")
                .querySelector("#add-list-modal")
                .dispatchEvent(
                    new CustomEvent("open-modal")
                );
        },
        async addNewWordList() {
            this.wordLists.push(await this.$wire.call("createNewList"));
        },
        openAddExistingWordListPanel() {
            this.openVersionablePanel({
                sliderButtonDisabled: true,
                sliderButtonSelected: "lists",
                showSliderButtons: false,
                closeOnFirstAdd: true
            });
        },
        async addExistingWordList(uuid) {
            const list = await this.$wire.call("addExistingWordList", uuid);
            if (!list.id) {
                this.$dispatch("notify", { message: "Er is iets misgegaan...", type: "error" });
                return;
            }
            this.wordLists.push(list);
            this.$dispatch("notify", { message: "Gelukt!" });

        },
        uploadWordList() {

        },
        async compileLists() {
            this.compiling = true;
            const listComponents = Array.from(this.$root.querySelectorAll(".word-list"))
                .map(element => element._x_dataStack[0]);

            if (this.listsValidationFailed(listComponents)) {
                return;
            }

            const updates = [];
            listComponents.forEach((component) => updates[component.list.id] = component.getUpdatesForCompiling());

            console.dir(updates);

            await this.$wire.call("compile", updates);

            this.compiling = false;
        },
        listsValidationFailed(components) {
            let failedValidation = false;

            components.forEach(component => {
                let validator = component.validate();
                if (!validator.passed) {
                    console.log(validator);
                    failedValidation = true;
                }
            });

            if (failedValidation) {
                console.log("validation failed");
            }

            return failedValidation;
        },
        hideModal() {
            document.querySelector("#LivewireUIModal").dispatchEvent(
                new CustomEvent("hide-modal")
            );
        },
        openVersionablePanel(config) {
            const defaultConfig = {
                sliderButtonDisabled: false,
                sliderButtonSelected: "lists",
                showSliderButtons: true,
                closeOnFirstAdd: false
            };

            this.hideModal();
            this.$store.sidePanel.reopenModal = true;

            this.$wire.emit(
                "openPanel",
                "teacher.versionable-side-panel-container",
                Object.assign({}, defaultConfig, config),
                { offsetTop: 70, width: "95vw" }
            );
        }
    }));


    Alpine.bind("gridcell", () => ({
        contenteditable: "plaintext-only",
        ["@input"]() {
            this.$el._x_model.set(this.$el.textContent);
            this.addEmptyRowWhenLastIsFull();
        },
        ["x-init"]() {
            this.$nextTick(() => {
                this.$el.textContent = this.$el._x_model.get();
            });
        }
    }));
});