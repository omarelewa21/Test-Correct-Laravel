import { validSvgElementKeys, pixelsPerCentimeter } from "./constants.js";
import * as svgElement from "./svgElement.js";
import { htmlElement } from "./htmlElement.js";

/**
 * @typedef propObj
 * @type {Object.<string, string|number>}
 *
 */

class svgShape {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {string} type The type of shape to be made.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {?SVGElement} parent The parent the shape should be appended to.
     */
    constructor(shapeId, type, props, parent, withHelperElements = true, withHighlightEvents = true) {
        this.shapeId = shapeId;
        this.type = type;
        this.props = props ?? {
            main: { class: "main" },
            group: { class: "shape draggable", id: `${type}-${shapeId}` },
        };
        if (!this.props.main) this.props.main = {};
        if (!this.props.group) this.props.group = {};
        this.offset = parseInt(this.props.main["stroke-width"]) / 2 + 3 || 5;
        this.parent = parent;
        //construct shape group
        this.props.group.class = "shape draggable";
        this.props.group.id = `${this.type}-${this.shapeId}`;
        this.shapeGroup = new svgElement.Group(this.props.group);
        // construct main element
        this.props.main["class"] = "main";
        this.mainElement = this.makeMainElementOfRightType();
        //append main element to shape
        this.shapeGroup.appendChild(this.mainElement.element);
        //append shape to parent
        this.parent.appendChild(this.shapeGroup.element);
        if (withHelperElements) {
            this.borderElement = this.makeBorderElement();
            this.shapeGroup.appendChild(this.borderElement.element);
            this.cornerElements = this.makeCornerElements();
            this.cornerElements.forEach((cornerElement) => {
                this.shapeGroup.appendChild(cornerElement.element);
            });
            this.hideHelperElements();
        }
        this.withHighlightEvents = withHighlightEvents;
    }
    makeMainElementOfRightType() {
        switch (this.type) {
            case "rect":
                return new svgElement.Rectangle(this.props.main);
            case "circle":
                return new svgElement.Circle(this.props.main);
            case "line":
                return new svgElement.Line(this.props.main);
            case "text":
                return new svgElement.Text(this.props.main);
            case "image":
                return new svgElement.Image(this.props.main);
            case "path":
                return new svgElement.Path(this.props.main);
        }
    }
    makeCornerElements() {
        let bbox = this.mainElement.getBoundingBox();
        return [
            new svgElement.Circle({
                "class": "corner left-top",
                "cx": bbox.x - this.offset,
                "cy": bbox.y - this.offset,
                "r": "8px",
                "stroke": "var(--teacher-Primary)",
                "stroke-width": "2",
                "fill": "var(--all-OffWhite)",
            }),
            new svgElement.Circle({
                "class": "corner left-bottom",
                "cx": bbox.x - this.offset,
                "cy": bbox.y + bbox.height + this.offset,
                "r": "8px",
                "stroke": "var(--teacher-Primary)",
                "stroke-width": "2",
                "fill": "var(--all-OffWhite)",
            }),
            new svgElement.Circle({
                "class": "corner right-bottom",
                "cx": bbox.x + bbox.width + this.offset,
                "cy": bbox.y + bbox.height + this.offset,
                "r": "8px",
                "stroke": "var(--teacher-Primary)",
                "stroke-width": "2",
                "fill": "var(--all-OffWhite)",
            }),
            new svgElement.Circle({
                "class": "corner right-top",
                "cx": bbox.x + bbox.width + this.offset,
                "cy": bbox.y - this.offset,
                "r": "8px",
                "stroke": "var(--teacher-Primary)",
                "stroke-width": "2",
                "fill": "var(--all-OffWhite)",
            }),
        ];
    }
    makeBorderElement() {
        let bbox = this.mainElement.getBoundingBox();
        return new svgElement.Rectangle({
            "class": "border",
            "x": bbox.x - this.offset,
            "y": bbox.y - this.offset,
            "width": bbox.width + this.offset * 2,
            "height": bbox.height + this.offset * 2,
            "stroke": "var(--teacher-Primary)",
            "stroke-width": "3",
            "stroke-dasharray": "10",
            "fill": "red",
            "fill-opacity": "0",
        });
    }
    updateCornerElements() {
        let bbox = this.mainElement.getBoundingBox();
        this.cornerElements[0].setCXAttribute(bbox.x - this.offset);
        this.cornerElements[0].setCYAttribute(bbox.y - this.offset);
        this.cornerElements[1].setCXAttribute(bbox.x - this.offset);
        this.cornerElements[1].setCYAttribute(
            bbox.y + bbox.height + this.offset
        );
        this.cornerElements[2].setCXAttribute(
            bbox.x + bbox.width + this.offset
        );
        this.cornerElements[2].setCYAttribute(
            bbox.y + bbox.height + this.offset
        );
        this.cornerElements[3].setCXAttribute(
            bbox.x + bbox.width + this.offset
        );
        this.cornerElements[3].setCYAttribute(bbox.y - this.offset);
    }
    updateBorderElement() {
        let bbox = this.mainElement.getBoundingBox();
        this.borderElement.setXAttribute(bbox.x - this.offset);
        this.borderElement.setYAttribute(bbox.y - this.offset);
        this.borderElement.setWidthAttribute(bbox.width + this.offset * 2);
        this.borderElement.setHeightAttribute(bbox.height + this.offset * 2);
    }
    showHelperElements() {
        this.showBorderElement();
        this.showCornerElements();
    }
    showBorderElement() {
        this.borderElement.setAttribute("stroke", this.borderElement.props.stroke);
    }
    showCornerElements() {
        this.cornerElements.forEach((cornerElement) => {
            cornerElement.show();
        });
    }
    hideHelperElements() {
        this.hideBorderElement();
        this.hideCornerElements();
    }
    hideBorderElement() {
        this.borderElement.setAttribute("stroke", "none");
    }
    hideCornerElements() {
        this.cornerElements.forEach((cornerElement) => {
            cornerElement.hide();
        });
    }
    toggleLock() {
        this.shapeGroup.element.classList.toggle("draggable");
        this.shapeGroup.element.classList.toggle("locked");
    }
    isLocked() {
        return !this.shapeGroup.element.classList.contains("draggable");
    }
    toggleHide() {
        if (this.isHidden()) {
            this.shapeGroup.show();
        } else {
            this.shapeGroup.hide();
        }
    }
    isHidden() {
        return this.shapeGroup.element.style.display == "none";
    }
    remove() {
        this.shapeGroup.remove();
        this.marker?.remove();
        delete this;
    }
    getSidebarEntry() {
        return this.sidebarEntry;
    }
    setSidebarEntry(entry) {
        this.sidebarEntry = entry;
    }
    cancelConstruction() {
        this.getSidebarEntry().remove();
    }
    onDrawStart(evt, cursor) {
        this.mainElement.onDrawStart?.(evt, cursor);

        this.onDrawStartShapeSpecific?.(evt, cursor);
    }
    onDraw(evt, cursor) {
        this.mainElement.onDraw?.(evt, cursor);
        this.onDrawShapeSpecific?.(evt, cursor);

        this.updateBorderElement();
        this.updateCornerElements();
    }
    onDrawEnd(evt, cursor) {
        this.mainElement.onDrawEnd?.(evt, cursor);
        this.onDrawEndShapeSpecific?.(evt, cursor);

        this.updateBorderElement();
        this.updateCornerElements();
        this.showBorderElement();
    }
    addHighlightEvents() {
        if(!this.withHighlightEvents) return;
        this.updateBorderElement();
        this.updateCornerElements();
        const settings = [
            {
                element: this.shapeGroup,
                events: {
                    "mouseenter touchstart": {
                        callback: () => {
                            this.highlight();
                            this.getSidebarEntry().highlight();
                        }
                    },
                    "mouseleave": {
                        callback: () => {
                            this.unhighlight();
                            this.getSidebarEntry().unhighlight();
                        }
                    },
                    "click": {
                        callback: () => {
                            this.highlight();
                            Canvas.setFocusedShape(this);
                        }
                    }
                }
            }
        ];
        drawingApp.bindEventListeners(settings, this);
    }
    highlight() {
        this.showBorderElement();
    }
    unhighlight() {
        this.hideBorderElement();
    }
}

export class Rectangle extends svgShape {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {?SVGElement} parent The parent the shape should be appended to.
     */
    constructor(shapeId, props, parent = null, withHelperElements, withHighlightEvents) {
        super(shapeId, "rect", props, parent, withHelperElements, withHighlightEvents);
    }
}

export class Circle extends svgShape {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {?SVGElement} parent The parent the shape should be appended to.
     */
    constructor(shapeId, props, parent = null, withHelperElements, withHighlightEvents) {
        super(shapeId, "circle", props, parent, withHelperElements, withHighlightEvents);
    }
}

export class Line extends svgShape {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {?SVGElement} parent The parent the shape should be appended to.
     */
    constructor(shapeId, props, parent = null, withHelperElements, withHighlightEvents) {
        super(shapeId, "line", props, parent, withHelperElements, withHighlightEvents);

        this.makeOwnMarkerForThisShape();
    }
    makeOwnMarkerForThisShape() {
        const markerType = this.getMarkerType();
        if(markerType === "no-endmarker") return;

        const newMarker = this.cloneGenericMarker(markerType);
        UI.svgCanvas.firstElementChild.appendChild(newMarker);

        const newMarkerId = `${newMarker.id}-line-${this.shapeId}`;
        newMarker.id = newMarkerId;
        this.props.main["marker-end"] = `url(#${newMarkerId})`;
        this.mainElement.setAttributeOnElementWithValidation(
            "marker-end",
            `url(#${newMarkerId})`
        );

        const propertyToChange = this.getPropertyToChange(markerType);
        newMarker.style[propertyToChange] = this.props.main.stroke;

        this.marker = newMarker;
    }
    getMarkerType() {
        const type = this.props.main["marker-end"];
        return type.substring(type.indexOf("svg-")+4, type.lastIndexOf("-line"));
    }
    cloneGenericMarker(type) {
        const markerToClone = document.querySelector(`marker#svg-${type}`);
        return markerToClone.cloneNode(true);
    }
    getPropertyToChange(type) {
        switch(type) {
            case "filled-arrow":
            case "filled-dot":
                return "fill";
            case "two-lines-arrow":
                return "stroke";
        }
    }
}

export class Text extends svgShape {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {?SVGElement} parent The parent the shape should be appended to.
     */
    constructor(shapeId, props, parent = null, withHelperElements, withHighlightEvents) {
        super(shapeId, "text", props, parent, withHelperElements, withHighlightEvents);
        this.mainElement.setTextContent(this.props.main["data-textcontent"]);
    }
    onDrawEndShapeSpecific(evt, cursor) {
        const windowCursor = drawingApp.convertCanvas2DomCoordinates(cursor);

        let canvasContainer = document.getElementById("svg-canvas").parentElement;
        const fontSize = parseFloat(this.mainElement.element.style.fontSize);

        let textInput = new htmlElement("input", canvasContainer, {
            id: "add-text-input",
            type: "text",
            placeholder: "Type here...",
            style:
                `width: ${canvasContainer.getBoundingClientRect().right - windowCursor.x}px;\
                position: absolute;\
                top: ${windowCursor.y - fontSize}px;\
                left: ${windowCursor.x - 2}px;\
                font-size: ${fontSize}px;\
                color: ${this.mainElement.getAttribute("fill")};\
                font-weight: ${this.mainElement.element.style.fontWeight || "normal"};`,
            autocomplete: "off",
            spellcheck: "false",
        });
        textInput.focus();

        textInput.addEventListener("blur", () => {
            const text = textInput.element.value;
            textInput.deleteElement();
            if (text.length === 0) {
                this.cancelConstruction();
                return;
            }
            this.mainElement.setTextContent(text);
            this.updateBorderElement();
            this.updateCornerElements();
        });
    }
}

export class Image extends svgShape {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {?SVGElement} parent The parent the shape should be appended to.
     */
    constructor(shapeId, props, parent = null, withHelperElements, withHighlightEvents) {
        super(shapeId, "image", props, parent, withHelperElements, withHighlightEvents);
    }
}

export class Path extends svgShape {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {?SVGElement} parent The parent the shape should be appended to.
     */
    constructor(shapeId, props, parent = null, withHelperElements, withHighlightEvents) {
        super(shapeId, "path", props, parent, withHelperElements, withHighlightEvents);
    }
}

export class Grid extends Path {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {HTMLElement} parent The parent the shape should be appended to.
     */
    constructor(shapeId, props, parent) {
        super(shapeId, props, parent, false);
        this.origin = new svgElement.Path(this.props.origin);
        this.setDAttributes(
            this.calculateDAttributeForGrid(this.props.size),
            this.calculateDAttributeForOrigin(this.props.size)
        );
        this.shapeGroup.element.appendChild(this.origin.element);
        this.shapeGroup.element.classList.remove("draggable");
        this.shapeGroup.setAttribute("id", `grid`);
    }
    show() {
        this.shapeGroup.show();
    }
    hide() {
        this.shapeGroup.hide();
    }
    setDAttributes(dGrid, dOrigin) {
        this.mainElement.setAttribute("d", dGrid);
        this.origin.setAttribute("d", dOrigin);
    }
    update() {
        const size = drawingApp.params.gridSize;
        this.setDAttributes(
            this.calculateDAttributeForGrid(size),
            this.calculateDAttributeForOrigin(size)
        );
    }
    calculateDAttributeForGrid(size) {
        let bounds = Canvas.params.bounds;
        if(Object.keys(bounds).length === 0) {
            bounds = calculatePreviewBounds();
        }
        const interval = size * pixelsPerCentimeter,
            lineAmount = this.calculateAmountOfGridLines(interval, bounds);
        let strOfPoints = ``;
        for (var i = -lineAmount.left; i <= lineAmount.right; i++) {
            strOfPoints += `M${interval * i},${bounds.top}v${bounds.height} `;
        }
        for (var j = -lineAmount.top; j <= lineAmount.bottom; j++) {
            strOfPoints += `M${bounds.left},${interval * j}h${bounds.width} `;
        }
        return strOfPoints;
    }
    calculateDAttributeForOrigin(size) {
        const spokeLength = size * pixelsPerCentimeter / 2;
        return `M-${spokeLength},${0}l${spokeLength * 2
        },0m-${spokeLength},-${spokeLength}l0,${spokeLength * 2}`
    }
    calculateAmountOfGridLines(interval, bounds) {
        return {
            left: Math.trunc(Math.abs(bounds.left) / (interval)),
            right: Math.trunc(Math.abs(bounds.right) / (interval)),
            top: Math.trunc(Math.abs(bounds.top) / (interval)),
            bottom: Math.trunc(Math.abs(bounds.bottom) / (interval)),
        };
    }
}

export class Freehand extends Path {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {?SVGElement} parent The parent the shape should be appended to.
     */
    constructor(shapeId, props, parent, withHelperElements, withHighlightEvents) {
        super(shapeId, props, parent, withHelperElements, withHighlightEvents);
        this.shapeGroup.setAttribute("id", `freehand-${shapeId}`);
    }
    onDrawShapeSpecific(evt, cursor) {
        let path = this.mainElement.getDAttribute();
        this.mainElement.setDAttribute(`${path} L ${cursor.x},${cursor.y}`);
    }
}