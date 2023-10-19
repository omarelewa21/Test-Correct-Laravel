import {nameInSidebarEntryForShape} from "./constants.js";

class sidebarComponent {
    constructor(drawingApp, Canvas) {
        this.drawingApp = drawingApp;
        this.Canvas = Canvas;
        this.root = drawingApp.params.root;
    }

    showFirstIcon(element) {
        element.querySelectorAll("svg")[0].style.display = "block";
        element.querySelectorAll("svg")[1].style.display = "none";
    }

    showSecondIcon(element) {
        element.querySelectorAll("svg")[0].style.display = "none";
        element.querySelectorAll("svg")[1].style.display = "block";
    }

}

export class Entry extends sidebarComponent {
    constructor(shape, drawingApp, Canvas) {
        super(drawingApp, Canvas);
        this.svgShape = shape;
        let entryTemplate = this.root.querySelector("#shape-group-template");
        const templateCopy = entryTemplate.content.cloneNode(true);

        this.entryContainer = templateCopy.querySelector(".shape-container");
        this.entryTitle = templateCopy.querySelector(".shape-title");

        this.btns = {
            delete: templateCopy.querySelector(".remove-btn"),
            lock: templateCopy.querySelector(".lock-btn"),
            hide: templateCopy.querySelector(".hide-btn"),
            drag: templateCopy.querySelector(".drag-btn"),
            up: templateCopy.querySelector(".up-btn"),
            down: templateCopy.querySelector(".down-btn")
        };

        this.type = this.svgShape.type === "path" ? "freehand" : this.svgShape.type;
        this.id = `${this.type}-${this.svgShape.shapeId}`;
        this.entryContainer.id = `shape-${this.id}`;
        this.entryTitle.innerText = `${
            this.root.querySelector('#translation-template').dataset[this.svgShape.type]
        } ${this.svgShape.shapeId}`;

        this.drawingApp.bindEventListeners(this.eventListenerSettings, this);
        this.updateLockState();
        this.updateHideState();
        this.customizeButtonsAccordingToType();

        this.deleteModal = this.root.querySelector('#delete-confirm');
    }

    get eventListenerSettings() {
        return [
            {
                element: this.entryContainer,
                events: {
                    "dragstart touchstart": {
                        callback: (evt) => {
                            evt.currentTarget.classList.add("dragging");
                        },
                    },
                    "dragend touchend": {
                        callback: (evt) => {
                            this.updateDraggedElementPosition(evt);
                        },
                    },
                    "mouseenter touchstart": {
                        callback: () => {
                            this.svgShape.highlight();  
                            // this.highlight();
                        },
                    },
                    "mouseleave touchend touchcancel": {
                        callback: () => {
                            this.svgShape.unhighlight();
                            this.unhighlight();
                        },
                    },
                    "click": {
                        callback: () => {
                            this.toggleShapeSelect();
                        },
                    },
                },
            },
            {
                element: this.btns.drag,
                events: {
                    "mouseenter touchstart": {
                        callback: (evt) => {
                            evt.currentTarget.closest('.shape-container').draggable = true;
                        },
                    },
                    'mouseleave touchend': {
                        callback: (evt) => {
                            evt.currentTarget.closest('.shape-container').draggable = false;
                        },
                    }
                },
            },
            {
                element: this.btns.delete,
                events: {
                    "click": {
                        callback: () => {
                            this.showConfirmDelete()
                        },
                    },
                },
            },
            {
                element: this.btns.lock,
                events: {
                    "click": {
                        callback: () => {
                            this.svgShape.toggleLock();
                            this.updateLockState();
                        },
                    },
                },
            },
            {
                element: this.btns.hide,
                events: {
                    "click": {
                        callback: () => {
                            this.handleToggleHide();
                        },
                    },
                },
            },
            {
                element: this.btns.down,
                events: {
                    "click": {
                        callback: (evt) => {
                            const entry = evt.currentTarget.closest('.shape-container');
                            this.updateClickedElementPositionDown(entry);
                            this.reorderHighlight(entry);
                        },
                    },
                },
            },
            {
                element: this.btns.up,
                events: {
                    "click": {
                        callback: (evt) => {
                            const entry = evt.currentTarget.closest('.shape-container')
                            this.updateClickedElementPositionUp(entry);
                            this.reorderHighlight(entry);
                        },
                    },
                },
            },
        ];
    }

    handleToggleHide() {
        this.svgShape.toggleHide();
        this.updateHideState();
    }

    updateClickedElementPositionDown(entry) {

        if (entry.nextElementSibling) {
            this.insertAfter(entry, entry.nextElementSibling);

            let newLayerId = entry.closest(".layer-group").id;
            let newSvgLayer = this.root.querySelector(`#svg-${newLayerId}`);
            let shape = newSvgLayer.querySelector(`#${entry.id.substring(6)}`);
            let shapeToInsertBefore = shape.previousElementSibling
            if (shapeToInsertBefore) {
                newSvgLayer.insertBefore(shape, shapeToInsertBefore);
                return;
            }
            newSvgLayer.appendChild(shape);
        }
    }

    updateClickedElementPositionUp(entry) {

        if (entry.previousElementSibling) {
            entry.parentElement.insertBefore(entry, entry.previousElementSibling);

            let newLayerId = entry.closest(".layer-group").id;
            let newSvgLayer = this.root.querySelector(`#svg-${newLayerId}`);
            let shape = newSvgLayer.querySelector(`#${entry.id.substring(6)}`);
            let shapeToInsertBefore = shape.nextElementSibling
            if (shapeToInsertBefore) {
                this.insertAfter(shape, shapeToInsertBefore);
                return;
            }
            newSvgLayer.appendChild(shape);
        }
    }

    insertAfter(newNode, existingNode) {
        existingNode.parentNode.insertBefore(newNode, existingNode.nextSibling);
    }

    updateDraggedElementPosition(evt) {
        let entry = evt.currentTarget;
        entry.classList.remove("dragging");

        let newLayerId = entry.closest(".layer-group").id;
        let newSvgLayer = this.root.querySelector(`#svg-${newLayerId}`);
        let shape = newSvgLayer.querySelector(`#${entry.id.substring(6)}`);
        let shapeToInsertBefore = newSvgLayer.querySelector(
            `#${entry.nextElementSibling?.id.substring(6)}.shape`
        );
        if (shapeToInsertBefore) {
            return this.insertAfter(shape, shapeToInsertBefore);
        }
        newSvgLayer.prepend(shape);
    }

    reorderHighlight(element) {
        element.classList.add("reorder-highlight");
        let timeout = setTimeout(() => {
            element.classList.remove('reorder-highlight');
            clearTimeout(timeout);
        }, 1000)
    }

    highlight() {
        this.entryTitle.classList.add("highlight");
        this.entryContainer.classList.add("highlight");
    }

    unhighlight() {
        this.entryTitle.classList.remove("highlight");
        this.entryContainer.classList.remove("highlight");
    }

    toggleShapeSelect() {
        const selectedEl = this.getSelectedElement();
        if (selectedEl) this.unselect(selectedEl);
        if (selectedEl === this.entryContainer) return;
        this.select();
    }

    getSelectedElement() {
        return this.entryContainer.parentElement.querySelector('.selected');
    }

    select() {
        this.entryContainer.classList.add('selected');
        this.svgShape.shapeGroup.element.classList.add('selected');
        this.startEditingShape();
    }
    unselect(element) {
        element = element ?? this.getSelectedElement();
        const shapeId = element.id.substring(6);
        element.classList.remove('selected');
        element.closest('#canvas-sidebar-container').querySelector(`#${shapeId}`).classList.remove('selected');
        this.removeAnyEditingShapes();
        document.activeElement.blur();
    }

    updateLockState() {
        if (this.svgShape.isLocked()) {
            this.showSecondIcon(this.btns.lock);
            this.btns.lock.title = this.btns.lock.getAttribute("data-title-locked");
        } else {
            this.showFirstIcon(this.btns.lock);
            this.btns.lock.title = this.btns.lock.getAttribute("data-title-unlocked");
        }
    }

    updateHideState() {
        if (this.svgShape.isHidden()) {
            this.showSecondIcon(this.btns.hide);
            this.btns.hide.style.color = "#929DAF";
            this.btns.hide.title = this.btns.hide.getAttribute("data-title-hidden");
            this.entryContainer.classList.add('hide');
        } else {
            this.showFirstIcon(this.btns.hide);
            this.btns.hide.style.color = "";
            this.btns.hide.title = this.btns.hide.getAttribute("data-title-unhidden");
            this.entryContainer.classList.remove('hide');
        }
    }

    handleEditShape(evt) {
        const selectedEl = this.getSelectedElement();

        if(!selectedEl) return this.startEditingShape();

        if(selectedEl === this.entryContainer && selectedEl.classList.contains('editing')) return;

        this.unselect(selectedEl);
        this.select();
        this.startEditingShape();
        evt.stopPropagation();
    }

    startEditingShape() {
        this.removeAnyEditingShapes();
        this.entryContainer.classList.add('editing');
        this.svgShape.shapeGroup.element.classList.add('editing');
        this.showRelevantShapeMenu();
        this.setInputValuesWhenShapeInEditMode();
    }

    setInputValuesWhenShapeInEditMode() {
        this.svgShape.setInputValuesOnEdit();
    }

    removeAnyEditingShapes() {
        this.root.querySelectorAll('.editing').forEach((element) => {
            element.classList.remove('editing');
        });
    }

    showRelevantShapeMenu() {
        let shapeType = this.svgShape.type;

        if(shapeType === 'image') return;

        if(shapeType === 'path') {
            shapeType = 'freehand';
        }
        document.querySelector(`#add-${shapeType}-btn`).click();
    }

    remove() {
        this.svgShape.remove();
        this.entryContainer.remove();
    }

    disable() {
        try {
            for (const btn of Object.values(this.btns)) {
                btn.disabled = true;
            }
            this.entryContainer.draggable = false;
        } catch (error) {
        }
    }

    enable() {
        for (const btn of Object.values(this.btns)) {
            btn.disabled = false;
        }
        this.entryContainer.draggable = true;
    }

    showConfirmDelete() {
        this.drawingApp.params.deleteSubject = this;
        this.deleteModal.classList.toggle('open');
    }

    customizeButtonsAccordingToType() {
        if (this.type === "image"){
            const editButton = this.btns.edit;
            editButton.style.color = "grey";
            editButton.disabled = true;
        }
    }
}

export class Layer extends sidebarComponent {
    /**
     *
     * @param {Object.<string, boolean|number|string|{}>} props
     * @param drawingApp
     * @param Canvas
     * @param {string} props.name
     * @param {string} props.id
     * @param {boolean} props.enabled
     */
    constructor(props = {}, drawingApp, Canvas) {
        super(drawingApp, Canvas);
        this.params = {
            hidden: false,
            locked: false,
        }
        this.props = props;
        this.svg = this.root.querySelector(`#svg-${props.id}`);
        this.sidebar = this.makeLayerElement();
        drawingApp.bindEventListeners(this.eventListenerSettings, this);
        if (this.props.enabled) {
            this.enable();
        }
        this.shapes = {};
    }

    makeLayerElement() {
        const layerTemplate = this.root.querySelector("#layer-group-template");
        const layersContainer = this.root.querySelector("#layers-container");
        const layersHeaderContainer = this.root.querySelector("#layers-heading");

        const templateCopy = layerTemplate.content.cloneNode(true);
        const layerGroup = templateCopy.querySelector(".layer-group");

        layerGroup.id = this.props.id;

        const headerTitle = templateCopy.querySelector(".header-title");
        headerTitle.innerText = this.props.name;
        headerTitle.setAttribute("selid", `header-${this.props.id}`)
        headerTitle.setAttribute('data-layer', this.props.id);
        headerTitle.closest('.header-container').setAttribute('data-layer', this.props.id);

        this.header = templateCopy.querySelector(".header");
        this.shapesGroup = templateCopy.querySelector(".shapes-group");

        this.btns = {
            delete: templateCopy.querySelector(".remove-btn"),
            lock: templateCopy.querySelector(".lock-btn"),
            hide: templateCopy.querySelector(".hide-btn"),
            addLayer: templateCopy.querySelector(".add-layer-btn")
        };

        this.explainer = templateCopy.querySelector(".explainer")
        this.setCorrectExplainerText();

        if (this.shouldAddLayerHeader()) {
            layersHeaderContainer.append(this.header);
        }
        layersContainer.append(templateCopy);

        return layerGroup;
    }

    get eventListenerSettings() {
        return [
            {
                element: this.btns.addLayer,
                events: {
                    "click": {
                        callback: this.enable,
                        options: {once: true}, 
                    } 
                }
            },
            {
                element: this.btns.hide,
                events: {
                    "click": {
                        callback: () => {
                            if (this.isHidden()) {
                                this.unhide();
                            } else {
                                this.hide();
                            }
                        },
                    }
                }
            },
            {
                element: this.btns.lock,
                events: {
                    "click": {
                        callback: () => {
                            if (this.isLocked()) {
                                this.unlock();
                            } else {
                                this.lock();
                            }
                        },
                    }
                }
            },
            {
                element: this.btns.delete,
                events: {
                    "click": {
                        callback: () => {
                            this.clearSidebar();
                        },
                    },
                }
            },
            {
                element: this.sidebar,
                events: {
                    "dragover touchmove": {
                        callback: (evt) => {
                            evt.preventDefault();
                            if (!this.props.enabled) return;
                            const draggedEntry = this.root.querySelector(".dragging");
                            if (draggedEntry == null) {
                                return;
                            }
                            const oldLayer = draggedEntry.closest(".layer-group");
                            const oldGroupKey = this.Canvas.layerID2Key(oldLayer.id);
                            const newLayer = this.sidebar;
                            const newGroupKey = this.Canvas.layerID2Key(newLayer.id);
                            if (newGroupKey !== oldGroupKey) {
                                const shapeID = draggedEntry.id.substring(6);
                                this.Canvas.layers[newGroupKey].shapes[shapeID] =
                                    this.Canvas.layers[oldGroupKey].shapes[shapeID];
                                // delete Canvas.layers[oldGroupKey].shapes[shapeID];
                            }

                            const entryToInsertBefore = this.getEntryToInsertBefore(this.sidebar, evt.clientY == null ?  evt.touches[0].clientY : evt.clientY).entry;
                            if (entryToInsertBefore == null) {
                                this.shapesGroup.appendChild(draggedEntry);
                                return;
                            }
                            this.shapesGroup.insertBefore(draggedEntry, entryToInsertBefore);
                        },
                    }
                }
            }
        ];
    }

    addEntry(entry) {
        this.hideExplainer();
        this.shapesGroup.insertBefore(entry.entryContainer, this.getTopShape());
    }

    getTopShape() {
        return this.shapesGroup.children[0];
    }

    isEmpty() {
        return Object.keys(this.shapes).length === 0 && this.svg.childElementCount === 0;
    }

    enable() {
        this.convertAddLayerBtnToDummyBtn();
        this.showRegularBtns();
        this.addEventListenerOnHeader();
        this.props.enabled = true;
    }

    convertAddLayerBtnToDummyBtn() {
        this.btns.addLayer.innerHTML = "";
        this.btns.addLayer.disabled = true;
        this.btns.addLayer.classList.add("btn-placeholder");
        this.btns.addLayer.classList.remove("add-layer-btn");
    }

    showRegularBtns() {
        this.btns.delete.style.display = "";
        this.btns.lock.style.display = "";
        this.btns.hide.style.display = "";
    }

    addEventListenerOnHeader() {
        const settings = [
            {
                element: this.header,
                events: {
                    "mousedown touchstart": {
                        callback: (evt) => {
                            const targetHeader = evt.target;
                            const newCurrentLayerID = this.getLayerDataFromTarget(targetHeader);
                            if (newCurrentLayerID) {
                                newCurrentLayerID.contains('question') ? this.Canvas.layers.answer.hide() : this.Canvas.layers.answer.unhideIfHidden();
                                this.Canvas.setCurrentLayer(this.Canvas.layerID2Key(newCurrentLayerID));
                            }
                        }
                    },
                }
            },
        ];
        this.drawingApp.bindEventListeners(settings, this);
    }

    hide() {
        this.svg.style.display = "none";
        this.sidebar.classList.add("hidden");
        this.showSecondIcon(this.btns.hide);
        this.btns.hide.title = this.btns.hide.getAttribute("data-title-hidden");
        this.params.hidden = true;
    }

    unhide() {
        this.svg.style.display = "";
        this.sidebar.classList.remove("hidden");
        this.showFirstIcon(this.btns.hide);
        this.btns.hide.title = this.btns.hide.getAttribute("data-title-unhidden");
        this.params.hidden = false;
    }

    isHidden() {
        return this.params.hidden;
    }

    lock() {
        this.svg.style.setProperty("--cursor-type-locked", "default");
        this.svg.style.setProperty("--cursor-type-draggable", "default");
        this.sidebar.classList.add("locked");
        for (const shape of Object.values(this.shapes)) {
            shape.sidebar.disable();
        }
        this.showSecondIcon(this.btns.lock);
        this.btns.lock.title = this.btns.lock.getAttribute("data-title-locked");
        this.params.locked = true;
    }

    unlock() {
        this.svg.style.removeProperty("--cursor-type-locked");
        this.svg.style.removeProperty("--cursor-type-draggable");
        this.sidebar.classList.remove("locked");
        for (const shape of Object.values(this.shapes)) {
            shape.sidebar.enable();
        }
        this.showFirstIcon(this.btns.lock);
        this.btns.lock.title = this.btns.lock.getAttribute("data-title-unlocked");
        this.params.locked = false;
    }

    isLocked() {
        return this.params.locked;
    }

    getEntryToInsertBefore(container, y) {
        const draggableEntriesWithoutDraggedEntry = [
            ...container.querySelectorAll("[draggable]:not(.dragging)"),
        ];
        return draggableEntriesWithoutDraggedEntry.reduce(
            (closestEntry, currentEntry) => {
                let box = currentEntry.getBoundingClientRect();
                let offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closestEntry.offset) {
                    return {offset, entry: currentEntry};
                }
                return closestEntry;
            },
            {offset: Number.NEGATIVE_INFINITY}
        );
    }

    unhideIfHidden() {
        if (this.isHidden()) {
            this.unhide();
        }
    }

    clearSidebar(withWarning = true) {
        if (this.isEmpty()) return;
        if (withWarning) {
            if (!confirm(`Alle vormen op deze laag (${this.props.name}) verwijderen?`)) return;
        }
        Object.values(this.shapes).forEach((shape) => {
            try {
                shape.sidebar.remove();
            } catch (error) {
                return;
            }
        });
        this.shapes = {};
        this.svg.innerHTML = '';
        this.shapesGroup.querySelectorAll('.shape-container').forEach((shape) => shape.remove());
    }

    getLayerDataFromTarget(element) {
        if (element.dataset.layer) return element.dataset.layer;
        if (element.querySelector('[data-layer]')) return element.querySelector('[data-layer]').dataset.layer
        return false;
    }

    hideExplainer() {
        this.explainer.style.display = 'none';
    }

    setCorrectExplainerText() {
        if(this.drawingApp.params.isPreview) {
            this.hideExplainer();
            return;
        }
        let group = this.props.id.replace('-group', '');

        this.explainer.innerText = this.explainer.dataset[`text${group.capitalize()}`];
    }

    shouldAddLayerHeader() {
        return !(this.props.id.contains('question') && !this.drawingApp.isTeacher())
    }
}