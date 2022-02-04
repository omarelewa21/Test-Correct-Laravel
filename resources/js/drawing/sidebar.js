import {nameInSidebarEntryForShape} from "./constants.js";

class sidebarComponent {
    constructor(drawingApp) {
        this.drawingApp = drawingApp;
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
    constructor(shape, drawingApp) {
        super(drawingApp);
        this.svgShape = shape;
        let entryTemplate = document.getElementById("shape-group-template");
        const templateCopy = entryTemplate.content.cloneNode(true);

        this.entryContainer = templateCopy.querySelector(".shape-container");
        this.entryTitle = templateCopy.querySelector(".shape-title");

        this.btns = {
            delete: templateCopy.querySelector(".remove-btn"),
            lock: templateCopy.querySelector(".lock-btn"),
            hide: templateCopy.querySelector(".hide-btn"),
            drag: templateCopy.querySelector(".drag-btn")
        };

        this.type = this.svgShape.type === "path" ? "freehand" : this.svgShape.type;
        this.id = `${this.type}-${this.svgShape.shapeId}`;
        this.entryContainer.id = `shape-${this.id}`;
        this.entryTitle.innerText = `${
            nameInSidebarEntryForShape[this.svgShape.type]
        } ${this.svgShape.shapeId}`;

        this.drawingApp.bindEventListeners(this.eventListenerSettings, this);
        this.updateLockState();
        this.updateHideState();
    }

    get eventListenerSettings() {
        return [
            {
                element: this.entryContainer,
                events: {
                    "dragstart": {
                        callback: (evt) => {
                            evt.currentTarget.classList.add("dragging");
                        },
                    },
                    "dragend": {
                        callback: (evt) => {
                            let entry = evt.currentTarget;
                            entry.classList.remove("dragging");

                            let newLayerId = entry.closest(".layer-group").id;
                            let newSvgLayer = document.getElementById(`svg-${newLayerId}`);
                            let shape = document.getElementById(entry.id.substring(6));
                            let shapeToInsertBefore = document.getElementById(
                                evt.currentTarget.nextElementSibling?.id.substring(6)
                            );
                            if (shapeToInsertBefore) {
                                newSvgLayer.insertBefore(shape, shapeToInsertBefore);
                                return;
                            }
                            newSvgLayer.appendChild(shape);
                        },
                    },
                    "mouseenter touchstart": {
                        callback: () => {
                            this.svgShape.highlight();
                            this.highlight();
                        },
                    },
                    "mouseleave touchend touchcancel": {
                        callback: () => {
                            this.svgShape.unhighlight();
                            this.unhighlight();
                        },
                    },
                },
            },
            {
                element: this.btns.delete,
                events: {
                    "click": {
                        callback: () => {
                            this.remove();
                            // delete this;
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
                            this.svgShape.toggleHide();
                            this.updateHideState();
                        },
                    },
                },
            },
        ];
    }

    highlight() {
        this.entryTitle.classList.add("highlight");
    }

    unhighlight() {
        this.entryTitle.classList.remove("highlight");
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
        } else {
            this.showFirstIcon(this.btns.hide);
            this.btns.hide.style.color = "";
            this.btns.hide.title = this.btns.hide.getAttribute("data-title-unhidden");
        }
    }

    remove() {
        this.svgShape.remove();
        this.entryContainer.remove();

    }

    disable() {
        for (const btn of Object.values(this.btns)) {
            btn.disabled = true;
        }
        this.entryContainer.draggable = false;
    }

    enable() {
        for (const btn of Object.values(this.btns)) {
            btn.disabled = false;
        }
        this.entryContainer.draggable = true;
    }
}

export class Layer extends sidebarComponent {
    /**
     *
     * @param {Object.<string, boolean|number|string|{}>} props
     * @param {string} props.name
     * @param {string} props.id
     * @param {boolean} props.enabled
     */
    constructor(props = {}, drawingApp) {
        super(drawingApp);
        this.params = {
            hidden: false,
            locked: false,
        }
        this.props = props;
        this.svg = document.getElementById(`svg-${props.id}`);
        this.sidebar = this.makeLayerElement();
        drawingApp.bindEventListeners(this.eventListenerSettings, this);
        if (this.props.enabled) {
            this.enable();
        }
        this.shapes = {};
    }

    makeLayerElement() {
        const layerTemplate = document.getElementById("layer-group-template"),
            layersContainer = document.getElementById("layers-container");
        const templateCopy = layerTemplate.content.cloneNode(true);
        const layerGroup = templateCopy.querySelector(".layer-group");
        layerGroup.id = this.props.id;
        const headerTitle = templateCopy.querySelector(".header-title");
        headerTitle.innerText = this.props.name;

        this.header = templateCopy.querySelector(".header");
        this.shapesGroup = templateCopy.querySelector(".shapes-group");

        this.btns = {
            delete: templateCopy.querySelector(".remove-btn"),
            lock: templateCopy.querySelector(".lock-btn"),
            hide: templateCopy.querySelector(".hide-btn"),
            addLayer: templateCopy.querySelector(".add-layer-btn")
        };

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
                            if (this.isEmpty()) return;
                            if (!confirm(`Alle vormen op deze laag (${this.props.name}) verwijderen?`)) return;
                            Object.values(this.shapes).forEach((shape) => {
                                shape.sidebar.remove();
                            });
                        },
                    },
                }
            },
            {
                element: this.sidebar,
                events: {
                    "dragover": {
                        callback: (evt) => {
                            evt.preventDefault();
                            if (!this.props.enabled) return;
                            const draggedEntry = document.querySelector(".dragging");
                            if (draggedEntry == null) {
                                return;
                            }
                            const oldLayer = draggedEntry.closest(".layer-group");
                            const oldGroupKey = Canvas.layerID2Key(oldLayer.id);
                            const newLayer = this.sidebar;
                            const newGroupKey = Canvas.layerID2Key(newLayer.id);
                            if (newGroupKey !== oldGroupKey) {
                                const shapeID = draggedEntry.id.substring(6);
                                Canvas.layers[newGroupKey].shapes[shapeID] =
                                    Canvas.layers[oldGroupKey].shapes[shapeID];
                                // delete Canvas.layers[oldGroupKey].shapes[shapeID];
                            }

                            const entryToInsertBefore = this.getEntryToInsertBefore(this.sidebar, evt.clientY).entry;
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
        this.shapesGroup.insertBefore(entry.entryContainer, this.getTopShape());
    }

    getTopShape() {
        return this.shapesGroup.children[0];
    }

    isEmpty() {
        return Object.keys(this.shapes).length === 0;
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
                            const newCurrentLayerID = targetHeader.closest(".layer-group").id;

                            Canvas.setCurrentLayer(Canvas.layerID2Key(newCurrentLayerID));
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
        if(this.isHidden()) {
            this.unhide();
        }
    }
}