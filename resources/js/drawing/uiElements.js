export class UIElements {
    constructor(rootElement) {
        for (var node of rootElement.querySelector("#drawing-tool").parentElement.querySelectorAll('[id]:not([id=""])')) {
            this[this.convertIdToCamelCase(node.id)] = node;
        }
    }
    /**
     * Converts id parameter to camelCase, i.e. removes all dashes and converts the first character after every dash to upper case.
     * @example "example-btn-to-explain" becomes "exampleBtnToExplain"
     * @param {string} id The id string to be converted.
     * @returns The converted id string.
     */
    convertIdToCamelCase(id) {
        return id.replace(
            /^([A-Z])|[\s-_]+(\w)/g,
            function (match, p1, p2, offset) {
                if (p2) return p2.toUpperCase();
                return p1.toLowerCase();
            }
        );
    }
}

export class warningBox {
    /**
     * Constructs a warning box that can be shown and hides automatically after a specified amount of time.
     * @param {string} content Text to be shown in the warning to inform the user.
     * @param {number} timeDisplayed How long the warning should be displayed (in milliseconds).
     */
    constructor(content = "", timeDisplayed = 1000 , rootElement) {
        const parent = rootElement.querySelector("div#canvas-sidebar-container");
        const template = rootElement.querySelector("template#warningbox-template");
        const templateCopy = template.content.cloneNode(true);
        this.box = templateCopy.querySelector("div.warning");
        const textWrapper = this.box.querySelector("div.warning-text");
        textWrapper.append(content);
        parent.appendChild(this.box);
        this.displayTime = timeDisplayed;
    }
    /**
     * Shows the warning box, if no time is specified the time given at initialization is used.
     * @param {number} displayTime How long the warning should be displayed (in milliseconds).
     */
    show(displayTime = this.displayTime) {
        this.box.style.top = "var(--top-value-visible)";
        setTimeout(() => {
            this.box.style.top = "var(--top-value-hidden)";
        }, displayTime);
    }
}