import {
    svgNS,
    validSvgElementKeys
} from "./constants.js";

class svgElement {
    constructor(type, props) {
        this.type = type;
        this.element = this.createSvgElementOfType(type);
        this.props = props ?? {};
        this.setAllAttributesOnElement();
        this.draw = {};
        this.drag = {};
        this.resize = {};
    }

    /**
     * Creates an SVG Element of given type.
     * @param {string} type
     * @returns The SVG Element.
     */
    createSvgElementOfType(type) {
        return document.createElementNS(svgNS, type);
    }

    /**
     * Sets attribute specified by key to given value, without validation, using .setAttributeNS()
     * @param {string} key
     * @param {string|number|boolean} value
     */
    setAttribute(key, value) {
        this.element.setAttributeNS(null, key, value);
    }

    /**
     * Calls getAttributeNS() on this.element to get the value of an attribute.
     * @param {string} key Key of the attribute to get.
     * @returns The return value of getAttributeNS()
     */
    getAttribute(key) {
        return this.element.getAttributeNS(null, key);
    }

    /**
     * Checks if a given key-value pair may be set on the element and does so if allowed. Shows an info in the console if not allowed.
     * @param {string} key
     * @param {string|number|boolean} value
     */
    setAttributeOnElementWithValidation(key, value) {
        if (this.keyIsValid(key) && this.valueIsValid(value)) {
            this.setAttribute(key, value);
        } else {
            console.info(
                `Attribute %c${key}%c is invalid for this element (type: ${this.type}) and has been ignored.`,
                "font-style: italic",
                null
            );
        }
    }

    /**
     * Checks if a given key may be set on the element.
     * @param {string} key An attribute key to check.
     * @returns Boolean value indicating if the given key may be set on the element.
     */
    keyIsValid(key) {
        let attrKeysToLoopOver = validSvgElementKeys.global.concat(
            validSvgElementKeys[this.type]
        );
        return attrKeysToLoopOver.some((attr) => {
            return attr === key;
        });
    }

    /**
     * Checks if a given value is a string, number or boolean.
     * @param {*} value
     * @returns Boolean value indicating if the value is a string,
     * number or boolean (all true) or something else (false).
     */
    valueIsValid(value) {
        return (typeof value) === "string" ||
            (typeof value) === "number" ||
            (typeof value) === "boolean";
    }

    /**
     * Loops over all attributes given and calls setAttributeOnElementWithValidation() on each.
     */
    setAllAttributesOnElement() {
        for (const [key, value] of Object.entries(this.props)) {
            this.setAttributeOnElementWithValidation(key, value);
        }
    }

    /**
     * Hide the element.
     */
    hide() {
        this.element.style.display = "none";
    }

    show() {
        this.element.style.display = "";
    }

    /**
     * Calls getBBox() on this.element.
     * @returns The return value of getBBox().
     */
    getBoundingBox() {
        return this.element.getBBox();
    }

    /**
     * Calls addEventListener() on this.element with the given arguments.
     * @param {string} type
     * @param {Function} func
     */
    addEventListener(type, func) {
        this.element.addEventListener(type, func);
    }

    /**
     * Calls appendChild() on this.element with the given argument.
     * @param {Element} element
     */
    appendChild(element) {
        this.element.appendChild(element);
    }

    /**
     * Calls remove() on this.element and deletes this object.
     */
    remove() {
        this.element.remove();
        delete this;
    }

    onDrag(evt, cursor) {
        this.move(this.calculateMovedDistance(cursor));
    }

    move(distance) {
    }

    calculateMovedDistance(cursor) {
        const previousCursorPosition = this.drag.previousCursorPosition;
        this.drag.previousCursorPosition = cursor;
        return {
            dx: cursor.x - previousCursorPosition.x,
            dy: cursor.y - previousCursorPosition.y,
        };
    }
}

export class Rectangle extends svgElement {
    constructor(props = null) {
        super("rect", props);
    }

    onDrawStart(evt, cursor) {
        this.draw.startingPosition = cursor;
    }

    /**
     * Function to be called when the cursor was moved.
     * @param {Event} evt The event that triggered the function.
     * @param {{x: number, y: number}} cursor The current cursor position.
     */
    onDraw(evt, cursor) {
        let coords = this.calculateCoords(cursor, this.draw.startingPosition);
        this.updateAttributes(coords);
    }

    onDragStart(evt, cursor) {
        this.drag.previousCursorPosition = cursor;
        this.drag.startingPosition = {x: this.props.x, y: this.props.y};
    }

    onResizeStart(evt, cursor) {
        this.resize.startingPosition = {x: this.props.x, y: this.props.y};
    }

    onResize(evt, cursor) {
        const coords = this.calculateCoords(cursor, this.resize.startingPosition);
        this.updateAttributes(coords);
    }

    updateAttributes(coords) {
        this.updatePosition(coords);
        this.updateSize(coords);
    }

    move(distance) {
        this.setX(parseInt(this.props.x) + distance.dx);
        this.setY(parseInt(this.props.y) + distance.dy);
    }

    updatePosition(coords) {
        this.setX(coords.x);
        this.setY(coords.y);
    }

    updateSize(coords) {
        this.setWidth(coords.width);
        this.setHeight(coords.height);
    }

    /**
     * Adjusts the x, y, width and height of a rectangle because SVG can't handle negative width and height values.
     * @param {{x: number, y: number}} cursor The current cursor position.
     * @param {{x: number, y: number}} startingPosition The cursor position at the start
     * @returns An Object containing a valid value for x, y, width and height.
     */
    calculateCoords(cursor, startingPosition) {
        let x = startingPosition.x,
            y = startingPosition.y,
            width = cursor.x - x,
            height = cursor.y - y;
        if (width < 0) {
            x = cursor.x;
            width *= -1;
        }
        if (height < 0) {
            y = cursor.y;
            height *= -1;
        }
        return {
            x,
            y,
            width,
            height,
        };
    }

    /**
     * Sets the X attribute on the shape and in the props
     * @param {number} value The value to be set.
     */
    setX(value) {
        this.setXAttribute(value);
        this.setXProperty(value);
    }

    /**
     * Sets the X attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setXAttribute(value) {
        this.setAttributeOnElementWithValidation("x", value);
    }

    /**
     * Sets the X attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setXProperty(value) {
        this.props.x = value;
    }

    /**
     * Sets the Y attribute on the shape and in the props
     * @param {number} value The value to be set.
     */
    setY(value) {
        this.setYAttribute(value);
        this.setYProperty(value);
    }

    /**
     * Sets the Y attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setYAttribute(value) {
        this.setAttributeOnElementWithValidation("y", value);
    }

    /**
     * Sets the Y attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setYProperty(value) {
        this.props.y = value;
    }

    /**
     * Sets the Width attribute on the shape and in the props
     * @param {number} value The value to be set.
     */
    setWidth(value) {
        this.setWidthAttribute(value);
        this.setWidthProperty(value);
    }

    /**
     * Sets the Width attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setWidthAttribute(value) {
        this.setAttributeOnElementWithValidation("width", value);
    }

    /**
     * Sets the Width attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setWidthProperty(value) {
        this.props.width = value;
    }

    /**
     * Sets the Height attribute on the shape and in the props
     * @param {number} value The value to be set.
     */
    setHeight(value) {
        this.setHeightAttribute(value);
        this.setHeightProperty(value);
    }

    /**
     * Sets the Height attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setHeightAttribute(value) {
        this.setAttributeOnElementWithValidation("height", value);
    }

    /**
     * Sets the Height attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setHeightProperty(value) {
        this.props.height = value;
    }
}

export class Circle extends svgElement {
    constructor(props = null) {
        super("circle", props);
    }

    /**
     * Function to be called when the cursor was moved during draw.
     * @param {Event} evt The event that triggered the function.
     * @param {{x: number, y: number}} cursor The current cursor position.
     */
    onDraw(evt, cursor) {
        this.setR(this.calculateRadius(cursor));
    }

    onDragStart(evt, cursor) {
        this.drag.previousCursorPosition = cursor;
        this.drag.startingPosition = {x: this.props.cx, y: this.props.cy};
    }

    /**
     * Function to be called when the cursor was moved during resize.
     * @param {Event} evt The event that triggered the function.
     * @param {{x: number, y: number}} cursor The current cursor position.
     */
    onResize(evt, cursor) {
        // The scaling ratio is here because the corner points do not lay on the circle,
        // but on a circle with a radius of sqrt(2) bigger
        const SCALING_RATIO = Math.SQRT1_2;
        this.setR(SCALING_RATIO * this.calculateRadius(cursor));
    }

    /**
     * Calculates the radius of a circle.
     * @param {{x: number, y: number}} cursor (x,y) position of the cursor on the screen.
     * @returns Radius of the circle.
     */
    calculateRadius(cursor) {
        let dx = cursor.x - this.props.cx;
        let dy = cursor.y - this.props.cy;
        return Math.sqrt(Math.pow(dx, 2) + Math.pow(dy, 2));
    }

    updateSize(coords) {
        this.setR(coords.r);
    }

    /**
     * Sets the R attribute on the shape and in the props.
     * @param {number} value The value to be set.
     */
    setR(value) {
        this.setRAttribute(value);
        this.setRProperty(value);
    }

    /**
     * Sets the R attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setRAttribute(value) {
        this.setAttributeOnElementWithValidation("r", value);
    }

    /**
     * Sets the R attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setRProperty(value) {
        this.props.r = value;
    }

    move(distance) {
        this.setCX(parseInt(this.props.cx) + distance.dx);
        this.setCY(parseInt(this.props.cy) + distance.dy);
    }

    updatePosition(coords) {
        this.setCX(coords.x);
        this.setCY(coords.y);
    }

    /**
     * Sets the CX attribute on the shape and in the props.
     * @param {number} value The value to be set.
     */
    setCX(value) {
        this.setCXAttribute(value);
        this.setCXProperty(value);
    }

    /**
     * Sets the CX attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setCXAttribute(value) {
        this.setAttributeOnElementWithValidation("cx", value);
    }

    /**
     * Sets the CX attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setCXProperty(value) {
        this.props.cx = value;
    }

    /**
     * Sets the CY attribute on the shape and in the props.
     * @param {number} value The value to be set.
     */
    setCY(value) {
        this.setCYAttribute(value);
        this.setCYProperty(value);
    }

    /**
     * Sets the CY attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setCYAttribute(value) {
        this.setAttributeOnElementWithValidation("cy", value);
    }

    /**
     * Sets the CY attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setCYProperty(value) {
        this.props.cy = value;
    }
}

export class Line extends svgElement {
    constructor(props = null) {
        super("line", props);
    }

    /**
     * Function to be called when the cursor was moved.
     * @param {Event} evt The event that triggered the function.
     * @param {{x: number, y: number}} cursor The current cursor position.
     */
    onDraw(evt, cursor) {
        this.setX2Attribute(cursor.x);
        this.setY2Attribute(cursor.y);
    }

    setX1(value) {
        this.setX1Attribute(value);
        this.setX1Property(value);
    }

    /**
     * Sets the X1 attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setX1Attribute(value) {
        this.setAttributeOnElementWithValidation("x1", value);
    }

    /**
     * Sets the X1 attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setX1Property(value) {
        this.props.x1 = value;
    }

    setX2(value) {
        this.setX2Attribute(value);
        this.setX2Property(value);
    }

    /**
     * Sets the X2 attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setX2Attribute(value) {
        this.setAttributeOnElementWithValidation("x2", value);
    }

    /**
     * Sets the X2 attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setX2Property(value) {
        this.props.x2 = value;
    }

    setY1(value) {
        this.setY1Attribute(value);
        this.setY1Property(value);
    }

    /**
     * Sets the Y1 attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setY1Attribute(value) {
        this.setAttributeOnElementWithValidation("y1", value);
    }

    /**
     * Sets the Y1 attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setY1Property(value) {
        this.props.y1 = value;
    }

    setY2(value) {
        this.setY2Attribute(value);
        this.setY2Property(value);
    }

    /**
     * Sets the Y2 attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setY2Attribute(value) {
        this.setAttributeOnElementWithValidation("y2", value);
    }

    /**
     * Sets the Y2 attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setY2Property(value) {
        this.props.y2 = value;
    }
}

export class Image extends svgElement {
    constructor(props = null) {
        super("image", props);
    }

    onDragStart(evt, cursor) {
        this.drag.previousCursorPosition = cursor;
        this.drag.startingPosition = {x: this.props.x, y: this.props.y};
    }

    onResize(evt, cursor) {
        const coords = this.calculateCoords(cursor);
        this.updateAttributes(coords);
    }

    calculateCoords(cursor) {
        let x = this.props.x,
            y = this.props.y,
            width = cursor.x - x,
            height = cursor.y - y;
        if (width < 0) {
            width = 0;
        }
        if (height < 0) {
            height = 0;
        }
        return {
            x,
            y,
            width,
            height,
        };
    }

    updateAttributes(coords) {
        this.updatePosition(coords);
        this.updateSize(coords);
    }

    move(distance) {
        this.setX(parseInt(this.props.x) + distance.dx);
        this.setY(parseInt(this.props.y) + distance.dy);
    }

    updatePosition(coords) {
        this.setX(coords.x);
        this.setY(coords.y);
    }

    updateSize(coords) {
        this.setWidth(coords.width);
        this.setHeight(coords.height);
    }

    /**
     * Sets the X attribute on the shape and in the props
     * @param {number} value The value to be set.
     */
    setX(value) {
        this.setXAttribute(value);
        this.setXProperty(value);
    }

    /**
     * Sets the Y attribute on the shape and in the props
     * @param {number} value The value to be set.
     */
    setY(value) {
        this.setYAttribute(value);
        this.setYProperty(value);
    }

    /**
     * Sets the X attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setXAttribute(value) {
        this.setAttributeOnElementWithValidation("x", value);
    }

    /**
     * Sets the X attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setXProperty(value) {
        this.props.x = value;
    }

    /**
     * Sets the Y attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setYAttribute(value) {
        this.setAttributeOnElementWithValidation("y", value);
    }

    /**
     * Sets the Y attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setYProperty(value) {
        this.props.y = value;
    }

    setWidth(value) {
        this.setWidthAttribute(value);
        this.setWidthProperty(value);
    }

    /**
     * Sets the Width attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setWidthAttribute(value) {
        this.setAttributeOnElementWithValidation("width", value);
    }

    /**
     * Sets the Width attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setWidthProperty(value) {
        this.props.width = value;
    }

    setHeight(value) {
        this.setHeightAttribute(value);
        this.setHeightProperty(value);
    }

    /**
     * Sets the Height attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setHeightAttribute(value) {
        this.setAttributeOnElementWithValidation("height", value);
    }

    /**
     * Sets the Height attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setHeightProperty(value) {
        this.props.height = value;
    }

    /**
     * Sets the Href attribute on the shape.
     * @param {string} value The value to be given to the attribute.
     */
    setHrefAttribute(value) {
        this.setAttributeOnElementWithValidation("href", value);
    }

    /**
     * Sets the Href attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setHrefProperty(value) {
        this.props.href = value;
    }
}

export class Path extends svgElement {
    constructor(props = null) {
        super("path", props);
    }

    /**
     * Sets the D attribute on the shape.
     * @param {string} value The value to be given to the attribute.
     */
    setDAttribute(value) {
        this.setAttributeOnElementWithValidation("d", value);
    }

    /**
     * Calls this.getAttribute("d").
     * @returns the return value of this.getAttribute("d")
     */
    getDAttribute() {
        return this.getAttribute("d");
    }

    /**
     * Sets the D attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setDProperty(value) {
        this.props.d = value;
    }
}

export class Text extends svgElement {
    constructor(props = null) {
        super("text", props);
    }

    /**
     * Function to be called when the cursor was moved.
     * @param {Event} evt The event that triggered the function.
     * @param {{x: number, y: number}} cursor The current cursor position.
     */
    onDraw(evt, cursor) {
        this.setAttributeOnElementWithValidation("x", cursor.x);
        this.setAttributeOnElementWithValidation("y", cursor.y);
    }

    onDragStart(evt, cursor) {
        this.drag.previousCursorPosition = cursor;
        this.drag.startingPosition = {x: this.props.x, y: this.props.y};
    }

    move(distance) {
        this.setX(parseInt(this.props.x) + distance.dx);
        this.setY(parseInt(this.props.y) + distance.dy);
    }

    updatePosition(coords) {
        this.setX(coords.x);
        this.setY(coords.y);
    }

    setX(value) {
        this.setXAttribute(value);
        this.setXProperty(value);
    }

    /**
     * Sets the X attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setXAttribute(value) {
        this.setAttributeOnElementWithValidation("x", value);
    }

    /**
     * Sets the X attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setXProperty(value) {
        this.props.x = value;
    }

    setY(value) {
        this.setYAttribute(value);
        this.setYProperty(value);
    }

    /**
     * Sets the Y attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setYAttribute(value) {
        this.setAttributeOnElementWithValidation("y", value);
    }

    /**
     * Sets the Y attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setYProperty(value) {
        this.props.y = value;
    }

    /**
     * Appends the specified text to the text element.
     * @param {string} text The text to be appended.
     */
    setTextContent(text) {
        this.element.textContent = text;
    }

    setFontFamily(font) {
        this.element.setAttribute('font-family', font);
    }
}

export class Textbox extends svgElement {
    constructor(props = null) {
        super("text", props);
    }
}

export class Group extends svgElement {
    constructor(props = null) {
        super("g", props);
    }
}