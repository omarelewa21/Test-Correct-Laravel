import {pixelsPerCentimeter, elementClassNameForType, resizableSvgShapes} from "./constants.js";
import * as svgElement from "./svgElement.js";
import {htmlElement} from "./htmlElement.js";

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
     * @param drawingApp
     * @param Canvas
     * @param withHelperElements
     * @param withHighlightEvents
     */
    constructor(shapeId, type, props, parent, drawingApp, Canvas, withHelperElements = true, withHighlightEvents = true) {
        this.shapeId = shapeId;
        this.type = type;
        this.props = props ?? {
            main: {class: "main"},
            group: {class: "shape draggable", id: `${type}-${shapeId}`},
        };
        this.Canvas = Canvas;
        this.drawingApp = drawingApp;
        this.root = drawingApp.params.root;
        if (!this.props.main) this.props.main = {};
        if (!this.props.group) this.props.group = {};
        this.offset = parseInt(this.props.main["stroke-width"]) / 2 + 3 || 5;
        this.parent = parent;
        //construct shape group
        this.props.group.class = "shape draggable";
        if(this.shapeShouldBeResizable()) this.props.group.class += " resizable";
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
        this.UI = Canvas?.UI;
    }

    makeMainElementOfRightType() {
        try {
            const classToMake = elementClassNameForType[this.type];
            return new svgElement[classToMake](this.props.main);
        } catch (e) {
            console.error(`Type ${this.type} is not a known element type. Skipped creating element.`);
        }
    }

    makeCornerElements() {
        let bbox = this.mainElement.getBoundingBox();
        return [
            new svgElement.Circle({
                "class": "corner side-nw",
                "cx": bbox.x - this.offset,
                "cy": bbox.y - this.offset,
                "r": "8px",
                "stroke": "var(--teacher-Primary)",
                "stroke-width": "2",
                "fill": "var(--all-OffWhite)",
            }),
            new svgElement.Circle({
                "class": "corner side-sw",
                "cx": bbox.x - this.offset,
                "cy": bbox.y + bbox.height + this.offset,
                "r": "8px",
                "stroke": "var(--teacher-Primary)",
                "stroke-width": "2",
                "fill": "var(--all-OffWhite)",
            }),
            new svgElement.Circle({
                "class": "corner side-se",
                "cx": bbox.x + bbox.width + this.offset,
                "cy": bbox.y + bbox.height + this.offset,
                "r": "8px",
                "stroke": "var(--teacher-Primary)",
                "stroke-width": "2",
                "fill": "var(--all-OffWhite)",
            }),
            new svgElement.Circle({
                "class": "corner side-ne",
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
        const borderColor = (this.isQuestionLayer() && this.drawingApp.isTeacher()) ? '--purple-mid-dark' : '--primary';
        return new svgElement.Rectangle({
            "class": "border",
            "x": bbox.x - this.offset,
            "y": bbox.y - this.offset,
            "width": bbox.width + this.offset * 2,
            "height": bbox.height + this.offset * 2,
            "stroke": `var(${borderColor})`,
            "stroke-width": "3",
            "stroke-dasharray": "10",
            "fill": "red",
            "fill-opacity": "0",
        });
    }

    shapeShouldBeResizable() {
        return resizableSvgShapes.includes(this.type);
    }

    isQuestionLayer() {
        return this.Canvas.layerID2Key(this.parent.id) === 'question';
    }

    updateHelperElements() {
        this.updateBorderElement();
        this.updateCornerElements();
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
        if (this.elementBelongsToCurrentLayer() && this.drawingApp.currentToolIs('drag')) {
            this.borderElement.setAttribute("stroke", this.borderElement.props.stroke);
            this.borderElement.setAttribute("stroke-dasharray", '4,5');
            this.borderElement.setAttribute("opacity", '.5');
        }
    }

    elementBelongsToCurrentLayer() {
        return this.parent.id.includes(this.Canvas.params.currentLayer);
    }

    showCornerElements() {
        if (this.elementBelongsToCurrentLayer() && this.drawingApp.currentToolIs('drag')) {
            this.cornerElements.forEach((cornerElement) => {
                cornerElement.show();
            });
        }
    }


    hideHelperElements() {
        this.hideBorderElement();
        this.hideCornerElements();
    }

    hideBorderElement() {
        this.borderElement.setAttribute("stroke", "none");
        this.borderElement.setAttribute("opacity", '');
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
        return this.shapeGroup.element.style.display === "none";
    }

    remove() {
        this.shapeGroup.remove();
        this.marker?.remove();
        if (this.parent.childElementCount === 0) this.showExplainerForLayer();
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

        this.updateHelperElements();
    }

    onDrawEnd(evt, cursor) {
        this.onDrawEndShapeSpecific?.(evt, cursor);

        this.updateHelperElements();
        this.showBorderElement();
        this.addHighlightEvents();
    }

    onDragStart(evt, cursor) {
        this.shapeGroup.element.classList.add("dragging");
        this.shapeGroup.element.parentElement.classList.add("child-dragging");
        this.mainElement.onDragStart?.(evt, cursor);
    }

    onDrag(evt, cursor) {
        this.mainElement.onDrag(evt, cursor);
        this.updateHelperElements();
    }

    onDragEnd() {
        this.shapeGroup.element.classList.remove("dragging");
        this.shapeGroup.element.parentElement.classList.remove("child-dragging");
    }

    onResizeStart(evt, cursor) {
        this.mainElement.onResizeStart(evt, cursor);
    }

    onResize(evt, cursor) {
        this.mainElement.onResize(evt, cursor);

        this.updateHelperElements();
    }

    addHighlightEvents() {
        if (!this.withHighlightEvents) return;
        this.updateHelperElements();
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
                    "mouseleave touchend": {
                        callback: () => {
                            this.unhighlight();
                            this.getSidebarEntry().unhighlight();
                        }
                    },
                    "click touchstart": {
                        callback: (evt) => {
                            if (evt.isTrusted === false) return;
                            this.highlight();
                            this.Canvas.setFocusedShape(this);
                        }
                    }
                }
            }
        ];
        this.drawingApp.bindEventListeners(settings, this);
    }

    getElemBoundaries() {
        return this.mainElement.getBoundingBox();
    }

    highlight() {
        this.showBorderElement();
        this.showCornerElements();
    }

    unhighlight() {
        this.hideBorderElement();
        this.hideCornerElements();
    }

    showExplainerForLayer() {
        this.sidebarEntry.entryContainer.parentElement.querySelector('.explainer').style.display = 'inline-block';
    }

    updateFillColor() {
        this.mainElement.setAttribute("fill", this.UI.fillColor.value);
    }

    updateOpacity() {
        const opacity = parseFloat(this.UI.fillOpacityNumber.value / 100);
        this.mainElement.setAttribute("opacity", opacity);
        this.mainElement.setAttribute("fill-opacity", opacity);
    }

    updateStrokeColor() {
        this.mainElement.setAttribute("stroke", this.UI.strokeColor.value);
    }

    updateLineColor() {
        this.mainElement.setAttribute("stroke", this.UI.lineColor.value);
    }

    updateStrokeWidth() {
        this.mainElement.setAttribute("stroke-width", this.UI.strokeWidth.value);
    }

    updateLineWidth() {
        this.mainElement.setAttribute("stroke-width", this.UI.lineWidth.value);
    }
}

export class Rectangle extends svgShape {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {?SVGElement} parent The parent the shape should be appended to.
     * @param drawingApp
     * @param Canvas
     * @param withHelperElements
     * @param withHighlightEvents
     */
    constructor(shapeId, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents) {
        super(shapeId, "rect", props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents);
    }

    static getMainElementAttributes(cursorPosition, UI) {
        return {
            "x": cursorPosition.x,
            "y": cursorPosition.y,
            "width": 0,
            "height": 0,
            "fill":
                UI.fillOpacityNumberRect.value === 0 ? "none" : UI.fillColorRect.value,
            "fill-opacity": parseFloat(UI.fillOpacityNumberRect.value / 100),
            "stroke": UI.strokeColorRect.value,
            "stroke-width": UI.strokeWidthRect.value,
            "opacity": parseFloat(UI.fillOpacityNumberRect.value / 100),
        };
    }
}

export class Circle extends svgShape {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {?SVGElement} parent The parent the shape should be appended to.
     * @param drawingApp
     * @param Canvas
     * @param withHelperElements
     * @param withHighlightEvents
     */
    constructor(shapeId, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents) {
        super(shapeId, "circle", props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents);
    }
}

export class Line extends svgShape {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {?SVGElement} parent The parent the shape should be appended to.
     * @param drawingApp
     * @param Canvas
     * @param withHelperElements
     * @param withHighlightEvents
     */
    constructor(shapeId, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents) {
        super(shapeId, "line", props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents);
        this.svgCanvas = drawingApp.params.root.querySelector('#svg-canvas');
        this.makeOwnMarkerForThisShape();
    }

    makeOwnMarkerForThisShape() {
        const markerType = this.getMarkerType();
        if (markerType === "no-endmarker") return;

        const newMarker = this.cloneGenericMarker(markerType);

        this.svgCanvas.firstElementChild.appendChild(newMarker);

        const newMarkerId = `${newMarker.id}-line-${this.shapeId}`;
        newMarker.id = newMarkerId;
        this.props.main["marker-end"] = `url(#${newMarkerId})`;
        this.mainElement.setAttributeOnElementWithValidation(
            "marker-end",
            `url(#${newMarkerId})`
        );

        const propertyToChange = this.getPropertyToChange(markerType);
        newMarker.style[propertyToChange] = this.props.main.stroke;
        this.parent.appendChild(newMarker);
        this.marker = newMarker;
    }

    getMarkerType() {
        const type = this.props.main["marker-end"];
        return type.substring(type.indexOf("svg-") + 4, type.lastIndexOf("-line"));
    }

    cloneGenericMarker(type) {
        const markerToClone = this.root.querySelector(`marker#svg-${type}`);
        return markerToClone.cloneNode(true);
    }

    getPropertyToChange(type) {
        switch (type) {
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
     * @param drawingApp
     * @param Canvas
     * @param withHelperElements
     * @param withHighlightEvents
     */
    constructor(shapeId, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents) {
        super(shapeId, "text", props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents);
        this.mainElement.setTextContent(this.props.main["data-textcontent"]);
        this.mainElement.setFontFamily('Nunito');
        this.registerEditingEvents();
    }

    updateTextColor() {
        this.mainElement.setAttribute("fill", this.UI.textColor.value);
    }

    updateBoldText() {
        this.mainElement.element.style.fontWeight = this.drawingApp.params.boldText ? 'bold' : 'normal';
        this.updateHelperElements();
    }

    updateTextSize() {
        this.mainElement.element.style.fontSize = `${this.UI.textSize.value / 16}rem`;
        this.updateHelperElements();
    }

    updateOpacity() {
        this.mainElement.setAttribute("opacity", parseFloat(this.UI.elemOpacityNumber.value / 100));
    }

    onDrawEndShapeSpecific(evt, cursor) {
        const windowCursor = this.drawingApp.convertCanvas2DomCoordinates(cursor);

        let canvasContainer = this.root.querySelector("#svg-canvas").parentElement;
        const fontSize = parseFloat(this.mainElement.element.style.fontSize);
        const topOffset = fontSize * parseFloat(getComputedStyle(document.documentElement).fontSize)
        let textInput = new htmlElement("input", canvasContainer, {
            id: "add-text-input",
            type: "text",
            placeholder: "Type here...",
            style:
                `width: ${canvasContainer.getBoundingClientRect().right - windowCursor.x}px;\
                position: absolute;\
                top: ${windowCursor.y - topOffset}px;\
                left: ${windowCursor.x - 2}px;\
                font-size: ${fontSize}rem;\
                color: ${this.mainElement.getAttribute("fill")};\
                font-weight: ${this.mainElement.element.style.fontWeight || "normal"};\
                transform-origin: bottom left;\
                transform: scale(${this.Canvas.params.zoomFactor})`,
            autocomplete: "off",
            spellcheck: "false",
        });
        textInput.focus();

        textInput.addEventListener("focusout", () => {
            const text = textInput.element.value;
            textInput.deleteElement();
            textInput.element.style.display = 'none';
            if (text.length === 0) {
                this.cancelConstruction();
                return;
            }
            this.mainElement.setTextContent(text, false);
            this.mainElement.setFontFamily('Nunito');
            this.updateBorderElement();
            this.updateCornerElements();
        });
    }

    registerEditingEvents() {
        const element = this.shapeGroup.element;

        ['touchenter', 'mouseenter'].forEach( evt =>
            element.addEventListener(evt,
                () => {
                    const activeTool = this.root.querySelector('[data-button-group=tool].active');
                    const dragIsActive = activeTool.id.split('-')[0] === 'drag';

                    if(element.classList.contains('editing') && !dragIsActive) {
                        activateTextEditing(this);
                    } else {
                        returnTextToNormal(this, dragIsActive);
                    }
                },
            false)
        );

        ['touchleave', 'mouseleave'].forEach( evt => 
            element.addEventListener(evt, () => {
                this.Canvas.params.editingTextInZone = false;
            }, false)
        );

        ['touchstart', 'mousedown'].forEach( evt =>
            element.addEventListener(evt, () => {
                if(!element.classList.contains('editing') || !this.Canvas.params.editingTextInZone) return;
    
                handleEditTextClick(this);
            },  false)
        );

        function returnTextToNormal(thisClass, dragIsActive) {
            if(dragIsActive){
                element.style.cursor = 'move';
            } else {
                element.style.cursor = 'crosshair';
            }
            thisClass.Canvas.params.editingTextInZone = false;
        }

        function activateTextEditing(thisClass) {
            element.style.cursor = 'text';
            thisClass.Canvas.params.editingTextInZone = true;
        }

        function handleEditTextClick(thisClass) {
            const textElement = thisClass.mainElement.element;
            const coordinates = thisClass.drawingApp.convertCanvas2DomCoordinates({
                x: textElement.getAttribute('x'),
                y: textElement.getAttribute('y'),
            });

            let textInput = makeTextInput(thisClass, textElement, coordinates);

            textInput.element.value = textElement.textContent;
            textElement.textContent = '';
            textElement.parentElement.style.display = 'none';

            textInput.focus();

            addInputEventListeners(thisClass, textInput, textElement);
        }

        function makeTextInput(thisClass, textElement, coordinates) {
            let canvasContainer = thisClass.root.querySelector("#svg-canvas").parentElement;
            const fontSize = parseFloat(textElement.style.fontSize);
            const topOffset = fontSize * parseFloat(getComputedStyle(document.documentElement).fontSize)
            const textInput = new htmlElement("input", canvasContainer, {
                id: "edit-text-input",
                type: "text",
                style:
                    `width: ${textElement.getBoundingClientRect().width}px;\
                    position: absolute;\
                    top: ${coordinates.y - topOffset}px;\
                    left: ${coordinates.x}px;\
                    font-size: ${fontSize}rem;\
                    color: ${textElement.getAttribute("fill")};\
                    opacity: ${textElement.getAttribute("opacity")};\
                    font-weight: ${textElement.style.fontWeight || "normal"};\
                    transform-origin: bottom left;\
                    transform: scale(${thisClass.Canvas.params.zoomFactor})`,
                autocomplete: "off",
                spellcheck: "false",
            });
            return textInput;
        }

        function addInputEventListeners(thisClass, textInput, textElement) {
            textInput.addEventListener('input', () => {
                textInput.element.style.width = `${textInput.element.value.length + 1}ch`;
            }, false)

            textInput.addEventListener("focusout", () => {
                const text = textInput.element.value;
                textInput.deleteElement();
                textInput.element.style.display = 'none';
                if (text.length === 0) {
                    thisClass.cancelConstruction();
                    return;
                }
                textElement.textContent = text;
                thisClass.updateBorderElement();
                thisClass.updateCornerElements();
                textElement.parentElement.style = '';
            });
        }
    }
}

export class Image extends svgShape {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {?SVGElement} parent The parent the shape should be appended to.
     * @param drawingApp
     * @param Canvas
     * @param withHelperElements
     * @param withHighlightEvents
     */
    constructor(shapeId, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents) {
        super(shapeId, "image", props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents);
    }

    moveToCenter() {
        const bbox = this.mainElement.getBoundingBox();
        this.mainElement.setX(-bbox.width / 2)
        this.mainElement.setY(-bbox.height / 2)
    }
}

export class Path extends svgShape {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {?SVGElement} parent The parent the shape should be appended to.
     * @param drawingApp
     * @param Canvas
     * @param withHelperElements
     * @param withHighlightEvents
     */
    constructor(shapeId, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents) {
        super(shapeId, "path", props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents);
    }
}

export class Grid extends Path {
    /**
     * @param {number} shapeId The unique identifier the shape gets.
     * @param {?propObj} props
     * All properties (attributes) to be assigned to the shape,
     * when omitted the properties of the shape are loaded.
     * @param {HTMLElement} parent The parent the shape should be appended to.
     * @param drawingApp
     * @param Canvas
     */
    constructor(shapeId, props, parent, drawingApp, Canvas) {
        super(shapeId, props, parent, drawingApp, Canvas, false);
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

    update(gridSize) {
        const size = gridSize ? gridSize : this.drawingApp.params.gridSize;
        this.setDAttributes(
            this.calculateDAttributeForGrid(size),
            this.calculateDAttributeForOrigin(size)
        );
    }

    calculateDAttributeForGrid(size) {
        let bounds = {};
        if (this.Canvas !== null) {
            bounds = this.Canvas.params.bounds;
        }
        if (Object.keys(bounds).length === 0) {
            bounds = calculatePreviewBounds(this.parent.parentElement);
        }
        const interval = size * pixelsPerCentimeter,
            lineAmount = this.calculateAmountOfGridLines(interval, bounds);
        let strOfPoints = ``;
        //Verticaal
        for (let i = -lineAmount.left; i <= lineAmount.right; i++) {
            strOfPoints += `M${interval * i},${bounds.top}v${bounds.height} `;
        }
        //Horizontaal
        for (let j = -lineAmount.top; j <= lineAmount.bottom; j++) {
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
     * @param drawingApp
     * @param Canvas
     * @param withHelperElements
     * @param withHighlightEvents
     */
    constructor(shapeId, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents) {
        super(shapeId, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents);
        this.shapeGroup.setAttribute("id", `freehand-${shapeId}`);
    }
}