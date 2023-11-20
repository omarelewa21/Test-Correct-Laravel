import {
    svgNS,
    validSvgElementKeys
} from "./constants.js";

/**
 * @typedef RectangleCoords
 * @type {Object}
 * @property {number} x
 * @property {number} y
 * @property {number} width
 * @property {number} height
 */
/**
 * @typedef CircleCoords
 * @type {Object}
 * @property {number} cx
 * @property {number} cy
 * @property {number} r
 */

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
     * Hides the element.
     */
    hide() {
        this.element.style.display = "none";
    }

    /**
     * Shows the element.
     */
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

    /**
     * Event handler called during dragging
     * @param {Event} evt
     * @param {Cursor} cursor
     */
    onDrag(evt, cursor) {
        this.move(this.calculateMovedDistance(cursor, this.drag.previousCursorPosition));
        this.drag.previousCursorPosition = cursor;
    }

    move() {}

    /**
     * Calculates difference between previous and current cursor position
     * @param {Cursor} currentPosition
     * @param {Cursor} previousPosition
     * @returns {{dx: number, dy: number}}
     */
    calculateMovedDistance(currentPosition, previousPosition) {
        return {
            dx: currentPosition.x - previousPosition.x,
            dy: currentPosition.y - previousPosition.y,
        };
    }
}

export class Rectangle extends svgElement {
    constructor(props = null) {
        super("rect", props);
    }

    /**
     * Event handler called at start of drawing
     * @param {Event} evt
     * @param {Cursor} cursor
     */
    onDrawStart(evt, cursor) {
        this.draw.startingPosition = cursor;
    }

    /**
     * Event handler called during drawing.
     * @param {Event} evt
     * @param {Cursor} cursor
     */
    onDraw(evt, cursor) {
        let coords = this.calculateCoordsForDraw(cursor, evt.shiftKey);
        this.updateAttributes(coords);
    }

    /**
     * Calculates values for the x, y, width and height properties of the rectangle.
     * @param {{x: number, y: number}} cursor
     * @param {boolean} keepAspectRatio
     * @returns {RectangleCoords}
     */
    calculateCoordsForDraw(cursor, keepAspectRatio=false) {
        const startingPosition = this.draw.startingPosition;
        let coords = {
            x: startingPosition.x,
            y: startingPosition.y,
            width: cursor.x - startingPosition.x,
            height: cursor.y - startingPosition.y,
        };
        return this.getCorrectCords(cursor, coords, keepAspectRatio);
    }
}

export class Ellipse extends svgElement {
    constructor(props = null) {
        super("ellipse", props);
        this.startingPosition = {};
        this.startingPosition.cx = this.props.cx;
        this.startingPosition.cy = this.props.cy;
    }

    /**
     * Event handler called during drawing.
     * @param {Event} evt
     * @param {Cursor} cursor
     */
    onDraw(evt, cursor) {
        this.setCX(cursor.x);
        this.setCY(cursor.y);
        this.setRX(this.calculateRX(cursor));
        this.setRY(this.calculateRY(cursor));
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

    /**
     * Sets the RX attribute on the shape and in the props.
     * @param {Cursor} cursor
     * @return {number} The value to be set.
     */
    calculateRX(cursor) {
        return Math.abs(cursor.x - this.startingPosition.cx);
    }

    /**
     * Sets the RY attribute on the shape and in the props.
     * @param {{x: number, y: number}} cursor
     * @return {number} The value to be set.
     */
    calculateRY(cursor) {
        return Math.abs(cursor.y - this.startingPosition.cy);
    }

    /**
     * Sets the RX attribute on the shape and in the props.
     * @param {number} value The value to be set.
     */
    setRX(value) {
        this.setRXAttribute(value);
        this.setRXProperty(value);
    }

    /** 
     * Sets the RX attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setRXAttribute(value) {
        this.setAttributeOnElementWithValidation("rx", value);
    }

    /**
     * Sets the RX attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setRXProperty(value) {
        this.props.rx = value;
    }

    /**
     * Sets the RY attribute on the shape and in the props.
     * @param {number} value The value to be set.
     */
    setRY(value) {
        this.setRYAttribute(value);
        this.setRYProperty(value);
    }

    /**
     * Sets the RY attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setRYAttribute(value) {
        this.setAttributeOnElementWithValidation("ry", value);
    }

    /**
     * Sets the RY attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setRYProperty(value) {
        this.props.ry = value;
    }
}

export class Circle extends svgElement {
    constructor(props = null) {
        super("circle", props);
    }

    /**
     * Event handler called during drawing.
     * @param {Event} evt
     * @param {Cursor} cursor
     */
    onDraw(evt, cursor) {
        this.setR(this.calculateRadius(cursor));
    }

    /**
     * Event handler called at start of dragging
     * @param {Event} evt
     * @param {Cursor} cursor
     */
    onDragStart(evt, cursor) {
        this.drag.previousCursorPosition = cursor;
        this.drag.startingPosition = {x: this.props.cx, y: this.props.cy};
    }

    /**
     * Event handler called during resize.
     * @param {Event} evt
     * @param {Cursor} cursor
     */
    onResize(evt, cursor) {
        // The scaling ratio is here because the corner points do not lay on the circle,
        // but on a circle with a radius of sqrt(2) bigger
        const SCALING_RATIO = Math.SQRT1_2;
        this.setR(SCALING_RATIO * this.calculateRadius(cursor));
    }

    /**
     * Calculates the radius of a circle.
     * @param {Cursor} cursor
     * @returns {number} Radius of the circle.
     */
    calculateRadius(cursor) {
        const dx = cursor.x - this.props.cx;
        const dy = cursor.y - this.props.cy;
        return Math.sqrt(Math.pow(dx, 2) + Math.pow(dy, 2));
    }

    /**
     * Sets the radius to the given value
     * @param {CircleCoords} coords
     */
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

    /**
     * Sets the RX attribute on the shape and in the props.
     * @param {number} value The value to be set.
     */
    setRX(value) {
        this.setRXAttribute(value);
        this.setRXProperty(value);
    }

    /** 
     * Sets the RX attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setRXAttribute(value) {
        this.setAttributeOnElementWithValidation("rx", value);
    }

    /**
     * Sets the RX attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setRXProperty(value) {
        this.props.rx = value;
    }

    /**
     * Sets the RY attribute on the shape and in the props.
     * @param {number} value The value to be set.
     */
    setRY(value) {
        this.setRYAttribute(value);
        this.setRYProperty(value);
    }

    /**
     * Sets the RY attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setRYAttribute(value) {
        this.setAttributeOnElementWithValidation("ry", value);
    }

    /**
     * Sets the RY attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setRYProperty(value) {
        this.props.ry = value;
    }

    /**
     * Sets the cx and cy values to the old values plus
     * the offset specified by distance.dx and distance.dy
     * @param {{dx: number, dy: number}} distance
     */
    move(distance) {
        this.setCX(parseFloat(this.props.cx) + distance.dx);
        this.setCY(parseFloat(this.props.cy) + distance.dy);
    }

    /**
     * Sets the specified position
     * @param {{x: number, y: number}} position
     */
    updatePosition(position) {
        this.setCX(position.x);
        this.setCY(position.y);
    }
}

export class Line extends svgElement {
    constructor(props = null) {
        super("line", props);
    }

    /**
     * Event handler called during drawing.
     * @param {Event} evt
     * @param {Cursor} cursor
     */
    onDraw(evt, cursor) {
        this.setX2(cursor.x);
        this.setY2(cursor.y);
    }

    /**
     * Event handler called at start of dragging
     * @param {Event} evt
     * @param {Cursor} cursor
     */
    onDragStart(evt, cursor) {
        this.drag.previousCursorPosition = cursor;
        this.drag.startingPosition = {
            x1: this.props.x1,
            y1: this.props.y1,
            x2: this.props.x2,
            y2: this.props.y2,
        };
    }

    /**
     * Sets the x and y values to the old values plus
     * the offset specified by distance.dx and distance.dy
     * @param {{dx: number, dy: number}} distance
     */
    move(distance) {
        this.setX1(parseFloat(this.props.x1) + distance.dx);
        this.setY1(parseFloat(this.props.y1) + distance.dy);
        this.setX2(parseFloat(this.props.x2) + distance.dx);
        this.setY2(parseFloat(this.props.y2) + distance.dy);
    }

    /**
     * Sets the X1 attribute on the shape and in the props
     * @param value
     */
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

    /**
     * Sets the X2 attribute on the shape and in the props
     * @param value
     */
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

    /**
     * Sets the Y1 attribute on the shape and in the props
     * @param value
     */
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

    /**
     * Sets the Y2 attribute on the shape and in the props
     * @param value
     */
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

    /**
     * Sets the Href attribute on the shape and in the props
     * @param value
     */
    setHref(value) {
        this.setHrefAttribute(value);
        this.setHrefProperty(value);
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
     * Event handler called at the start of drawing
     * @param evt
     * @param cursor
     */
    onDrawStart(evt, cursor) {
        this.draw.previousPosition = cursor;
    }

    /**
     * Event handler called during drawing
     * @param evt
     * @param cursor
     */
    onDraw(evt, cursor) {
        const path = this.getDAttribute();
        const distance = this.calculateMovedDistance(cursor, this.draw.previousPosition);
        this.setD(`${path} l ${distance.dx},${distance.dy}`);
        this.draw.previousPosition = cursor;
    }

    /**
     * Event handler called at the start of dragging
     * @param evt
     * @param cursor
     */
    onDragStart(evt, cursor) {
        this.drag.previousCursorPosition = cursor;
    }

    /**
     * Helper function called from onDrag to move the element
     * @param {{dx: number, dy: number}} distance
     */
    move(distance) {
        let dValue = this.props.d.split(" ");
        dValue[1] = this.calculateNewStartingPoint(dValue[1], distance);
        this.setD(dValue.join(" "));
    }

    /**
     * Parses oldStartingPoint, then moves it by the offset specified by distance.
     * @param {string} oldStartingPoint
     * @param {{dx: number, dy: number}} distance
     * @returns {string}
     */
    calculateNewStartingPoint(oldStartingPoint, distance) {
        const oldCoords = oldStartingPoint.split(",").map(parseFloat);
        return [oldCoords[0] + distance.dx, oldCoords[1] + distance.dy].join(",");
    }

    /**
     * Sets the D attribute on the shape and in the props
     * @param {string} value
     */
    setD(value) {
        this.setDAttribute(value);
        this.setDProperty(value);
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
     * @param {string} value The value to be given to the property.
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
     * Event handler called during drawing
     * @param {Event} evt
     * @param {Cursor} cursor
     */
    onDraw(evt, cursor) {
        this.setAttributeOnElementWithValidation("x", cursor.x);
        this.setAttributeOnElementWithValidation("y", cursor.y);
    }

    /**
     * Event handler called at the start of dragging
     * @param {Event} evt
     * @param {Cursor} cursor
     */
    onDragStart(evt, cursor) {
        this.drag.previousCursorPosition = cursor;
        this.drag.startingPosition = {x: this.props.x, y: this.props.y};
    }

    /**
     * Helper function called from onDrag to move the element
     * @param {{dx: number, dy: number}} distance
     */
    move(distance) {
        this.setX(parseFloat(this.props.x) + distance.dx);
        this.setY(parseFloat(this.props.y) + distance.dy);
    }

    updatePosition(coords) {
        this.setX(coords.x);
        this.setY(coords.y);
    }

    /**
     * Sets the X attribute on the shape and in the props.
     * @param {number} value
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
     * Sets the Y attribute on the shape and in the props.
     * @param {number} value
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
     * Sets the specified text to be the content of the text element.
     * @param {string} text
     */
    setTextContent(text, shouldDecodeText = true) {
        if(shouldDecodeText) {
            text = decodeURI(text);
        }

        this.element.textContent = text;
    }

    /**
     * Sets the specified font family
     * @param {string} font
     */
    setFontFamily(font) {
        this.setAttributeOnElementWithValidation('font-family', font);
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

const rectangularFunctionality = {

    /**
     * Event handler called at start of dragging
     * @param {Event} evt
     * @param {Cursor} cursor
     */
    onDragStart(evt, cursor) {
        this.drag.previousCursorPosition = cursor;
        this.drag.startingPosition = {x: this.props.x, y: this.props.y};
    },

    updateAttributes(coords) {
        this.updatePosition(coords);
        this.updateSize(coords);
    },

    /**
     * Sets the x and y values to the old values plus
     * the offset specified by distance.dx and distance.dy
     * @param {{dx: number, dy: number}} distance
     */
    move(distance) {
        this.setX(parseFloat(this.props.x) + distance.dx);
        this.setY(parseFloat(this.props.y) + distance.dy);
    },

    updatePosition(coords) {
        this.setX(coords.x);
        this.setY(coords.y);
    },

    updateSize(coords) {
        this.setWidth(coords.width);
        this.setHeight(coords.height);
    },

    /**
     * Sets the X attribute on the shape and in the props
     * @param {number} value The value to be set.
     */
    setX(value) {
        this.setXAttribute(value);
        this.setXProperty(value);
    },

    /**
     * Sets the X attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setXAttribute(value) {
        this.setAttributeOnElementWithValidation("x", value);
    },

    /**
     * Sets the X attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setXProperty(value) {
        this.props.x = value;
    },

    /**
     * Sets the Y attribute on the shape and in the props
     * @param {number} value The value to be set.
     */
    setY(value) {
        this.setYAttribute(value);
        this.setYProperty(value);
    },

    /**
     * Sets the Y attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setYAttribute(value) {
        this.setAttributeOnElementWithValidation("y", value);
    },

    /**
     * Sets the Y attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setYProperty(value) {
        this.props.y = value;
    },

    /**
     * Sets the Width attribute on the shape and in the props
     * @param value
     */
    setWidth(value) {
        this.setWidthAttribute(value);
        this.setWidthProperty(value);
    },

    /**
     * Sets the Width attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setWidthAttribute(value) {
        this.setAttributeOnElementWithValidation("width", value);
    },

    /**
     * Sets the Width attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setWidthProperty(value) {
        this.props.width = value;
    },

    /**
     * Sets the Height attribute on the shape and in the props.
     * @param value
     */
    setHeight(value) {
        this.setHeightAttribute(value);
        this.setHeightProperty(value);
    },

    /**
     * Sets the Height attribute on the shape.
     * @param {number} value The value to be given to the attribute.
     */
    setHeightAttribute(value) {
        this.setAttributeOnElementWithValidation("height", value);
    },

    /**
     * Sets the Height attribute in the props.
     * @param {number} value The value to be given to the property.
     */
    setHeightProperty(value) {
        this.props.height = value;
    },

    /**
     * Event handler called at start of resizing
     * @param {Event} evt
     * @param {Cursor} cursor
     */
    onResizeStart(evt, cursor) {
        this.resize.startingCoords = {
            x: this.props.x,
            y: this.props.y,
            width: this.props.width,
            height: this.props.height,
        };
        this.resize.selectedCorner = this.getClassOfSelectedCorner(evt.target);
    },

    /**
     * Determines the first class name of the given element that matches the format.
     * @param {Element} element
     * @returns {string}
     */
    getClassOfSelectedCorner(element) {
        const cornerElementClassMatcher = /(side-)[\w-]+/g;
        return element.classList.toString()
            .match(cornerElementClassMatcher)[0];
    },

    /**
     * Event handler called during resizing
     * @param {Event} evt
     * @param {Cursor} cursor
     */
    onResize(evt, cursor) {
        const coords = this.calculateCoordsForResize(cursor, evt.shiftKey);
        this.updateAttributes(coords);
    },

    /**
     * Calculates new values for the x, y, width and height properties of the rectangle.
     * @param {{x: number, y: number}} cursor
     * @param {boolean} keepAspectRatio
     * @returns {RectangleCoords}
     */
    calculateCoordsForResize(cursor, keepAspectRatio=false) {
        const startingCoords = this.resize.startingCoords;
        const coords = {
            x: startingCoords.x,
            y: startingCoords.y,
            width: cursor.x - startingCoords.x,
            height: cursor.y - startingCoords.y,
        };
        const replacements = {
            x: cursor.x,
            y: cursor.y,
            width: 0,
            height: 0,
        };
        switch (this.resize.selectedCorner) {
            case "side-se":
                break;
            case "side-ne":
                coords.y = cursor.y;
                coords.height = parseFloat(startingCoords.height) + (startingCoords.y - cursor.y);
                replacements.y = this.props.y;
                break;
            case "side-sw":
                coords.x = cursor.x;
                coords.width = parseFloat(startingCoords.width) + (startingCoords.x - cursor.x);
                replacements.x = this.props.x;
                break;
            case "side-nw":
                coords.x = cursor.x;
                coords.y = cursor.y;
                coords.width = parseFloat(startingCoords.width) + (startingCoords.x - cursor.x);
                coords.height = parseFloat(startingCoords.height) + (startingCoords.y - cursor.y)
                replacements.x = this.props.x;
                replacements.y = this.props.y;
                break;
            default:
                console.error(`'this.resize.selectedCorner' contains an invalid value: "${this.resize.selectedCorner}"`);
                coords.width = startingCoords.width;
                coords.height = startingCoords.height;
        }
        replacements.width = -coords.width;
        replacements.height = -coords.height;

        this.correctNegativeSizes(coords, replacements);
        if(keepAspectRatio) {
            this.fixCoordsToKeepAspectRatio(coords);
            this.fixCoordsCoordinatesOnResize(coords, replacements);
        }
        return coords;
    },

    /**
     * Inverts the width and height and changes x and y when the width or height
     * are negative because SVG can't handle negative width and height values.
     * @param {RectangleCoords} coords The calculated position and size of a rectangle
     * @param {RectangleCoords} replacements The values assigned when correction is needed
     * @returns {RectangleCoords}
     */
    correctNegativeSizes(coords, replacements) {
        if (coords.width < 0) {
            coords.x = replacements.x;
            coords.width = replacements.width;
        }
        if (coords.height < 0) {
            coords.y = replacements.y;
            coords.height = replacements.height;
        }
        return coords;
    },

    /**
     * Calculates the new values for the x, y, width and height properties of the rectangle when the width or height are negative.
     * @param {{x: number, y: number}} cursor 
     * @param {RectangleCoords} coords
     * @param {bool} keepAspectRatio
     * @returns {RectangleCoords}
     */
    getReplacementCoordsForNegativeSizesCorrection(cursor, coords, keepAspectRatio) {
        let replacements = {
            x: cursor.x,
            y: cursor.y,
            width: -coords.width,
            height: -coords.height
        };

        if(keepAspectRatio) {
            if(coords.width < 0 && coords.height < 0) {
                // Mouse direction is up left from starting point
                -coords.width >= -coords.height
                    ? replacements.x = coords.x + coords.height
                    : replacements.y = coords.y + coords.width;
            }
            else if (coords.width < 0 && -coords.width >= coords.height) {
                // Mouse direction is down left from starting point
                replacements.x = coords.x + -coords.height;
            }
            else if (coords.height < 0 && -coords.height >= coords.width) {
                // Mouse direction is up right from starting point
                replacements.y = coords.y + -coords.width;
            }
        }

        return replacements;
    },

    /**
     * Checks if the aspect ratio should be kept and corrects the given coordinates if needed.
     * @param {RectangleCoords} coords 
     */
    fixCoordsToKeepAspectRatio(coords) {
        coords.width < coords.height
            ? coords.height = coords.width
            : coords.width = coords.height;        
    },

    /**
     * Check if the aspect ratio should be kept and corrects the given coordinates if needed.
     * @param {{x: number, y: number}} cursor
     * @param {RectangleCoords} coords
     * @param {bool} keepAspectRatio
     * @returns {RectangleCoords}
     */
    getCorrectCords(cursor, coords, keepAspectRatio) {
        const replacements = this.getReplacementCoordsForNegativeSizesCorrection(cursor, coords, keepAspectRatio);
        this.correctNegativeSizes(coords, replacements);
        keepAspectRatio && this.fixCoordsToKeepAspectRatio(coords);
        return coords;
    },

    /**
     * Fixes the coordinates of the rectangle when the aspect ratio is kept.
     * @param {RectangleCoords} coords 
     * @param {RectangleCoords} replacements 
     */
    fixCoordsCoordinatesOnResize(coords, replacements) {
        switch(this.resize.selectedCorner){
            case "side-se":
                if(replacements.height > 0) {
                    const difference = replacements.height - coords.height;
                    coords.y = coords.y + difference;
                }
                if(replacements.width > 0) {
                    const difference = replacements.width - coords.width;
                    coords.x = coords.x + difference;
                }
                break;
            case "side-ne":
                if(replacements.height < 0) {
                    const difference = replacements.height + coords.height;
                    coords.y = coords.y - difference;
                }
                if(replacements.width > 0) {
                    const difference = replacements.width - coords.width;
                    coords.x = coords.x + difference;
                }
                break;
            case "side-sw":
                if(replacements.height > 0) {
                    const difference = replacements.height - coords.height;
                    coords.y = coords.y + difference;
                }
                if(replacements.width < 0) {
                    const difference = replacements.width + coords.width;
                    coords.x = coords.x - difference;
                }
                break;
            case "side-nw":
                if(replacements.height < 0) {
                    const difference = replacements.height + coords.height;
                    coords.y = coords.y - difference;
                }
                if(replacements.width < 0) {
                    const difference = replacements.width + coords.width;
                    coords.x = coords.x - difference;
                }
                break;
        }
    },
};

Object.assign(Rectangle.prototype, rectangularFunctionality);
Object.assign(Image.prototype, rectangularFunctionality);