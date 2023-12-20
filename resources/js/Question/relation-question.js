import Alpine from "alpinejs";
import _ from "lodash";

class ListValidator {
    component = null;
    passed = true;
    errors = {};
    messages = {};

    localization = [];
    methods = [
        "requiredTypeAmount",
        "duplicateColumns",
        "wordsWithoutType",
        "columnWithoutWords",
        "requiredSubjectWord",
        "requiredWordsPerRow"
    ];

    constructor(component) {
        this.component = component;
        this.localization = document.getElementById("word-list-modal-validation-strings").dataset;
    }

    validate() {
        this.methods.forEach((method) => this[method]());

        return this;
    }

    failed(rule, perpetrators, message) {
        this.passed = false;
        this.errors[rule] = perpetrators;
        this.messages[rule] = message;
    }

    countingRule(name, ruleCallback, messageCallback) {
        let errorTracker = [];
        ruleCallback(errorTracker);

        if (errorTracker.length === 0) {
            return;
        }

        let message = this.localization[((errorTracker.length > 1) ? name + "Multi" : name)];

        this.failed(name, errorTracker, messageCallback(message, errorTracker));
    }

    requiredTypeAmount() {
        /* Need at least 2 columns set */
        if (this.component.getUsedColumnHeads().length >= 2) {
            return;
        }

        this.failed("requiredTypeAmount", [], this.localization["requiredTypeAmount"]);
    }

    duplicateColumns() {
        /* Has duplicate selected column heads */
        if (_.uniq(this.component.getUsedColumnHeads()).length === this.component.getUsedColumnHeads().length) {
            return;
        }

        const duplicates = _.filter(
            this.component.getUsedColumnHeads(),
            (value, index, iteratee) => _.includes(iteratee, value, index + 1)
        );

        this.failed("duplicateColumns", duplicates, this.localization["duplicateColumns"]);
    }

    wordsWithoutType() {
        /* Words in column without a column type set */
        let unusedColumnsIndexes = this.component.cols.map((col, index) => {
            if (col === null) {
                return index;
            }
        }).filter(c => c !== undefined);

        const ruleCallback = (errorTracker) => {
            this.component.rows.forEach((row, rowIndex) => {
                row.forEach((word, index) => {
                    if (errorTracker.includes(index)) return;
                    if (![null, ""].includes(word.text) && unusedColumnsIndexes.includes(index)) {
                        errorTracker.push(index);
                    }
                });
            });
        };

        const messageCallback = (message, errorTracker) => {
            return message.replace("%column%", this.joinErrorsForMessage(errorTracker));
        };

        this.countingRule(
            "wordsWithoutType",
            (errorTracker) => ruleCallback(errorTracker),
            (message, errorTracker) => messageCallback(message, errorTracker)
        );
    }

    columnWithoutWords() {
        /* Column head selected without words in it */
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

        const messageCallback = (message, errorTracker) => {
            const colNames = this.component.cols.map((col, index) => {
                if (errorTracker.includes(index)) {
                    return col;
                }
            }).filter(c => c !== undefined);

            return message.replace("%type%", this.joinErrorsForMessage(colNames));
        };
        this.countingRule(
            "columnWithoutWords",
            (errorTracker) => ruleCallback(errorTracker),
            (message, errorTracker) => messageCallback(message, errorTracker)
        );
    }

    requiredSubjectWord() {
        /* The subject column does not have a value while other columns in this row have */
        const subjectIndex = this.component.cols.findIndex((c) => c === "subject");
        if (subjectIndex === -1) {
            return;
        }

        const ruleCallback = (errorTracker) => {
            this.component.rows.forEach((row, rowIndex) => {
                if (this.component.wordsInRow(row) === 0) return;
                if ([null, ""].includes(row[subjectIndex]?.text)) {
                    errorTracker.push([rowIndex, subjectIndex]);
                }
            });
        };
        const messageCallback = (message, errorTracker) => {
            const rows = errorTracker.map(coords => coords[0]);
            return message.replace("%row%", this.joinErrorsForMessage(rows));
        };

        this.countingRule(
            "requiredSubjectWord",
            (errorTracker) => ruleCallback(errorTracker),
            (message, errorTracker) => messageCallback(message, errorTracker)
        );
    }

    requiredWordsPerRow() {
        /* Need at least 2 words in a row, Subject and a different one */
        const ruleCallback = (errorTracker) => {
            this.component.rows.forEach((row, rowIndex) => {
                if (this.component.wordsInRow(row) === 1) {
                    errorTracker.push(rowIndex);
                }
            });
        };

        const messageCallback = (message, errorTracker) => {
            return message.replace("%row%", this.joinErrorsForMessage(errorTracker));
        };

        this.countingRule(
            "requiredWordsPerRow",
            (errorTracker) => ruleCallback(errorTracker),
            (message, errorTracker) => messageCallback(message, errorTracker)
        );
    }

    joinErrorsForMessage(errorTracker) {
        if (errorTracker.length === 1) {
            return (errorTracker[0] + 1).toString();
        }

        const joinedString = errorTracker.slice(0, -1).map(i => i + 1).join(", ");
        return `${joinedString} ${this.localization.and} ${(errorTracker[errorTracker.length - 1] + 1)}`;
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
            this.setActiveColumn();
        },
        selectColumn(column, updateWords = true) {
            this.selectedColumn = column;
            if (!updateWords) return;
            this.loopRows().forEach((rowKey) => {
                this.deselectColumns(this.rows[rowKey], rowKey);
                let word = this.rows[rowKey][column];
                if ([null, ""].includes(word?.text)) {
                    word = this.rows[rowKey]["subject"];
                }
                this.selectWord(rowKey, word);
            });
        },
        selectWord(rowIndex, word) {
            if ([null, ""].includes(word.text) || word.selected === true) return;
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
                    if (skipRow.includes(key) || (column === "subject" && [null, ""].includes(word.text))) {
                        skipRow.push(key);
                        return;
                    }
                    if ([null, ""].includes(word.text) && !(this.disabledColumns.includes(column))) {
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
            this.setDisabledColumns();
            this.setActiveColumn();
        },
        setActiveColumn() {
            let activeColumns = [];
            Object.values(this.rows).forEach(row => {
                row = Object.values(row);
                if (!this.wordsInRow(row)) return;
                activeColumns.push(
                    row.filter(w => w.selected === true)[0].type
                );
            });
            const uniqueColumns = _.uniq(activeColumns);
            if (uniqueColumns.length === 1) {
                this.selectColumn(uniqueColumns[0], false);
            }
        },
        wordsInRow(row) {
            return row?.filter(item => ![null, ""].includes(item.text))?.length ?? false;
        }
    }));

    Alpine.data("compileList", (list, columns) => ({
        expanded: true,
        cols: [],
        rows: [],
        list,
        wordCount: 0,
        selectedWordCount: 0,
        errorState: false,
        mutation: 1,
        errorMessages: {},
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
                this.cols[i] = this.getUsedTypes(this.list.rows)[i] ?? null;
            }

            this.rows = this.list
                .rows
                .map(row => this.buildRow(row));

            this.addMinimumAmountOfRows();

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
            let usedCols = this.getUsedTypes(this.list.rows);
            let selectBoxes = this.$root.querySelectorAll(".single-select");
            usedCols.forEach((usedCol, key) => {
                let index = this.cols.findIndex((col) => col === usedCol);
                selectBoxes[index].querySelector(`.option[data-value="${usedCol}"]`).click();
            });
        },
        getUsedTypes(rows) {
            return _.uniq(rows.flatMap(r => Object.keys(r)));
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
            if (this.errorState) {
                this.resetErrorState();
            }
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
        move(event, direction, currentElement) {
            if (event.shiftKey || event.altKey) return;
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
        addMinimumAmountOfRows() {
            if (this.rows.length < 10) {
                let add = 10 - this.rows.length;
                const index = this.rows.length;

                for (let i = index; i < (add + index); i++) {
                    this.rows[i] = this.buildRow([]);
                }
            }
        },
        addEmptyRowWhenLastIsFull() {
            if (this.wordsInRow(this.rows[this.rows.length - 1]) > 0) {
                this.rows[this.rows.length] = this.buildRow([]);
            }
        },
        removeEmptyTrailingRow() {
            if (this.wordsInRow(this.rows[this.rows.length - 1]) === 0) {
                this.rows.pop();

                if (this.rows.length !== 0) {
                    this.removeEmptyTrailingRow();
                }
            }
        },
        wordsInRow(row) {
            return row?.filter(item => ![null, ""].includes(item.text))?.length ?? false;
        },
        columnValueUpdated(headerIndex, value) {
            if (this.errorState) {
                this.resetErrorState();
            }
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

            if (validator.passed) {
                this.errorState = false;
                return validator;
            }

            this.markFailedItemsWithErrors(Object.entries(validator.errors));
            this.showErrorMessages(validator.messages);

            this.errorState = true;

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

                        return word;
                    }).filter(Boolean);
                }).filter(Boolean),
                enabled: Array.from(this.list.enabledRows) ?? []
            };
        },
        addFromWordListBank() {
            this.openVersionablePanel({
                sliderButtonSelected: "lists",
                listUuid: this.list.uuid,
                used: this.getUsedItemsForSidePanel()
            });
        },
        addFromWordBank() {
            this.openVersionablePanel({
                sliderButtonSelected: "words",
                listUuid: this.list.uuid,
                used: this.getUsedItemsForSidePanel()
            });
        },
        addFromUpload() {
            this.$root.closest(".compile-list-modal")
                .querySelector("#compile-list-upload-modal")
                .dispatchEvent(
                    new CustomEvent("open-modal", { detail: { list: this.list.uuid } })
                );
        },
        async addExistingWordListToList(uuid) {
            const list = await this.$wire.call("addExistingWordList", uuid, true);

            this._handleExternalList(list);
        },
        async addExistingWordToList(uuid) {
            const row = await this.$wire.call("addExistingWord", uuid);

            this._handleExternalRows([row]);
        },
        async addUploadToList(file) {
            await this.$wire.upload(
                "importFile",
                file,
                async (uploadedFilename) => {
                    this._handleExternalRows(await this.$wire.call("importIntoList", false, this.cols));
                },
                () => {
                },
                (event) => {
                }
            );
        },
        handleIncomingExistingColumns(list) {
            const newCols = this.getUsedTypes(list.rows).filter((c) => !this.cols.includes(c));
            if (newCols.length > 0) {
                let selectBoxes = this.$root.querySelectorAll(".single-select");
                newCols.forEach(newCol => {
                    const i = this.cols.findIndex(c => c === null);
                    selectBoxes[i].querySelector(`.option[data-value="${newCol}"]`).click();
                });
            }
        },
        addTemplateMutation() {
            this.mutation++;
        },
        getTemplateRowKey(row, rowIndex) {
            return `row-${rowIndex}-${this.mutation}`;
        },
        getTemplateWordKey(word, wordIndex) {
            return `word-${wordIndex}-${this.mutation}`;
        },
        getUsedItemsForSidePanel() {
            return {
                lists: _.uniq(this.rows.flatMap(r => _.uniq(r.map(w => w.word_list_id)))),
                words: _.uniq(this.rows.flatMap(r => _.uniq(r.map(w => w.word_id)))).filter(Boolean)
            };
        },
        _handleExternalList(list) {
            if (!list.id) return this.dispatchError();

            /*Prepare*/
            this.handleIncomingExistingColumns(list);

            /* Mutate */
            this.removeEmptyTrailingRow();

            this.rows.push(...list.rows.map(row => this.buildRow(row)));

            this.externalAdditionAfterCare();
        },
        _handleExternalRows(rows) {
            if (!Object.keys(rows).length) return this.dispatchError();

            /*Prepare*/
            this.handleIncomingExistingColumns({ rows: rows });

            /* Mutate */
            this.removeEmptyTrailingRow();

            this.rows.push(...Object.values(rows).map(row => this.buildRow(row)));

            this.externalAdditionAfterCare();
        },
        externalAdditionAfterCare() {
            this.countWords();
            this.addMinimumAmountOfRows();
            this.addEmptyRowWhenLastIsFull();
            this.addTemplateMutation();
        },
        dispatchError() {
            this.$dispatch("notify", { message: "Er is iets misgegaan...", type: "error" });
        },
        dispatchSuccess() {
            this.$dispatch("notify", { message: "Gelukt!" });
        },
        resetErrorState() {
            this.$root.querySelectorAll(".single-select.error")
                .forEach(select => select.dispatchEvent(new CustomEvent("disable-error-state")));

            this.$root.querySelectorAll(".word-cell.validation-error")
                .forEach(cell => cell.classList.remove("validation-error"));

            this.$root.querySelectorAll(".notification.error")
                .forEach(notification => notification.dispatchEvent(new CustomEvent("hide-error")));

            this.errorState = false;
        },
        markFailedItemsWithErrors(errors) {
            for (const [name, error] of errors) {
                if (name === "requiredTypeAmount") { // => Highlight all non-set columns;
                    this.$root.querySelectorAll(`.single-select[data-selected-value="none"]:not(.disabled)`)
                        ?.forEach(select => select.dispatchEvent(new CustomEvent("enable-error-state")));
                }
                if (name === "duplicateColumns") { // => Highlight all duplicate columns
                    error.forEach(column => {
                        this.$root.querySelectorAll(`.single-select[data-selected-value="${column}"]`)
                            ?.forEach(select => select.dispatchEvent(new CustomEvent("enable-error-state")));
                    });
                }
                if (name === "wordsWithoutType") { // => Highlight column without type set
                    this.$root.querySelectorAll(`.single-select`)
                        .forEach((select, key) => {
                            if (!error.includes(key)) return;
                            select.dispatchEvent(new CustomEvent("enable-error-state"));
                        });
                }
                if (name === "columnWithoutWords") { // => Highlight column without words
                    this.$root.querySelectorAll(`.single-select`)
                        .forEach((select, key) => {
                            if (!error.includes(key)) return;
                            select.dispatchEvent(new CustomEvent("enable-error-state"));
                        });
                }
                if (name === "requiredSubjectWord") { // => Highlight subject column empty fields
                    error.forEach((coords) => {
                        this.$root.querySelector(rowSelector(coords[0]) + colSelector(coords[1]))
                            ?.parentElement
                            .classList
                            .add("validation-error");
                    });
                }
                if (name === "requiredWordsPerRow") { // => Highlight row with missing fields
                    error.forEach((row) => {
                        this.$root.querySelectorAll(rowSelector(row))
                            .forEach(cell => {
                                cell?.parentElement
                                    .classList
                                    .add("validation-error");
                            });
                    });
                }
            }

            function rowSelector(row) {
                return `[data-row-value="${row}"]`;
            }

            function colSelector(col) {
                return `[data-column-value="${col}"]`;
            }
        },
        removeErrorMessage(message) {
            delete this.errorMessages[message];
        },
        showErrorMessages(messages) {
            this.errorMessages = messages;
        }
    }));

    Alpine.data("compileWordListContainer", (wordLists) => ({
        wordLists,
        globalWordCount: 0,
        globalSelectedWordCount: 0,
        compiling: false,
        showAddListModal: false,
        init() {
            if(!Object.keys(this.wordLists).length) {
                this.$nextTick(() => this.addWordList());
            }
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
            this.$root.closest(".compile-list-modal")
                .querySelector("#compile-list-upload-modal")
                .dispatchEvent(
                    new CustomEvent("open-modal")
                );
        },
        async compileLists() {
            this.compiling = true;
            const listComponents = Array.from(this.$root.querySelectorAll(".word-list"))
                .map(element => element._x_dataStack[0]);

            if (this.listsValidationFailed(listComponents)) {
                this.compiling = false;
                return;
            }

            const updates = [];
            listComponents.forEach((component) => updates[component.list.id] = component.getUpdatesForCompiling());

            const changesCompiled = await this.$wire.call("compile", updates);
            if (!changesCompiled) {
                this.$dispatch("notify", { message: "Something went wrong...", type: "error" });
            }

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
                closeOnFirstAdd: false,
                listUuid: ""
            };

            this.hideModal();
            this.$store.sidePanel.reopenModalWhenDone = true;

            this.$wire.emit(
                "openPanel",
                "teacher.versionable-side-panel-container",
                Object.assign({}, defaultConfig, config),
                { offsetTop: 70, width: "95vw" }
            );
        },
        async addUploadToNew(file) {
            await this.$wire.upload(
                "importFile",
                file,
                async (uploadedFilename) => {
                    let list = await this.$wire.call("importIntoList", true);

                    if (!list.id) {
                        this.$dispatch("notify", { message: "Er is iets misgegaan...", type: "error" });
                        return;
                    }
                    this.wordLists.push(list);
                    this.$dispatch("notify", { message: "Gelukt!" });
                },
                () => {
                    this.$dispatch("notify", { message: "Er is iets misgegaan...", type: "error" });
                },
                (event) => {
                }
            );
        }
    }));

    Alpine.data("versionableOverviewManager", (sliderButton, closeOnFirstAdd, listUuid, showSliderButtons) => ({
        view: sliderButton,
        closeOnFirstAdd,
        listUuid,
        showSliderButtons,
        addListPromptShown: false,
        addListSeparate: false,
        init() {
            this.$watch("view", value => {
                this.nudgeOverviewToFixTheirChoices(value);
            });
        },
        done() {
            this.openSidePanel = false;
        },
        add(type, uuid) {
            this.dispatchUpdate(type, uuid);

            if (this.closeOnFirstAdd) {
                this.done();
            }
        },
        dispatchUpdate(type, uuid) {
            let selector = ".word-list-container";
            if (this.listUuid && !this.addListSeparate) {
                selector += ` [data-list-uuid='${this.listUuid}']`;
            }
            document.querySelector(selector).dispatchEvent(
                new CustomEvent(`add-${type}`, { detail: { uuid } })
            );

            this.addListSeparate = false;
        },
        nudgeOverviewToFixTheirChoices(value) {
            this.$root.querySelectorAll(`#${value}-view-container .custom-choices`).forEach(choice => {
                setTimeout(() => {
                    choice.dispatchEvent(new CustomEvent("reset-width"));
                }, 10);
            });
        },
        addList(list, separateOverride = false) {
            if (this.showSliderButtons === true && this.addListPromptShown === false) {
                // Prompt for adding list as a whole or insert in existing
                this.addListPromptShown = true;
                this.containerRoot()
                    .querySelector("#choose-add-list-modal")
                    ?.dispatchEvent(
                        new CustomEvent(
                            "open-modal",
                            { detail: { list } }
                        )
                    );
                return;
            }

            if (separateOverride === true) {
                this.addListSeparate = true;
            }

            this.add("list", list.uuid);
            this.overviewWire('word-lists').call("addToUsed", list.id, true);
            this.overviewWire('word-lists').emit("newListAdded", list.id);

            this.addListPromptShown = false;
        },
        addWord(uuid, id) {
            this.add("word", uuid);
            this.overviewWire('words').call("addToUsed", id);
        },
        wire(id) {
            return window.Livewire.find(id);
        },
        containerRoot() {
            if (this.$root.id === 'versionable-side-panel-container') {
                return this.$root;
            }
            return this.$root.closest("#versionable-side-panel-container");
        },
        containerWire() {
            return this.wire(this.containerRoot().getAttribute('wire:id'));
        },
        overviewRoot(type){
            if (this.$root.id === (type + '-overview')) {
                return this.$root;
            }
            return this.containerRoot().querySelector(`#${type}-overview`);
        },
        overviewWire(type) {
            return this.wire(this.overviewRoot(type).getAttribute('wire:id'));
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