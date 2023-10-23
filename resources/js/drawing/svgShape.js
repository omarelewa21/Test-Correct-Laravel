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
        this.shapeCanBeResized() &&
        this.cornerElements.forEach((cornerElement) => {
            cornerElement.show();
        });
    }

    shapeCanBeResized() {
        return this.shapeGroup.element.classList.contains('selected') ||
            (this.elementBelongsToCurrentLayer() && this.drawingApp.currentToolIs('drag'))
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

    meetsMinRequirements() {
        return true;
    }

    setInputValuesOnEdit() {}
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

    setInputValuesOnEdit() {
        this.setFillColorOnEdit();
        this.setOpacityInputValueOnEdit();
        this.setStrokeColorOnEdit();
        this.setStrokeWidthOnEdit();
    }

    setFillColorOnEdit() {
        const fillColor = this.mainElement.getAttribute("fill");
        const input = this.UI.fillColorRect;
        input.value = fillColor;
        input.style.cssText = `background-color: ${fillColor}; color: ${fillColor};`;
    }

    setStrokeColorOnEdit() {
        const strokeColor = this.mainElement.getAttribute("stroke");
        const input = this.UI.strokeColorRect;
        input.value = strokeColor;
        input.style.cssText = `border-color: ${strokeColor}`;
    }

    setOpacityInputValueOnEdit() {
        const input = this.UI.fillOpacityNumberRect;
        input.value = Math.round(this.mainElement.getAttribute("fill-opacity") * 100);
        input.dispatchEvent(new Event('input'));
    }

    setStrokeWidthOnEdit() {
        this.UI.strokeWidthRect.value = this.mainElement.getAttribute("stroke-width");
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

    updateFillColor() {
        this.mainElement.setAttribute("fill", this.UI.fillColorRect.value);
    }

    updateStrokeWidth() {
        this.mainElement.setAttribute("stroke-width", this.UI.strokeWidthRect.value);
    }

    updateOpacity() {
        const opacity = parseFloat(this.UI.fillOpacityNumberRect.value / 100);
        this.mainElement.setAttribute("opacity", opacity);
        this.mainElement.setAttribute("fill-opacity", opacity);
    }

    updateStrokeColor() {
        this.mainElement.setAttribute("stroke", this.UI.strokeColorRect.value);
    }

    meetsMinRequirements() {
        const bbox = this.mainElement.getBoundingBox();
        return bbox.width >= 8 && bbox.height >= 8;
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

    setInputValuesOnEdit() {
        this.setFillColorOnEdit();
        this.setOpacityInputValueOnEdit();
        this.setStrokeColorOnEdit();
        this.setStrokeWidthOnEdit();
    }

    setFillColorOnEdit() {
        const fillColor = this.mainElement.getAttribute("fill");
        const input = this.UI.fillColorCircle;
        input.value = fillColor;
        input.style.cssText = `background-color: ${fillColor}; color: ${fillColor};`;
    }

    setStrokeColorOnEdit() {
        const strokeColor = this.mainElement.getAttribute("stroke");
        const input = this.UI.strokeColorCircle;
        input.value = strokeColor;
        input.style.cssText = `border-color: ${strokeColor}`;
    }

    setOpacityInputValueOnEdit() {
        const input = this.UI.fillOpacityNumberCircle;
        input.value = Math.round(this.mainElement.getAttribute("fill-opacity") * 100);
        input.dispatchEvent(new Event('input'));
    }

    setStrokeWidthOnEdit() {
        this.UI.strokeWidthCircle.value = this.mainElement.getAttribute("stroke-width");
    }

    static getMainElementAttributes(cursorPosition, UI) {
        return {
            "cx": cursorPosition.x,
            "cy": cursorPosition.y,
            "r": 0,
            "fill":
                UI.fillOpacityNumberCircle.value === 0 ? "none" : UI.fillColorCircle.value,
            "fill-opacity": parseFloat(UI.fillOpacityNumberCircle.value / 100),
            "stroke": UI.strokeColorCircle.value,
            "stroke-width": UI.strokeWidthCircle.value,
            "opacity": parseFloat(UI.fillOpacityNumberCircle.value / 100),
        };
    }

    updateFillColor() {
        this.mainElement.setAttribute("fill", this.UI.fillColorCircle.value);
    }

    updateStrokeWidth() {
        this.mainElement.setAttribute("stroke-width", this.UI.strokeWidthCircle.value);
    }

    updateOpacity() {
        const opacity = parseFloat(this.UI.fillOpacityNumberCircle.value / 100);
        this.mainElement.setAttribute("opacity", opacity);
        this.mainElement.setAttribute("fill-opacity", opacity);
    }

    updateStrokeColor() {
        this.mainElement.setAttribute("stroke", this.UI.strokeColorCircle.value);
    }

    meetsMinRequirements() {
        return this.mainElement.getAttribute("r") >= 4;
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

    editOwnMarkerForThisShape() {
        this.makeOwnMarkerForThisShape(true);
    }

    makeOwnMarkerForThisShape(editing = false) {
        const markerType = editing ? this.getCurrentActiveMarkerType() : this.getMarkerType();
        if(markerType === "no-endmarker"){
            this.marker?.remove();
            this.marker = null;
            this.props.main["marker-end"] = "url(#svg-no-endmarker-line)";
            this.mainElement.setAttributeOnElementWithValidation(
                "marker-end",
                "url(#svg-no-endmarker-line)"
            );
            return;
        }

        const newMarker = this.cloneGenericMarker(markerType);

        this.svgCanvas.firstElementChild.appendChild(newMarker);

        const newMarkerId = `${newMarker.id}-line-${this.shapeId}`;
        this.deletePreviousMarker(newMarkerId);

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

    static getMainElementAttributes(cursorPosition, UI, params) {
        return {
            "x1": cursorPosition.x,
            "y1": cursorPosition.y,
            "x2": cursorPosition.x,
            "y2": cursorPosition.y,
            "marker-end": `url(#svg-${params.endmarkerType}-line)`,
            "stroke": UI.penColorLine.value,
            "stroke-width": UI.penWidthLine.value,
        };
    }

    getMarkerType() {
        const type = this.props.main["marker-end"];
        return type.substring(type.indexOf("svg-") + 4, type.lastIndexOf("-line"));
    }

    getCurrentActiveMarkerType() {
        return this.drawingApp.params.endmarkerType;
    }

    cloneGenericMarker(type) {
        const markerToClone = this.root.querySelector(`marker#svg-${type}`);
        return markerToClone.cloneNode(true);
    }

    deletePreviousMarker(newMarkerId) {
        const previousMarker = this.root.querySelectorAll(`marker#${newMarkerId}`);
        previousMarker.forEach((marker) => {
            marker.remove();
        });
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

    updateMarkerColor() {
        if (!this.marker) return;
        const propertyToChange = this.getPropertyToChange(this.getMarkerType());
        this.marker.style[propertyToChange] = this.UI.penColorLine.value;
    }

    setInputValuesOnEdit() {
        this.setLineColorOnEdit();
        this.setLineWidthOnEdit();
        this.setEndMarkerOnEdit();
    }

    setLineColorOnEdit() {
        const lineColor = this.mainElement.getAttribute("stroke");
        const input = this.UI.penColorLine;
        input.value = lineColor;
        input.style.cssText = `background-color: ${lineColor}; color: ${lineColor};`;
    }

    setLineWidthOnEdit() {
        this.UI.penWidthLine.value = this.mainElement.getAttribute("stroke-width");
    }

    setEndMarkerOnEdit() {
        const markerType = this.getMarkerType();
        this.root.querySelector('.endmarker-type.active')?.classList.remove('active');
        this.root.querySelector(`.endmarker-type#${markerType}`)?.classList.add('active');
    }

    updatePenWidth() {
        this.mainElement.setAttribute("stroke-width", this.UI.penWidthLine.value);
    }

    updatePenColor() {
        this.mainElement.setAttribute("stroke", this.UI.penColorLine.value);
        this.updateMarkerColor();
    }

    meetsMinRequirements() {
        return this.mainElement.element.getTotalLength() >= 10;
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

    static getMainElementAttributes(cursorPosition, UI, params) {
        return {
            "x": cursorPosition.x,
            "y": cursorPosition.y,
            "fill": UI.textColor.value,
            "stroke-width": 0,
            "opacity": parseFloat(UI.elemOpacityNumber.value / 100),
            "style": `${params.boldText ? "font-weight: bold;" : ""} font-size: ${UI.textSize.value / 16}rem`,
        };
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

    setInputValuesOnEdit() {
        this.setTextColorOnEdit();
        this.setTextSizeOnEdit();
        this.setBoldTextOnEdit();
        this.setOpacityInputValueOnEdit();
    }

    setTextColorOnEdit() {
        const textColor = this.mainElement.getAttribute("fill");
        const input = this.UI.textColor;
        input.value = textColor;
        input.style.cssText = `background-color: ${textColor}; color: ${textColor};`;
    }

    setTextSizeOnEdit() {
        this.UI.textSize.value = parseFloat(this.mainElement.element.style.fontSize) * 16;
    }

    setBoldTextOnEdit() {
        const isBold = this.mainElement.element.style.fontWeight === 'bold';
        this.drawingApp.params.boldText = this.UI.boldText.checked = isBold;

        if(isBold){
            this.UI.boldToggleButton.classList.add('active');
        } else {
            this.UI.boldToggleButton.classList.remove('active');
        }
    }

    setOpacityInputValueOnEdit() {
        const input = this.UI.elemOpacityNumber;
        input.value = Math.round(this.mainElement.getAttribute("opacity") * 100);
        input.dispatchEvent(new Event('input'));
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

    static getMainElementAttributes(cursorPosition, UI) {
        return {
            "d": `M ${cursorPosition.x},${cursorPosition.y}`,
            "fill": "none",
            "stroke": UI.penColorFreehand.value,
            "stroke-width": UI.penWidthFreehand.value,
        };
    }

    updatePenWidth() {
        this.mainElement.setAttribute("stroke-width", this.UI.penWidthFreehand.value);
    }

    updatePenColor() {
        this.mainElement.setAttribute("stroke", this.UI.penColorFreehand.value);
    }

    meetsMinRequirements() {
        return this.mainElement.element.getTotalLength() >= 10;
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

    setInputValuesOnEdit() {
        this.setPathColorOnEdit();
        this.setPathWidthOnEdit();
    }

    setPathColorOnEdit() {
        const pathColor = this.mainElement.getAttribute("stroke");
        const input = this.UI.penColorFreehand;
        input.value = pathColor;
        input.style.cssText = `background-color: ${pathColor}; color: ${pathColor};`;
    }

    setPathWidthOnEdit() {
        this.UI.penWidthFreehand.value = this.mainElement.getAttribute("stroke-width");
    }
}