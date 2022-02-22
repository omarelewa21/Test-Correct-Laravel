import { validHtmlElementKeys } from "./constants.js";

export class htmlElement {
    /**
     *
     * @param {string} type
     * @param {DOMElement} parent
     * @param {propObj} attr
     */
    constructor(type, parent, attr) {
        this.type = type;
        this.element = this.createElement(this.type);
        this.parent = parent;
        this.parent.appendChild(this.element);
        this.setAttributesOnElement(attr);
    }
    setAttributesOnElement(attr) {
        for (const [key, value] of Object.entries(attr)) {
            this.setAttributeOnElementWithValidation(key, value);
        }
    }
    setAttributeOnElementWithValidation(key, value) {
        if (this.attributeIsValid(key)) {
            this.element.setAttribute(key, value);
        } else {
            console.info(
                `Attribute %c${key}%c is invalid for this element (type: ${this.type}) and has been ignored.`,
                "font-style: italic",
                null
            );
        }
    }
    attributeIsValid(key) {
        let attrKeysToLoopOver = validHtmlElementKeys.global.concat(
            validHtmlElementKeys[this.type]
        );
        return attrKeysToLoopOver.some((attr) => {
            return attr === key;
        });
    }
    addEventListener(type, cbFunction) {
        this.element.addEventListener(type, cbFunction);
    }
    focus() {
        this.element.focus();
    }
    deleteElement() {
        this.element.remove();
    }
    createElement(type) {
        return document.createElement(type);
    }
}