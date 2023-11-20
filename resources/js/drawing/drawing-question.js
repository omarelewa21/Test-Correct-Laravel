import {panParams, resizableSvgShapes, shapePropertiesAvailableToUser, zoomParams, shapeTypeWithRespectiveSvgClass} from "./constants.js";
import * as svgShape from "./svgShape.js";
import {UIElements, warningBox} from "./uiElements.js";
import * as sidebar from "./sidebar.js";
import {v4 as uuidv4} from 'uuid';
import * as UnicodeBase64Polyfill from './unicodeBase64Polyfill.js';

window.UnicodeBase64Polyfill = UnicodeBase64Polyfill;

window.initDrawingQuestion = function (rootElement, isTeacher, isPreview, grid, isOldDrawing) {

    /**
     * @typedef Cursor
     * @type {Object}
     * @property {number} x
     * @property {number} y
     */
    /**
     * @typedef propObj
     * @type {Object.<string, string|number>}
     */
    /**
     * @typedef ELOptions
     * @type {Object.<string, boolean|AbortSignal>|boolean}
     */
    /**
     * @typedef ELEvent
     * @type {Object.<string, Function|ELOptions>}
     */
    /**
     * @typedef ELEvents
     * @type {Object.<string, ELEvent>}
     */
    /**
     * @typedef EventListenerSettings
     * @type {Object.<string, HTMLElement|ELEvents>}
     */
    /**
     * @typedef Element
     * @type {HTMLElement|SVGElement}
     */

    /**
     * Global Object containing all DOM Elements on the page that have an id attribute.
     * The key is the id value converted to camelCase, the value being the DOM Element itself.
     */
    let UI = new UIElements(rootElement);

    /**
     * Global Object containing some parameters that don't belong in Canvas.
     */
    let drawingApp = {
        params: {
            currentTool: "drag",
            boldText: false,
            endmarkerType: "no-endmarker",
            gridSize: 1,
            spacebarPressed: false,
            root: rootElement,
            isTeacher: isTeacher && !isPreview,
            isPreview: isPreview,
            hiddenLayersCount: 0
        },
        firstInit: true,
        warnings: {},
        explainer: null,
        livewireComponent: null,
        init() {
            if (this.firstInit) {
                this.bindEventListeners(eventListenerSettings);
            }

            const drawingApp = this
            const pollingFunction = setInterval(function () {
                if (UI.svgCanvas.getBoundingClientRect().width !== 0) {
                    setCorrectPopupHeight();
                    calculateCanvasBounds();
                    updateClosedSidebarWidth();

                    if (drawingApp.firstInit) {
                        makeGrid();
                        updateMidPoint();
                    }
                    if (grid && grid !== '0') {
                        drawGridBackground();
                    }
                    processGridToggleChange();
                    clearLayers();
                    retrieveSavedDrawingData();

                    Canvas.setCurrentLayer(Canvas.params.currentLayer);

                    drawingApp.firstInit = false;
                    clearInterval(pollingFunction);
                    if (Canvas.params.initialZoomLevel !== 1) {
                        updateZoomInputValue(Canvas.params.initialZoomLevel);
                        zoom(Canvas.params.initialZoomLevel);
                        panDrawingCenterToScreenCenter();
                    }
                }
            });

            setCorrectZIndex();
            setCursorTypeAccordingToCurrentType();
            updateAllOpacitySliderColor();

            if (!this.isTeacher()) {
                Canvas.layers.question.lock();
                Canvas.layers.question.sidebar.style.display = "none";
            }

            this.warnings = {
                whenAnyToolButDragSelected: new warningBox(
                    UI.warningboxTemplate.dataset.text,
                    5000,
                    rootElement
                ),
            };
            if (!this.explainer) {
                const layerTemplate = rootElement.querySelector("#layer-group-template");
                const templateCopy = layerTemplate.content.cloneNode(true);
                this.explainer = templateCopy.querySelector(".explainer");
            }

            this.livewireComponent = getClosestLivewireComponentByAttribute(rootElement, 'questionComponent')
        },
        convertCanvas2DomCoordinates(coordinates) {
            const matrix = Canvas.params.domMatrix;
            return {
                x: coordinates.x * matrix.a + matrix.e,
                y: coordinates.y * matrix.d + matrix.f,
            };
        },
        /**
         * Adds event listeners with the parameters specified in the settings.
         * @param {EventListenerSettings[]} settings
         * @param {*} thisArg Specific this context when needed.
         */
        bindEventListeners(settings, thisArg = null) {
            settings.forEach((eventListener) => {

                for (const [type, params] of Object.entries(eventListener.events)) {

                    const types = type.split(" ");

                    types.forEach((type) => {
                        const callbackFunction = (thisArg)
                            ? params.callback.bind(thisArg)
                            : params.callback;

                        if (eventListener.elements && Array.isArray(eventListener.elements)) {
                            eventListener.elements.forEach((elem) => {
                                this.bindToElement(elem, type, callbackFunction, params.options);
                            });
                        } else {
                            this.bindToElement(eventListener.element, type, callbackFunction, params.options);
                        }
                    });
                }
            });
        },
        bindToElement(elem, type, func, options) {
            if (elem) {
                elem.addEventListener(type, (evt) => {
                    func(evt);
                }, options);
            }
        },
        currentToolIs(toolname) {
            return this.params.currentTool === toolname;
        },
        toolAndShapeOfSameType(shape) {
            return this.getElementShapeType(shape) === this.params.currentTool;
        },
        getElementShapeType(shape) {
            return shape.id.split("-")[0];
        },
        isTeacher() {
            return this.params.isTeacher;
        }
    };

    /**
     * Global Object containing all parameters, Shapes and corresponding sidebarEntries.
     */
    let Canvas = (function () {
        let Obj = {
            params: {
                cursorPosition: {x: 0, y: 0},
                cursorPositionMousedown: {x: 0, y: 0},
                imageTracker: [],
                touchmoving: false,
                currentLayer: "question",
                focusedShape: null,
                bounds: {},
                editingTextInZone: false,
                draw: {
                    newShape: null,
                    shapeCountForEachType: {
                        rect: 0,
                        circle: 0,
                        line: 0,
                        text: 0,
                        image: 0,
                        path: 0,
                        freehand: 0
                    },
                },
                drag: {
                    enabled: false,
                    translateOfSvgShape: null,
                    offsetCursorToMidPoint: null,
                },
                resize: {
                    enabled: false,
                },
                pan: {
                    enabled: false,
                    startCoordinates: {x: 0, y: 0},
                },
                domMatrix: new DOMMatrix(),
                zoomFactor: 1,
                initialZoomLevel: 1,
            },
            UI: UI,
            element: UI.svgCanvas,
            layers: {},
            dragging() {
                return this.params.drag.enabled
            },
            panning() {
                return this.params.pan.enabled
            },
            drawing() {
                return this.params.draw.newShape
            },
            resizing() {
                return this.params.resize.enabled
            },
            getLayerDomElementsByLayerId: function (layerId) {
                const layer = rootElement.querySelector(`#${layerId}`);
                const layerHeader = rootElement.querySelector(`[data-layer="${layerId}"]`).closest('.header');
                const layerSvg = rootElement.querySelector(`#svg-${layerId}`);
                return {layer, layerHeader, layerSvg}
            },
            removeHighlightFromLayer: function (layerId) {
                const {layer, layerHeader, layerSvg} = this.getLayerDomElementsByLayerId(layerId);

                layer.classList.remove("highlight");
                layer.querySelectorAll('.selected').forEach((item) => item.classList.remove('selected'));
                layerSvg.querySelectorAll('.selected').forEach((item) => item.classList.remove('selected'));
                layerHeader.classList.remove("highlight");
            },
            addHighlightToLayer: function (layerId) {
                const {layer, layerHeader} = this.getLayerDomElementsByLayerId(layerId);

                layer.classList.add("highlight");
                layerHeader.classList.add("highlight");
            },
            setCurrentLayer(newCurrentLayerID) {
                this.removeHighlightFromLayer(this.layerKey2ID(this.params.currentLayer));
                this.addHighlightToLayer(this.layerKey2ID(newCurrentLayerID));

                Canvas.params.currentLayer = newCurrentLayerID;
            },
            getEnabledLayers() {
                return Object.values(this.layers).filter((layer) => {
                    return layer.params.enabled
                });
            },
            layerID2Key(id) {
                return (id.startsWith("svg-") ? id.substring(4, id.lastIndexOf("-")) : id.substring(0, id.lastIndexOf("-")));
            },
            layerKey2ID(key) {
                return `${key}-group`;
            },
            setFocusedShape(shape) {
                this.params.focusedShape = shape;
            },
            getFocusedShape() {
                return this.params.focusedShape;
            },
            data: {
                question: "",
                answer: "",
            },
            makeLayers() {
                this.layers = {
                    "question": new sidebar.Layer({
                        name: UI.translationTemplate.dataset.question,
                        id: "question-group",
                        enabled: true,
                    }, drawingApp, this),
                    "answer": new sidebar.Layer({
                        name: UI.translationTemplate.dataset.answer,
                        id: "answer-group",
                        enabled: false,
                    }, drawingApp, this),
                    "grid": {
                        svg: UI.svgGridGroup,
                        params: {
                            locked: true,
                            hidden: true,
                        },
                    },
                }
            },
            deleteObject(object) {
                const objectId = object.id
                const layer = object.svgShape.isQuestionLayer() ? 'question' : 'answer';
                object.remove();

                delete this.layers[layer].shapes[objectId];
            },
            cleanShapeCount() {
                this.params.draw.shapeCountForEachType = {
                    rect: 0,
                    circle: 0,
                    line: 0,
                    text: 0,
                    image: 0,
                    path: 0,
                    freehand: 0
                }
            },
            initCanvas() {
                this.cleanShapeCount();
                this.makeLayers();
            },
            unhighlightShapes() {
                if (Canvas.params.highlightedShape) {
                    Canvas.params.highlightedShape.svg.unhighlight();
                    Canvas.params.highlightedShape = null;
                }
            },
            /**
             * @param {*} g element 
             * @returns object containing element svg (svgShape class) and its sidebar (Entry class)
             */
            getShapeDataObject(shape) {
                return Canvas.layers[Canvas.layerID2Key(shape.parentElement.id)].shapes[shape.id];
            }
        }

        Obj.initCanvas();
        return Obj;
    })();


    function clearLayers() {
        Canvas.layers.question.clearSidebar(false);
        Canvas.layers.answer.clearSidebar(false);
        Canvas.cleanShapeCount();
        updateGrid();
    }

    /******************************
     * EVENT LISTENERS DEFINITION *
     ******************************/

    const eventListenerSettings = [
        {
            element: window,
            events: {
                "resize": {
                    callback: () => {
                        updateClosedSidebarWidth();
                        updateGrid();
                        setCorrectPopupHeight();
                    },
                },
                "keydown": {
                    callback: (evt) => {
                        switch (evt.code) {
                            case "Space":
                                if (!drawingApp.params.spacebarPressed) {
                                    drawingApp.params.spacebarPressed = true;
                                    startPan();
                                }
                                break;
                            /* Zoom in on Ctrl+'+' */
                            case "Equal":
                                if (evt.ctrlKey) {
                                    evt.preventDefault();
                                    zoomInOneStep();
                                }
                                break;
                            /* Zoom out on Ctrl+'-' */
                            case "Minus":
                                if (evt.ctrlKey) {
                                    evt.preventDefault();
                                    zoomOutOneStep();
                                }
                                break;
                            /* Restore zoom to 100% on Ctrl+'0' */
                            case "Digit0":
                                if (evt.ctrlKey) {
                                    evt.preventDefault();
                                    zoom();
                                    updateZoomInputValue();
                                }
                                break;
                            case "Delete":
                                if (Canvas.params.focusedShape) {
                                    if (drawingApp.currentToolIs("drag")) {
                                        Canvas.params.focusedShape.getSidebarEntry().remove();
                                    }
                                }
                                break;
                            default:
                        }
                    }
                },
                "keyup": {
                    callback: (evt) => {
                        if (evt.code === "Space" && drawingApp.params.spacebarPressed) {
                            drawingApp.params.spacebarPressed = false;
                            stopPan();
                        }
                    }
                },
                "paste": {
                    callback: (evt) => {
                        isTeacher && UI.canvas.matches(':hover') && handleImagePaste(evt);
                    }
                }
            }
        },
        {
            element: UI.drawingTool,
            events: {
                "mouseup touchend mouseleave touchcancel": {
                    callback: cursorStop,
                    options: {passive: false},
                },
                "wheel": {
                    callback: (evt) => {
                        if (evt.ctrlKey) {
                            evt.preventDefault();
                        }
                    },
                    options: {passive: false},
                },
                "mousedown touchstart": {
                    callback: () => {
                        Canvas.unhighlightShapes()
                        // if (Canvas.params.highlightedShape) {
                        //     Canvas.params.highlightedShape.svg.unhighlight();
                        //     Canvas.params.highlightedShape = null;
                        // }
                    }
                }
            }
        },
        {
            element: UI.svgCanvas,
            events: {
                "mousedown touchstart": {
                    callback: cursorStart,
                    options: {passive: false},
                },
                "mousemove touchmove": {
                    callback: cursorMove,
                    options: {passive: false},
                },
                "wheel": {
                    callback: (evt) => {
                        evt.preventDefault();
                        const direction = -Math.sign(evt.deltaY);
                        if (evt.ctrlKey) {
                            /* Zoom on Ctrl+Scroll */
                            zoomOneStepToCursor(-direction);
                        } else if (evt.shiftKey) {
                            /* Pan horizontal on Shift+Scroll */
                            panHorizontalOneStep(direction);
                        } else {
                            /* Pan vertical on Scroll */
                            panVerticalOneStep(direction);
                        }
                    },
                    options: {passive: false},
                },
                "click": {
                    callback: (evt) => {
                        if (!movedDuringClick(evt)) {
                            handleShapeSelection(evt);
                        }
                    }
                },
                "touchend touchcancel": {
                    callback: (evt) => {
                        if (!Canvas.params.touchmoving) handleShapeSelection(evt);
                        Canvas.params.touchmoving = false;
                    }
                }
            }
        },
        {
            elements: [...rootElement.querySelectorAll("[data-button-group=tool]")],
            events: {
                "click": {callback: processToolChange},
            }
        },
        {
            elements: [...rootElement.querySelectorAll("[data-button-group=endmarker-type]")],
            events: {
                "click": {
                    callback: processEndmarkerTypeChange,
                },
            }
        },
        {
            element: UI.boldToggle,
            events: {
                "change": {
                    callback: (evt) => {
                        drawingApp.params.boldText = evt.target.checked;
                        if(evt.target.checked){
                            UI.boldToggleButton.classList.add('active');
                        } else {
                            UI.boldToggleButton.classList.remove('active');
                        }
                        editShape('updateBoldText');
                    }
                }
            }
        },
        {
            element: UI.elemOpacityNumber,
            events: {
                "input": {
                    callback: () => {
                        if(valueWithinBounds(UI.elemOpacityNumber)) {
                            updateElemOpacityRangeInput();
                            editShape('updateOpacity');
                        }
                    },
                }
            }
        },
        {
            element: UI.elemOpacityRange,
            events: {
                "input": {
                    callback: () => {
                        if(valueWithinBounds(UI.elemOpacityRange)) {
                            updateElemOpacityNumberInput();
                            editShape('updateOpacity');
                        }
                    }
                },
                "focus": {
                    callback: () => {
                        UI.elemOpacityNumber.classList.add("active");
                    },
                },
                "blur": {
                    callback: () => {
                        UI.elemOpacityNumber.classList.remove("active");
                    },
                },
            }
        },
        {
            element: UI.textColor,
            events: {
                "input": {
                    callback: () => {
                        setSliderColor(UI.elemOpacityRange, UI.textColor.value);
                        editShape('updateTextColor');
                    }
                }
            }
        },
        {
            elements: [...rootElement.querySelectorAll('[id*="stroke-width"]')],
            events: {
                "input": {
                    callback: (evt) => valueWithinBounds(evt.currentTarget) && editShape('updateStrokeWidth')
                },
                "blur": {
                    callback: (evt) => toggleDisableButtonStates(evt.currentTarget, 'stroke-width')
                }
            }
        },
        {
            elements: [...rootElement.querySelectorAll('[id*="decr-stroke-width"]')],
            events: {
                "click": {
                    callback: (evt) => {
                        const input = evt.currentTarget.parentElement.querySelector('input[type=number]');
                        input.stepDown();
                        toggleDisableButtonStates(evt.currentTarget, 'stroke-width');
                        editShape('updateStrokeWidth');
                    },
                },
                "focus": {
                    callback: (evt) => evt.currentTarget.classList.add("active")
                },
                "blur": {
                    callback: (evt) => evt.currentTarget.classList.remove("active")
                },
            }
        },
        {
            elements: [...rootElement.querySelectorAll('[id*="incr-stroke-width"]')],
            events: {
                "click": {
                    callback: (evt) => {
                        const input = evt.currentTarget.parentElement.querySelector('input[type=number]');
                        input.stepUp();
                        toggleDisableButtonStates(evt.currentTarget, 'stroke-width');
                        editShape('updateStrokeWidth');
                    },
                },
                "focus": {
                    callback: (evt) => evt.currentTarget.classList.add("active")
                },
                "blur": {
                    callback: (evt) => evt.currentTarget.classList.remove("active")
                },
            }
        },
        {
            elements: [...rootElement.querySelectorAll('[id*="stroke-color"]')],
            events: {
                "input": {
                    callback: () => editShape('updateStrokeColor')
                }
            }
        },
        {
            elements: [...rootElement.querySelectorAll('[id*="pen-width"]')],
            events: {
                "input": {
                    callback: (evt) => {
                        valueWithinBounds(evt.currentTarget) && editShape('updatePenWidth');
                    }
                },
                "blur": {
                    callback: (evt) => {
                        toggleDisableButtonStates(evt.currentTarget, 'pen-width');
                    }
                }
            }
        },
        {
            elements: [...rootElement.querySelectorAll('[id*="decr-pen-width"]')],
            events: {
                "click": {
                    callback: (evt) => {
                        const input = evt.currentTarget.parentElement.querySelector('input[type=number]');
                        input.stepDown();
                        toggleDisableButtonStates(evt.currentTarget, 'pen-width');
                        editShape('updatePenWidth');
                    },
                },
                "focus": {
                    callback: (evt) => {
                        evt.currentTarget.classList.add("active");
                    },
                },
                "blur": {
                    callback: (evt) => {
                        evt.currentTarget.classList.remove("active");
                    },
                },
            }
        },
        {
            elements: [...rootElement.querySelectorAll('[id*="incr-pen-width"]')],
            events: {
                "click": {
                    callback: (evt) => {
                        const input = evt.currentTarget.parentElement.querySelector('input[type=number]');
                        input.stepUp();
                        toggleDisableButtonStates(evt.currentTarget, 'pen-width');
                        editShape('updatePenWidth');
                    },
                },
                "focus": {
                    callback: (evt) => {
                        evt.currentTarget.classList.add("active");
                    },
                },
                "blur": {
                    callback: (evt) => {
                        evt.currentTarget.classList.remove("active");
                    },
                },
            }
        },
        {
            element: UI.textSize,
            events: {
                "input": {
                    callback: () => valueWithinBounds(UI.textSize) && editShape('updateTextSize')
                },
                "blur": {
                    callback: () => {
                        handleTextSizeButtonStates()
                    },
                },
            }
        },
        {
            element: UI.decrTextSize,
            events: {
                "click": {
                    callback: () => {
                        UI.textSize.stepDown();
                        handleTextSizeButtonStates();
                        editShape('updateTextSize');
                    },
                },
                "focus": {
                    callback: () => {
                        UI.textSize.classList.add("active");
                    },
                },
                "blur": {
                    callback: () => {
                        UI.textSize.classList.remove("active");
                    },
                },
            }
        },
        {
            element: UI.incrTextSize,
            events: {
                "click": {
                    callback: () => {
                        UI.textSize.stepUp();
                        handleTextSizeButtonStates();
                        editShape('updateTextSize');
                    },
                },
                "focus": {
                    callback: () => {
                        UI.textSize.classList.add("active");
                    },
                },
                "blur": {
                    callback: () => {
                        UI.textSize.classList.remove("active");
                    },
                },
            }
        },
        {
            elements: [...rootElement.querySelectorAll('[id*="fill-color"]')],
            events: {
                "input": {
                    callback: (evt) => {
                        updateOpacitySliderColor(evt.currentTarget, 'fill');
                        editShape('updateFillColor');
                    }
                }
            }
        },
        {
            elements: [...rootElement.querySelectorAll('[id*="fill-opacity-number"]')],
            events: {
                "input": {
                    callback: (evt) => {
                        if(valueWithinBounds(evt.currentTarget)) {
                            updateFillOpacityRangeInput(evt.currentTarget, 'fill');
                            editShape('updateOpacity');
                        }
                    },
                }
            }
        },
        {
            elements: [...rootElement.querySelectorAll('[id*="fill-opacity-range"]')],
            events: {
                "input": {
                    callback: (evt) => {
                        if(valueWithinBounds(evt.currentTarget)) {
                            updateFillOpacityNumberInput(evt.currentTarget, 'fill');
                            editShape('updateOpacity');
                        }
                    }
                },
                "focus": {
                    callback: (evt) => {
                        evt.currentTarget.classList.add("active");
                    },
                },
                "blur": {
                    callback: (evt) => {
                        evt.currentTarget.classList.remove("active");
                    },
                },
            }
        },
        {
            elements: [...rootElement.querySelectorAll('[id*="pen-color"]')],
            events: {
                "input": {
                    callback: () => editShape('updatePenColor')
                }
            }
        },
        {
            element: UI.endmarkerTypeWrapper,
            events: {
                "click": {
                    callback: () => editShape('editOwnMarkerForThisShape')
                }
            }
        },
        {
            element: UI.decrZoom,
            events: {
                "click": {
                    callback: () => {
                        const currentFactor = Canvas.params.zoomFactor,
                            newFactor = checkZoomFactorBounds(currentFactor - zoomParams.STEP);
                        updateZoomInputValue(newFactor);
                        zoom(newFactor);
                    }
                }
            }
        },
        {
            element: UI.incrZoom,
            events: {
                "click": {
                    callback: () => {
                        const currentFactor = Canvas.params.zoomFactor,
                            newFactor = checkZoomFactorBounds(currentFactor + zoomParams.STEP);
                        updateZoomInputValue(newFactor);
                        zoom(newFactor);
                    }
                }
            }
        },
        {
            elements: Canvas.getEnabledLayers().map((layer) => {
                layer.header
            }),
            events: {
                "mousedown touchstart": {
                    callback: (evt) => {
                        const targetHeader = evt.target;
                        const newCurrentLayerID = targetHeader.querySelector('.header-title').dataset.layer;
                        this.Canvas.setCurrentLayer(this.Canvas.layerID2Key(newCurrentLayerID));
                    }
                },
            }
        },
        {
            element: UI.submitBtn,
            events: {
                "click": {
                    callback() {
                        if (hasHiddenLayers()) {
                            toggleSaveConfirm();
                        } else if (hasNoAnswerObjects()) {
                            toggleSaveNoAnswersConfirm();
                        } else {
                            submitDrawingData();
                            closeDrawingTool();
                        }
                    },
                }
            }
        },
        {
            element: UI.exitBtn,
            events: {
                "click": {
                    callback: handleCloseByExit,
                }
            }
        },
        {
            element: UI.closeCancelBtn,
            events: {
                "click": {
                    callback: handleCloseByExit,
                }
            }
        },
        {
            element: UI.closeConfirmBtn,
            events: {
                "click": {
                    callback: handleCloseByExit,
                }
            }
        },
        {
            element: UI.deleteCancelBtn,
            events: {
                "click": {
                    callback() {
                        UI.deleteConfirm.classList.toggle('open');
                    },
                }
            }
        },
        {
            element: UI.deleteConfirmBtn,
            events: {
                "click": {
                    callback() {
                        Canvas.deleteObject(drawingApp.params.deleteSubject);
                        UI.deleteConfirm.classList.toggle('open');
                    },
                }
            }
        },
        {
            element: UI.saveCancelBtn,
            events: {
                "click": {
                    callback() {
                        toggleSaveConfirm();
                    },
                }
            }
        },
        {
            element: UI.saveConfirmBtn,
            events: {
                "click": {
                    callback() {
                        handleHiddenLayers();
                        toggleSaveConfirm();
                        if (hasNoAnswerObjects()) {
                            toggleSaveNoAnswersConfirm();
                        } else {
                            submitDrawingData();
                            closeDrawingTool();
                        }
                    },
                }
            }
        },
        {
            element: UI.saveNoAnswersCancelBtn,
            events: {
                "click": {
                    callback() {
                        toggleSaveNoAnswersConfirm();
                    },
                }
            }
        },
        {
            element: UI.saveNoAnswersConfirmBtn,
            events: {
                "click": {
                    callback() {
                        submitDrawingData();
                        closeDrawingTool();
                        toggleSaveNoAnswersConfirm();
                    },
                }
            }
        },
        {
            element: UI.gridToggle,
            events: {
                "change": {
                    callback() {
                        processGridToggleChange()
                    },
                }
            }
        },
        {
            element: UI.gridSize,
            events: {
                "input": {
                    callback: updateGrid,
                },
                "blur": {
                    callback: () => {
                        handleGridSizeButtonStates();
                    }
                }
            }
        },
        {
            element: UI.decrGridSize,
            events: {
                "click": {
                    callback: () => {
                        UI.gridSize.stepDown();
                        handleGridSizeButtonStates();
                        updateGrid();
                    },
                },
                "focus": {
                    callback: () => {
                        UI.gridSize.classList.add("active");
                    },
                },
                "blur": {
                    callback: () => {
                        UI.gridSize.classList.remove("active");
                    },
                },
            }
        },
        {
            element: UI.incrGridSize,
            events: {
                "click": {
                    callback: () => {
                        UI.gridSize.stepUp();
                        handleGridSizeButtonStates();
                        updateGrid();
                    },
                },
                "focus": {
                    callback: () => {
                        UI.gridSize.classList.add("active");
                    },
                },
                "blur": {
                    callback: () => {
                        UI.gridSize.classList.remove("active");
                    },
                },
            }
        },
        {
            element: UI.centerBtn,
            events: {
                "click": {
                    callback: () => {
                        panDrawingCenterToScreenCenter();
                    },
                },
            }
        }
    ];

    if (drawingApp.isTeacher()) {
        eventListenerSettings.push(
            {
                element: UI.imgUpload,
                events: {
                    "change": {
                        callback: processUploadedImages,
                    }
                }
            }
        );
    }

    function encodeSvgLayersAsBase64Strings() {
        return {
            question: UnicodeBase64Polyfill.encode(Canvas.layers.question.svg.innerHTML),
            answer: UnicodeBase64Polyfill.encode(Canvas.layers.answer.svg.innerHTML),
            grid: UnicodeBase64Polyfill.encode(Canvas.layers.grid.svg.innerHTML)
        };
    }

    function retrieveSavedDrawingData() {
        const data = Canvas.data;
        if (data.question) {
            decodeSvgLayerFromBase64String({
                name: "question",
                data: data.question,
            });
        }
        if (data.answer) {
            decodeSvgLayerFromBase64String({
                name: "answer",
                data: data.answer,
            });
            Canvas.layers.answer.enable();
        }
        if (data.question || data.answer) {
            //Disabled as it causes unnecessary zooming
            fitDrawingToScreen();
        }
    }

    function decodeSvgLayerFromBase64String(layerData) {
        if (layerData.data.startsWith("data:image/png;base64")) {
            // made with old tool, load as image
            const parentID = layerData.name;
            const shapeID = "image-1";
            const newShape = makeNewSvgShapeWithSidebarEntry(
                "image",
                {
                    group: {},
                    main: {
                        href: layerData.data,
                    },
                },
                parentID,
            );
            Canvas.layers[parentID].shapes[shapeID] = newShape;
            newShape.svg.addHighlightEvents();
            newShape.svg.updateBorderElement();
            newShape.svg.updateCornerElements();
        } else {
            const decodedString = UnicodeBase64Polyfill.decode(layerData.data);
            UI.svgLayerToRender.innerHTML = decodedString;
            renderShapesFromSvgLayerString(layerData.name);
        }
    }

    function fitDrawingToScreen() {
        // panDrawingCenterToScreenCenter();

        while (!drawingFitsScreen() && canZoomOut()) {
            zoomOutOneStep();
        }
    }

    function canZoomOut() {
        return (Canvas.params.zoomFactor > zoomParams.MIN);
    }

    function panDrawingCenterToScreenCenter() {
        let systemGridToggle = false;
        if (!UI.gridToggle.checked) {
            UI.gridToggle.checked = true
            processGridToggleChange()
            systemGridToggle = true;
        }
        let systemQuestionHide = false
        if (!Canvas.layers.question.isHidden()) {
            Canvas.layers.question.hide()
            systemQuestionHide = true;
        }
        let systemAnswerHide = false
        if (!Canvas.layers.answer.isHidden()) {
            Canvas.layers.answer.hide()
            systemAnswerHide = true;
        }

        const bbox = UI.svgPanZoomGroup.getBBox({fill: true, stroke: true, markers: true});
        const centerDrawingToOrigin = {
            dx: parseInt(bbox.x + (bbox.width / 2)),
            dy: parseInt(bbox.y + (bbox.height / 2)),
        };

        pan(centerDrawingToOrigin);
        if (centerDrawingToOrigin.dy !== 0 || centerDrawingToOrigin.dx !== 0) {
            if (UI.gridToggle.checked) {
                panDrawingCenterToScreenCenter();
            }
        }

        if (systemGridToggle) {
            UI.gridToggle.checked = false
            processGridToggleChange()
        }
        if (systemQuestionHide) {
            Canvas.layers.question.unhide()
        }
        if (systemAnswerHide) {
            Canvas.layers.answer.unhide()
        }
    }

    function drawingFitsScreen() {
        const bbox = UI.svgPanZoomGroup.getBBox({fill: true, stroke: true, markers: true});
        const screenBounds = Canvas.params.bounds;
        return !(bbox.x < screenBounds.left
            || bbox.y < screenBounds.top
            || bbox.width > screenBounds.width
            || bbox.height > screenBounds.height);
    }

    function renderShapesFromSvgLayerString(layerName) {
        const content = UI.svgLayerToRender.content;
        for (const groupElement of content.children) {
            const mainElement = groupElement.querySelector(".main");
            if (mainElement === null) continue;
            const props = {
                group: copyAllAttributesFromElementToObject(groupElement),
                main: copyAllAttributesFromElementToObject(mainElement),
            };
            const shapeID = groupElement.id;
            let shapeType = shapeID.substring(0, shapeID.indexOf("-"));
            if(shapeType === 'ellipse') shapeType = 'circle';
            let newShape = makeNewSvgShapeWithSidebarEntry(
                shapeType,
                props,
                layerName,
                true,
                !(!drawingApp.isTeacher() && layerName === "question")
            )
            // Convert old dragging system (using SVGTransforms)
            // to new dragging system (all done with the SVG attributes of the element itself)
            if(shapeIsPathShape(newShape) && pathShapeUsesAbsoluteCoords(newShape))
                newShape = letPathShapesUseRelativeCoords(newShape);
            newShape = convertDragTransforms(newShape);

            Canvas.layers[layerName].shapes[shapeID] = newShape;
            if (isOldDrawing && layerName === "question") {
                fitDrawingToScreen();
            }
            newShape.svg.addHighlightEvents();
        }
        UI.svgLayerToRender.innerHTML = "";
    }

    function shapeIsPathShape(shape) {
        return shape.svg.type === 'path';
    }

    function pathShapeUsesAbsoluteCoords(shape) {
        return shape.svg.mainElement.getDAttribute().contains("L");
    }

    function letPathShapesUseRelativeCoords(shape) {
        const mainElement = shape.svg.mainElement;
        const oldDValue = convertDStringToArray(mainElement.getDAttribute());

        let newDValue = [],
            startingPoint = oldDValue.shift();
        oldDValue.reduce((previousCommand, currentCommand) => {
            newDValue.push(convertCommandFromAbsoluteToRelative(currentCommand, previousCommand));
            return currentCommand;
        }, startingPoint);
        newDValue.unshift(startingPoint);
        mainElement.setD(newDValue.map((command) => `${command[0]} ${command[1].join(",")}`).join(" "));
        return shape;
    }

    /**
     * @typedef PathDStruct
     * @type {Array.<Array.<String, Array.<Number>>>}
     */

    /**
     * @example 'M -58.6,38.38 L 16.7,56.93' becomes [['M',[-58.6, 38.38]],['L',[16.7,56.93]]]
     * @param dValue
     * @returns {PathDStruct}
     */
    function convertDStringToArray(dValue) {
        const commandMatcher = /([A-Z])(\s)([\-0-9.,])+/g; // Example: 'M -58.6,38.38'
        const coordValueMatcher = /([\-.0-9])+/g; // Example: '-58.6'
        return dValue
            .match(commandMatcher)
            .map((command) => [
                    command[0],
                    command.match(coordValueMatcher).map(Number)
                ]);
    }

    /**
     * @param {PathDStruct} command
     * @param {PathDStruct} previousCommand
     * @returns {PathDStruct}
     */
    function convertCommandFromAbsoluteToRelative(command, previousCommand) {
        return [
            command[0].toLowerCase(),
            convertCoordsFromAbsoluteToRelative(command[1], previousCommand[1])
        ];
    }

    function convertCoordsFromAbsoluteToRelative(currentCoords, previousCoords) {
        return [currentCoords[0] - previousCoords[0], currentCoords[1] - previousCoords[1]];
    }

    function convertDragTransforms(shape) {
        const shapeGroup = shape.svg.shapeGroup;
        const distanceToMove = retrieveTranslateValuesOfElement(shapeGroup.element);
        shape.svg.mainElement.move(distanceToMove);
        shapeGroup.element.removeAttribute("transform");
        return shape;
    }

    function retrieveTranslateValuesOfElement(element) {
        if(!elementHasTransforms(element))
            return {
                dx: 0,
                dy: 0,
            };
        const translationMatrix = element.transform.baseVal[0].matrix;
        return {
            dx: translationMatrix.e,
            dy: translationMatrix.f,
        };
    }

    function elementHasTransforms(element) {
        return element.transform.baseVal.length
    }

    function copyAllAttributesFromElementToObject(element) {
        const attributes = {};
        for (const attr of element.attributes) {
            attributes[attr.name] = attr.value;
        }

        if (element.nodeName === "TEXT" && !attributes["data-textcontent"]) {
            attributes["data-textcontent"] = encodeURI(element.textContent);
        }
        return attributes;
    }

    function calculateCanvasBounds() {
        const matrix = Canvas.params.domMatrix;
        const height = UI.svgCanvas.clientHeight,
            width = UI.svgCanvas.clientWidth;
        let bounds = {
            top: -(matrix.f),
            bottom: height - matrix.f,
            height: height,
            left: -(matrix.e),
            right: width - matrix.e,
            width: width,
            cx: -matrix.e + (width / 2),
            cy: -matrix.f + (height / 2),
        };
        for (const [key, value] of Object.entries(bounds)) {
            Canvas.params.bounds[key] = value / Canvas.params.zoomFactor;
        }
    }

    function updateMidPoint() {
        const midPoint = {
            dx: UI.svgCanvas.clientWidth / 2,
            dy: UI.svgCanvas.clientHeight / 2,
        };
        pan(midPoint);
    }

    function updateClosedSidebarWidth() {
        const currentOpenSidebarWidth = UI.sidebar.clientWidth;
        const closedSidebarWidthString = getRootCSSProperty(
            "--closed-sidebar-width"
        );
        const closedSidebarWidth =
            closedSidebarWidthString.substr(0,
                closedSidebarWidthString.length - 2
            );
        setRootCSSProperty(
            "--closed-sidebar-right-value",
            `${closedSidebarWidth - currentOpenSidebarWidth}px`
        );
    }

    function setCorrectZIndex() {
        let popUpParentZIndex = UI.drawingTool.parentElement.style.zIndex;
        setRootCSSProperty("--pop-up-z-index", popUpParentZIndex);
    }

    function setCorrectPopupHeight() {
        // UI.drawingTool.style.height = Math.round(window.innerHeight * 0.95) + "px";
    }

    function cleanedBase64EncodedStrings() {
        return {
            question: UnicodeBase64Polyfill.encode(clearImageSources(Canvas.layers.question.svg)),
            answer: UnicodeBase64Polyfill.encode(clearImageSources(Canvas.layers.answer.svg))
        };
    }

    function clearImageSources(layer) {
        const hrefsToReplace = [];
        layer.querySelectorAll('image')?.forEach((image) => {
            hrefsToReplace.push(image.getAttribute('href'));
        });
        layer = layer.innerHTML;
        hrefsToReplace.forEach((href) => layer = layer.replace(href, ''));

        return layer;
    }

    async function submitDrawingData() {
        if (drawingApp.params.isPreview) return;
        prepareShapesForSubmission();

        const b64Strings = encodeSvgLayersAsBase64Strings();
        const grid = (Canvas.layers.grid.params.hidden) ? "0.00" : drawingApp.params.gridSize.toString();

        const panGroupSize = getPanGroupSize();

        const livewireComponent = getClosestLivewireComponentByAttribute(rootElement, 'questionComponent');

        const cleanedSvg = cleanedBase64EncodedStrings();

        fitDrawingToScreen();

        livewireComponent.handleUpdateDrawingData({
            svg_answer: b64Strings.answer,
            svg_question: b64Strings.question,
            svg_grid: b64Strings.grid,
            grid_size: grid,
            svg_zoom_group: panGroupSize,
            png_question_preview_string: await getPNGQuestionPreviewStringFromSVG(panGroupSize),
            png_correction_model_string: await getPNGCorrectionModelStringFromSVG(panGroupSize),
            cleaned_question_svg: cleanedSvg.question,
            cleaned_answer_svg: cleanedSvg.answer
        });
    }

    async function getPNGCorrectionModelStringFromSVG(panGroupSize) {
        const svg = UI.svgCanvas.cloneNode(true);

        svg.querySelector('#svg-answer-group').setAttribute('style', '');
        svg.querySelector('#svg-question-group').setAttribute('style', '');

        return getPNGStringFromSVG(svg, panGroupSize);
    }

    async function getPNGQuestionPreviewStringFromSVG(panGroupSize) {
        const svg = UI.svgCanvas.cloneNode(true);
        svg.querySelector('#svg-answer-group').remove();
        return getPNGStringFromSVG(svg, panGroupSize);
    }

    function getDataUrlFromCanvasByImage(image) {
        const canvas = document.createElement("canvas");
        canvas.setAttribute('width', image.width);
        canvas.setAttribute('height', image.height);

        return new Promise((resolve) => {
            image.onload = () => {
                const ctx = canvas.getContext("2d");
                ctx.drawImage(image, 0, 0, image.width, image.height);
                resolve(canvas.toDataURL());
            };
        });
    }

    async function getPNGStringFromSVG(svg, panGroupSize) {
        prepareSvgForConversion(svg, panGroupSize);
        const newImage = new Image(panGroupSize.width, panGroupSize.height);
        const base64StringOfSVG = UnicodeBase64Polyfill.encode(new XMLSerializer().serializeToString(svg));
        newImage.setAttribute('src', 'data:image/svg+xml;base64,' + base64StringOfSVG);

        return await getDataUrlFromCanvasByImage(newImage);
    }

    async function compressedImageUrl(image, scaleFactor) {
        const newImage = new Image(image.width * scaleFactor, image.height * scaleFactor)
        newImage.src = image.src;

        return await getDataUrlFromCanvasByImage(newImage);
    }

    function prepareSvgForConversion(svg, panGroupSize) {
        addNunitoFontToSVG(svg);
        adjustViewboxProperties(svg, panGroupSize);
        return svg;
    }

    function prepareShapesForSubmission() {
        rootElement.querySelectorAll(".selected,.editing").forEach((element) => {
            element.classList.remove("selected", "editing");
        });
    }

    function addNunitoFontToSVG(svg) {
        let defsNode = svg.querySelector('defs', '')
        let style = document.createElement('style');
        defsNode.appendChild(style)
        let textNode = document.createTextNode('@font-face{font-family:"Nunito";src:url(data:font/woff;base64,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) format("woff");font-weight:normal;font-style:normal;}')
        style.appendChild(textNode);
    }

    function adjustViewboxProperties(svg, panGroupSize) {
        svg.setAttribute('viewBox', `${panGroupSize.x} ${panGroupSize.y} ${panGroupSize.width} ${panGroupSize.height}`);
        svg.setAttribute('width', `${panGroupSize.width}`);
        svg.setAttribute('height', `${panGroupSize.height}`);
        svg.querySelector('#svg-pan-zoom-group').setAttribute('transform', '');
        svg.querySelector('#svg-grid-group').setAttribute('stroke', '#c3d0ed');
    }

    function hasNoAnswerObjects() {
        return !Object.keys(Canvas.layers.answer.shapes).length;
    }

    function toggleSaveNoAnswersConfirm() {
        UI.saveNoAnswersConfirm.classList.toggle('open');
    }

    function toggleSaveConfirm() {
        UI.saveConfirm.classList.toggle('open');
    }

    function hasHiddenLayers() {
        if (drawingApp.isTeacher()) {
            if (Canvas.params.currentLayer === 'question') {
                return questionLayerIsHidden() || hasQuestionHiddenLayers()
            }
        }
        return answerLayerIsHidden() || questionLayerIsHidden() || hasAnswerHiddenLayers() || hasQuestionHiddenLayers()
    }

    function handleHiddenLayers() {
        if (Object.keys(Canvas.layers.question.shapes).length) {
            Object.values(Canvas.layers.question.shapes).forEach((shape) => {
                if (shape.sidebar.svgShape.isHidden()) {
                    shape.sidebar.handleToggleHide();
                }

            });
        }
        if (Object.keys(Canvas.layers.answer.shapes).length) {
            Object.values(Canvas.layers.answer.shapes).forEach((shape) => {
                if (shape.sidebar.svgShape.isHidden()) {
                    shape.sidebar.handleToggleHide();
                }
            });
        }
    }

    function closeDrawingTool() {
        rootElement.dispatchEvent(new CustomEvent('close-drawing-tool'));
    }

    function handleCloseByExit() {
        UI.closeConfirm.classList.toggle('open');
    }

    function answerLayerIsHidden() {
        return Canvas.layers.answer.params.hidden && !!Object.keys(Canvas.layers.answer.shapes).length;
    }

    function questionLayerIsHidden() {
        return Canvas.layers.question.params.hidden && !!Object.keys(Canvas.layers.question.shapes).length;
    }

    function hasQuestionHiddenLayers() {
        if (Object.keys(Canvas.layers.question.shapes).length) {
            return !!Object.values(Canvas.layers.question.shapes).filter((shape) => {
                return shape.sidebar.svgShape.isHidden()
            }).length;
        }
        return false;
    }

    function hasAnswerHiddenLayers() {
        if (Object.keys(Canvas.layers.answer.shapes).length) {
            return !!Object.values(Canvas.layers.answer.shapes).filter((shape) => {
                return shape.sidebar.svgShape.isHidden()
            }).length;
        }
        return false;
    }

    function handleShapeSelection(evt) {
        const shapeGroup = evt.target.closest(".shape");
        if (!shapeGroup) return;

        const layerID = shapeGroup.parentElement.id;
        const layerObject = Canvas.layers[Canvas.layerID2Key(layerID)];
        if (!layerObject.props.id.includes(layerObject.Canvas.params.currentLayer)) return;

        const selectedShape = rootElement.querySelector('.selected');
        const selectedSvgShape = evt.target.closest("g.shape");

        if (selectedShape) removeSelectState(selectedShape);
        if (selectedShape === selectedSvgShape) return;

        addSelectState(selectedSvgShape);
    }

    function removeSelectState(element) {
        element.classList.remove('selected')
        rootElement.querySelector('#shape-' + element.id).classList.remove('selected')
    }

    function addSelectState(element) {
        element.classList.add('selected')
        rootElement.querySelector('#shape-' + element.id).classList.add('selected')
    }

    function movedDuringClick(evt) {
        if (drawingApp.params.currentTool !== "drag") {
            return true;
        }

        const delta = 6;
        const startX = Canvas.params.cursorPositionMousedown.x;
        const startY = Canvas.params.cursorPositionMousedown.y;

        let evtClientX = evt.clientX;
        let evtClientY = evt.clientY;

        if (evt.touches?.length > 0) {
            evtClientX = evt.touches[0].clientX;
            evtClientY = evt.touches[0].clientY;
        }

        const diffX = Math.abs(evtClientX - startX);
        const diffY = Math.abs(evtClientY - startY);

        return !(diffX < delta && diffY < delta);
    }

    function setMousedownPosition(evt) {
        Canvas.params.cursorPositionMousedown.x = evt.clientX;
        Canvas.params.cursorPositionMousedown.y = evt.clientY;
        if (evt.touches?.length > 0) {
            Canvas.params.cursorPositionMousedown.x = evt.touches[0].clientX;
            Canvas.params.cursorPositionMousedown.y = evt.touches[0].clientY;
        }
    }

    /**
     * Event handler for down events of the cursor.
     * Calls either startDrag() or startDraw() based on currentType.
     * @param {Event} evt
     */
    function cursorStart(evt) {
        evt.preventDefault();
        if(ShouldEditTextOnClick()) return;

        updateCursorPosition(evt);
        setMousedownPosition(evt)

        if (Canvas.params.focusedShape)
            Canvas.params.focusedShape = null;

        Canvas.unhighlightShapes();

        if (evt.touches?.length === 2) {
            return startPan(evt);
        }
        if (drawingApp.params.currentTool === "drag") {
            if (evt.target.classList.contains("corner")) return startResize(evt);
            return startDrag(evt);
        }
        return startDraw(evt);
    }

    function startDrag(evt) {
        const shapeGroup = evt.target.closest(".shape");
        if (!shapeGroup) return;

        const layerID = shapeGroup.parentElement.id;
        const layerObject = Canvas.layers[Canvas.layerID2Key(layerID)];
        if (!shapeMayBeDragged(shapeGroup, layerObject)) return;

        const selectedSvgShape = layerObject.shapes[shapeGroup.id].svg;

        Canvas.params.drag = {
            enabled: true,
            selectedSvgShape: selectedSvgShape
        };

        selectedSvgShape.onDragStart(evt, Canvas.params.cursorPosition);
    }

    function startResize(evt) {
        const shapeGroup = evt.target.closest(".shape");
        if (!shapeGroup) return;

        const layerID = shapeGroup.parentElement.id;
        const layerObject = Canvas.layers[Canvas.layerID2Key(layerID)];
        if (!shapeMayBeDragged(shapeGroup, layerObject)) return;

        const selectedSvgShape = layerObject.shapes[shapeGroup.id].svg;
        if (!shapeIsResizable(selectedSvgShape)) return;

        Canvas.params.resize = {
            enabled: true,
            selectedSvgShape: selectedSvgShape
        };

        selectedSvgShape.onResizeStart(evt, getCursorPosition(evt));
    }

    function shapeIsResizable(shape) {
        return resizableSvgShapes.includes(shape.type);
    }

    function shapeMayBeDragged(shapeGroup, layerObject) {
        return shapeGroup.classList.contains("draggable") && !layerObject.params.locked && layerObject.props.id.includes(layerObject.Canvas.params.currentLayer);
    }

    function startPan() {
        Canvas.params.pan = {
            enabled: true,
            startCoordinates: Canvas.params.cursorPosition,
        };
    }

    function startDraw(evt) {
        const cursorPosition = Canvas.params.cursorPosition;
        Canvas.params.cursorPosition = cursorPosition;

        const currentTool = drawingApp.params.currentTool,
            properties = determinePropertiesForShape(currentTool),
            parent = Canvas.params.currentLayer;

        const newShape = makeNewSvgShapeWithSidebarEntry(
            currentTool,
            properties,
            parent
        );
        newShape.svg.onDrawStart?.(evt, cursorPosition);
        const shapeID = Canvas.params.draw.shapeCountForEachType[currentTool];
        const shapeObjectID = `${currentTool}-${shapeID}`;
        const layerObject = Canvas.layers[Canvas.params.currentLayer];
        layerObject.shapes[shapeObjectID] = newShape;
        layerObject.unhideIfHidden();
        Canvas.params.draw.newShape = newShape;
    }

    function determinePropertiesForShape(type) {
        return {
            main: determineMainElementAttributes(type),
        };
    }

    function determineMainElementAttributes(type) {
        const cursorPosition = Canvas.params.cursorPosition;
        return svgShape[shapeTypeWithRespectiveSvgClass[type]]
            .getMainElementAttributes(cursorPosition, UI, drawingApp.params);
    }

    function makeNewSvgShapeWithSidebarEntry(type, props, parent, withHelperElements, withHighlightEvents) {
        let svgShape = makeNewSvgShape(type, props, Canvas.layers[parent].svg, withHelperElements, withHighlightEvents);
        let newSidebarEntry = new sidebar.Entry(svgShape, drawingApp);
        Canvas.layers[parent].addEntry(newSidebarEntry);
        svgShape.setSidebarEntry(newSidebarEntry);
        return {
            svg: svgShape,
            sidebar: newSidebarEntry,
        };
    }

    /**
     * Determines based on type which shape constructor to call and returns the created object.
     * @param {string} type Type of svgShape to be created.
     * @param {propObj} props Properties which will be appended to the main svgElement.
     * @param {?SVGElement} parent The parent to which the created svgShape will be added. Defaults to an empty SVGElement().
     * @param {boolean} withHelperElements Boolean to set whether helper elements should be created for the shape
     * @param {boolean} withHighlightEvents Boolean to set whether highlight events should be set for the shape
     * @returns A shape object of the right type
     */
    function makeNewSvgShape(type, props, parent = new SVGElement(), withHelperElements, withHighlightEvents) {
        let shapeID = ++Canvas.params.draw.shapeCountForEachType[type];
        switch (type) {
            case "rect":
                return new svgShape.Rectangle(shapeID, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents);
            case "circle":
                return new svgShape.Circle(shapeID, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents);
            case "line":
                return new svgShape.Line(shapeID, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents);
            case "text":
                return new svgShape.Text(shapeID, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents);
            case "image":
                return new svgShape.Image(shapeID, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents);
            case "path":
                return new svgShape.Path(shapeID, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents);
            case "freehand":
                return new svgShape.Freehand(shapeID, props, parent, drawingApp, Canvas, withHelperElements, withHighlightEvents);
            default:
                console.error(
                    `makeShapeOfRightType(): type  (${type}) is not valid. No shape was created.`
                );
                return null;
        }
    }

    /**
     * Event handler for moving of the cursor.
     * @param {Event} evt
     */
    function cursorMove(evt) {
        updateCursorPosition(evt);
        const cursorPosition = Canvas.params.cursorPosition;
        showCursorPosition(cursorPosition);

        if (Canvas.panning()) {
            processUserPan();
        } else if (Canvas.dragging()) {
            drag(evt);
        } else if (Canvas.resizing()) {
            resize(evt);
        } else if (Canvas.drawing()) {
            Canvas.params.draw.newShape.svg.onDraw?.(evt, cursorPosition);
        }

        Canvas.params.cursorPosition = cursorPosition;

        if (evt.type === 'touchmove') {
            Canvas.params.touchmoving = true;
        }
    }

    function updateCursorPosition(evt) {
        Canvas.params.cursorPosition = getCursorPosition(evt);
    }

    /**
     * Calculates the position of the cursor in CTM of the SVG canvas.
     * @param {Event} evt
     * @returns {Cursor} The X and Y coordinates of the cursor
     */
    function getCursorPosition(evt) {
        let CTM = UI.svgPanZoomGroup.getScreenCTM();
        evt = evt.touches?.[0] || evt;

        if (evt.type === 'touchend') return Canvas.params.cursorPosition;

        return {
            x: (evt.clientX - CTM.e) / CTM.a,
            y: (evt.clientY - CTM.f) / CTM.d,
        };
    }

    /**
     * Shows provided cursor coordinates on screen.
     * @param {Cursor} position Cursor coordinates.
     */
    function showCursorPosition(position) {
        const cursorXTrunc = Math.trunc(position.x),
            cursorYTrunc = Math.trunc(position.y);
        UI.cursorPos.innerText = `X ${cursorXTrunc}, Y ${cursorYTrunc * -1}`;
    }

    function drag(evt) {
        evt.preventDefault();
        Canvas.params.drag.selectedSvgShape.onDrag(evt, Canvas.params.cursorPosition);
    }

    function resize(evt) {
        evt.preventDefault();
        Canvas.params.resize.selectedSvgShape.onResize(evt, getCursorPosition(evt));
    }

    function processUserPan() {
        const difference = calculateDistanceCursor(
            Canvas.params.pan.startCoordinates,
            Canvas.params.cursorPosition
        );
        pan(difference);
    }

    /**
     * Moves the canvas the specified distance. If no distance is specified it doesn't move.
     * @param {Object.<string, number>} distance
     * @param {number} distance.dx
     * @param {number} distance.dy
     */
    function pan(distance = {dx: 0, dy: 0}) {
        const matrix = Canvas.params.domMatrix;
        const ratio = Canvas.params.zoomFactor;
        matrix.translateSelf(distance.dx * ratio, distance.dy * ratio);

        setPanZoomMatrix(matrix);
        calculateCanvasBounds();
        updateGrid();
    }

    /**
     * Moves the canvas in horizontal direction, LEFT when direction 1, RIGHT when direction -1.
     * @param {number} direction defaults to 0.
     */
    function panHorizontalOneStep(direction = 0) {
        pan({
            dx: panParams.STEP * direction / Canvas.params.zoomFactor,
            dy: 0,
        });
    }

    /**
     * Moves the canvas in vertical direction, UP when direction 1, DOWN when direction -1.
     * @param {number} direction defaults to 0.
     */
    function panVerticalOneStep(direction = 0) {
        pan({
            dx: 0,
            dy: panParams.STEP * direction / Canvas.params.zoomFactor,
        });
    }

    /**
     * Zooms the canvas to the specified level. If no level is specified, it zooms to 100%;
     * If no origin is specified, it zooms with respect to the midpoint of the viewport.
     * @param {number} level
     * @param {Object.<string, number>} origin
     * @param {number} origin.x
     * @param {number} origin.y
     */
    function zoom(level = 1, origin) {
        const currentFactor = Canvas.params.zoomFactor,
            factorToBeZoomed = level / currentFactor;
        if (!origin) {
            const bounds = Canvas.params.bounds;
            origin = {
                x: bounds.cx,
                y: bounds.cy,
            };
        }
        let matrix = Canvas.params.domMatrix;
        matrix.scaleSelf(factorToBeZoomed, factorToBeZoomed, 1, origin.x, origin.y);

        Canvas.params.zoomFactor *= factorToBeZoomed;
        setPanZoomMatrix(matrix);
        calculateCanvasBounds();
        updateGrid();
    }

    /**
     * Zooms the canvas in by one step with respect to the midpoint of the viewport.
     */
    function zoomInOneStep() {
        zoomOneStep(-1);
    }

    /**
     * Zooms the canvas out by one step with respect to the midpoint of the viewport.
     */
    function zoomOutOneStep() {
        zoomOneStep(1);
    }

    /**
     * Zooms the canvas by one step, OUT when direction is 1, IN when direction is -1.
     * @param {number} direction defaults to 0.
     * @param {Object.<string, number>} origin
     * @param {number} origin.x
     * @param {number} origin.y
     */
    function zoomOneStep(direction = 0, origin) {
        let factor = Canvas.params.zoomFactor - (zoomParams.STEP * direction);
        factor = checkZoomFactorBounds(factor);
        zoom(factor, origin);
        updateZoomInputValue(factor);
    }

    /**
     * Zooms the canvas by one step with the cursorPosition as origin.
     * @param {number} direction
     */
    function zoomOneStepToCursor(direction) {
        const cursorPosition = Canvas.params.cursorPosition;
        zoomOneStep(direction, cursorPosition);
    }

    /**
     * Sets the transform matrix on the svgPanZoomGroup.
     * @param {DOMMatrix} matrix
     */
    function setPanZoomMatrix(matrix) {
        UI.svgPanZoomGroup.setAttributeNS(null, "transform", matrix.toString());
    }

    function calculateDistanceCursor(oldCoords, currentCoords) {
        return {
            dx: currentCoords.x - oldCoords.x,
            dy: currentCoords.y - oldCoords.y
        }
    }

    function updateZoomInputValue(value = 1) {
        handleDisabledZoomButtonStates(value);
        UI.zoomLevel.value = (value * 100) + "%";
    }

    function checkZoomFactorBounds(value) {
        if (value > zoomParams.MAX) value = zoomParams.MAX;
        if (value < zoomParams.MIN) value = zoomParams.MIN;
        return value;
    }

    function cursorStop(evt) {
        updateCursorPosition(evt);
        if (Canvas.drawing()) {
            stopDraw(evt);
        } else if (Canvas.dragging()) {
            stopDrag();
        } else if (Canvas.resizing()) {
            stopResize();
        } else if (Canvas.panning()) {
            stopPan();
        }
    }

    function stopDraw(evt) {
        const newShape = Canvas.params.draw.newShape;
        newShape.svg.onDrawEnd(
            evt,
            Canvas.params.cursorPosition
        );
        Canvas.params.draw.newShape = null;
        if(!newShape.svg.meetsMinRequirements()) {
            Canvas.deleteObject(newShape.sidebar);
            --Canvas.params.draw.shapeCountForEachType[newShape.sidebar.type];
            return;
        } 
        Canvas.params.highlightedShape = newShape;
    }

    function stopDrag() {
        Canvas.params.drag.selectedSvgShape.onDragEnd();
        Canvas.params.drag = {
            enabled: false,
        };
    }

    function stopResize() {
        Canvas.params.resize = {
            enabled: false,
        };
    }

    function stopPan() {
        Canvas.params.pan = {
            enabled: false,
        };
    }

    function processToolChange(evt) {
        let currentTool = drawingApp.params.currentTool,
            newTool = determineNewTool(evt);
        if (currentTool === newTool) return;
        drawingApp.params.currentTool = newTool;
        makeSelectedBtnActive(evt.currentTarget);
        enableSpecificPropSelectInputs();
        setCursorTypeAccordingToCurrentType();
        unselectShapeIfNecessary();
        if (!drawingApp.currentToolIs("drag")) {
            drawingApp.warnings.whenAnyToolButDragSelected.show();
        }
    }

    function manualToolChange(tool) {
        let currentTool = drawingApp.params.currentTool;
        const newTool = tool;
        if (currentTool === newTool) return;

        drawingApp.params.currentTool = newTool;

        const btnElement = rootElement.querySelector(`[id*="${newTool}-btn"]`)
        makeSelectedBtnActive(btnElement);
        enableSpecificPropSelectInputs();
        setCursorTypeAccordingToCurrentType();
        if (!drawingApp.currentToolIs("drag")) {
            drawingApp.warnings.whenAnyToolButDragSelected.show();
        }
    }

    function determineNewTool(evt) {
        const id = evt.currentTarget.id;
        let startOfSlice = id.indexOf("-") + 1,
            endOfSlice = id.lastIndexOf("-");
        if (endOfSlice === startOfSlice - 1 || endOfSlice === -1) {
            endOfSlice = startOfSlice - 1;
            startOfSlice = 0;
        }
        return id.slice(startOfSlice, endOfSlice);
    }

    function unselectShapeIfNecessary() {
        const selectedShape = rootElement.querySelector('.selected');
        if(shouldUnselectShape(selectedShape)) {
            const shapeDataObject = Canvas.getShapeDataObject(selectedShape);
            shapeDataObject.sidebar.unselect();
        }
    }

    function shouldUnselectShape(selectedShape) {
        return selectedShape
            && !drawingApp.currentToolIs("drag")
            && !drawingApp.toolAndShapeOfSameType(selectedShape);
    }

    function processEndmarkerTypeChange(evt) {
        drawingApp.params.endmarkerType = determineNewEndmarkerType(evt);
        makeSelectedBtnActive(evt.currentTarget);
    }

    function determineNewEndmarkerType(evt) {
        return evt.currentTarget.id;
    }


    function processUploadedImages(evt) {
        for (const file of evt.target.files) {
            createImageInsideCanvas(file);
        }

        manualToolChange('drag');
        UI.imgUpload.value = null;
    }

    function fileLoadedIntoReader(evt, identifier) {
        const imageURL = evt.target.result;
        const dummyImage = new Image();
        dummyImage.src = imageURL;
        drawingApp.bindEventListeners([
            {
                element: dummyImage,
                events: {
                    load: {
                        callback: (evt) => {
                            dummyImageLoaded(evt, identifier);
                        },
                    },
                    error: {
                        callback: () => {
                            console.error("Something went wrong while processing this image.");
                        },
                    },
                }
            }
        ]);
    }

    async function dummyImageLoaded(evt, identifier) {
        const dummyImage = evt.target,
            scaleFactor = correctImageSize(dummyImage),
            // imageURL = dummyImage.src;
            base65PNGString = await compressedImageUrl(dummyImage, scaleFactor);
        const shape = makeNewSvgShapeWithSidebarEntry(
            "image",
            {
                main: {
                    href: base65PNGString,
                    width: dummyImage.width * scaleFactor,
                    height: dummyImage.height * scaleFactor,
                    identifier: identifier
                },
            },
            Canvas.params.currentLayer
        );
        shape.svg.moveToCenter();
        shape.svg.addHighlightEvents();
        const objectID = `image-${shape.svg.shapeId}`;
        Canvas.layers[Canvas.params.currentLayer].shapes[objectID] = shape;
    }

    function correctImageSize(image) {
        const canvasWidth = UI.svgCanvas.clientWidth,
            canvasHeight = UI.svgCanvas.clientHeight;
        var scaleFactor = 1;

        if (image.width > canvasWidth) {
            scaleFactor = canvasWidth / image.width;
            if (image.height * scaleFactor > canvasHeight) {
                scaleFactor = canvasHeight / image.height;
            }
        } else if (image.height > canvasHeight) {
            scaleFactor = canvasHeight / image.height;
            if (image.width * scaleFactor > canvasWidth) {
                scaleFactor = canvasWidth / image.width;
            }
        }
        return scaleFactor * 0.99;
    }

    function handleImagePaste(evt) {
        let items = evt.clipboardData.items;

        for (let i = 0; i < items.length; i++) {
            if (items[i].type.indexOf("image") !== -1) {
                let file = items[i].getAsFile();

                createImageInsideCanvas(file);
            }
        }

        manualToolChange('drag');
        UI.imgUpload.value = null;
    }

    function createImageInsideCanvas(file) {
        if(!imageTypeIsAllowed(file)) return;

        UI.submitBtn.disabled = true

        let identifier = uuidv4();
        let reader = new FileReader();

        uploadImageToLivewireComponent(file, identifier);

        reader.readAsDataURL(file);

        drawingApp.bindEventListeners([
            {
                element: reader,
                events: {
                    loadend: {
                        callback: (evt) => {
                            fileLoadedIntoReader(evt, identifier);
                        },
                    },
                    error: {
                        callback: () => {
                            console.error(
                                "Something went wrong while loading this image."
                            );
                        },
                    },
                }
            }
        ]);
    }

    function uploadImageToLivewireComponent(file, identifier) {
        drawingApp.livewireComponent.upload(`cmsPropertyBag.images.${Canvas.params.currentLayer}.${identifier}`, file, () => {
            // Success callback.
            UI.submitBtn.disabled = false
        }, () => {
            // Error callback.
            UI.submitBtn.disabled = false
        }, () => {
            drawMissingShapesOnSvg();
            // Progress callback.
        })
    }

    function drawMissingShapesOnSvg() {
        for (const layerKey in Canvas.layers) {
            const layer = Canvas.layers[layerKey];
            for (const shapeKey in layer.shapes) {
                const shape = layer.shapes[shapeKey];
                shape.svg.redrawOnSvg();
            }
        }
    }
    
    function imageTypeIsAllowed(file) {
        if(file.size / (1024 * 1024) > 4) {
            dispatchEvent(new CustomEvent('js-localized-notify-popup', {detail: {translation_key: 'image-size-error', message_type: 'error'}}));
            return false;
        }

        if(!['png', 'jpeg', 'jpg'].includes(file.type.toLowerCase().split('/')[1])) {
            dispatchEvent(new CustomEvent('js-localized-notify-popup', {detail: {translation_key: 'image-type-error', message_type: 'error'}}));
            return false;
        }

        return true;
    }

    function updateGridButtonStates(disabled) {
        UI.gridSize.disabled = disabled;
        UI.decrGridSize.disabled = UI.gridSize.value <= UI.gridSize.min ? true : disabled;
        UI.incrGridSize.disabled = UI.gridSize.value >= UI.gridSize.max ? true : disabled;
        Canvas.layers.grid.params.hidden = disabled;

        const gridSizeContainerClassList = UI.gridSize.parentElement.classList;
        disabled ? gridSizeContainerClassList.add('disabled') : gridSizeContainerClassList.remove('disabled');
    }

    function processGridToggleChange() {
        const gridState = !UI.gridToggle.checked;
        updateGridButtonStates(gridState);
        updateGridVisibility();
    }

    function makeGrid() {
        const props = {
            group: {
                style: "display: none;",
            },
            main: {},
            origin: {
                // stroke: "var(--teacher-Primary)",
                id: "grid-origin",
            },
            size: (drawingApp.isTeacher() ? UI.gridSize.value : drawingApp.params.gridSize),
        }
        Canvas.layers.grid.shape = new svgShape.Grid(0, props, UI.svgGridGroup, drawingApp, Canvas);
    }

    function drawGridBackground() {
        const props = {
            group: {},
            main: {},
            origin: {
                id: "grid-origin",
            },
            size: getAdjustedGridValue(),
        }
        Canvas.layers.bgGrid = new svgShape.Grid(0, props, UI.svgGridGroup, drawingApp, Canvas);
    }

    function getAdjustedGridValue() {
        if (grid && grid !== '0') {
            return 1 / parseInt(grid) * 14;   // This calculation is based on try and change to reach the closest formula that makes grid visualization same as old drawing
        }
        return 0;
    }

    function updateGridVisibility() {
        const grid = Canvas.layers.grid;
        const shape = grid.shape;
        if (!grid.params.hidden && valueWithinBounds(UI.gridSize)) {
            shape.show();
            return;
        }
        shape.hide();
    }

    function updateGrid() {
        if (valueWithinBounds(UI.gridSize)) {
            drawingApp.params.gridSize = UI.gridSize.value;
            Canvas.layers.grid.shape.update();
            if (Canvas.layers.bgGrid) {
                Canvas.layers.bgGrid.update(getAdjustedGridValue());
            }
        }
    }

    function updateElemOpacityNumberInput() {
        UI.elemOpacityNumber.value = UI.elemOpacityRange.value;
        setSliderColor(UI.elemOpacityRange, UI.textColor.value);
    }

    function updateElemOpacityRangeInput() {
        UI.elemOpacityRange.value = UI.elemOpacityNumber.value;
        setSliderColor(UI.elemOpacityRange, UI.textColor.value);
    }

    function updateAllOpacitySliderColor() {
        rootElement.querySelectorAll('[id*="fill-color"]').forEach(elem => {
            updateOpacitySliderColor(elem, 'fill');
        });
        setSliderColor(UI.elemOpacityRange, UI.textColor.value);
    }

    function updateOpacitySliderColor(elem, property) {
        const propertGroup = elem.closest('.property-group');
        const shape = getShapeFromElemId(propertGroup);
        const slider = propertGroup.querySelector('input[type="range"]');
        const sliderColor = propertGroup.querySelector(`#${property}-color-${shape}`).value;

        setSliderColor(slider, sliderColor);
    }

    /**
     * Sets the '--slider-color' property on the _slider_ to a linear-gradient
     * where the color left of the knob is determined by _leftColorHexValue_.
     * @param {HTMLElement} slider The slider to update.
     * @param {?string} leftColorHexValue The hexadecimal value for the color left of the knob.
     */
    function setSliderColor (
        slider,
        leftColorHexValue = getRootCSSProperty("--all-Base")
    ) {
        let ratio = calculateRatioOfValueToMax(slider);
        let leftColorRgbaValue = convertHexToRgbaColor(
            leftColorHexValue,
            slider.value
        );
        slider.style.setProperty(
            "--slider-color",
            `linear-gradient(to right, ${leftColorRgbaValue} 0%, ${leftColorRgbaValue} ${ratio}%, var(--all-White) ${ratio}%, var(--all-White) 100%)`
        );
    }

    /**
     * Gets the value of the property from the CSS :root selector element
     * @param {string} property
     * @returns {string} The value of the property
     */
    function getRootCSSProperty(property) {
        return window
            .getComputedStyle(document.documentElement)
            .getPropertyValue(property);
    }

    function setRootCSSProperty(property, value) {
        document.documentElement.style.setProperty(property, value);
    }

    /**
     * Calculates the ratio between the current value and the max value of the slider.
     * @param {HTMLElement} input The input of which to calculate the ratio.
     * @returns {Number} The ratio, clipped on two decimals.
     */
    function calculateRatioOfValueToMax(input) {
        return (
            ((input.value - input.min) / (input.max - input.min)) *
            100
        ).toFixed(2);
    }

    /**
     * Converts a color defined in hexadecimal and an opacity [0,100] to a color defined with rgba()
     * @param {string} color Hexadecimal string (#xxxxxx)
     * @param {number} A Number [0,100] representing the Alpha (opacity)
     * @returns 'rgba(red, blue, green, alpha)' as a string.
     */
    function convertHexToRgbaColor(color, A) {
        color = color.trim();
        let R = parseInt(color.substring(1, 3), 16),
            G = parseInt(color.substring(3, 5), 16),
            B = parseInt(color.substring(5, 7), 16);
        return `rgba(${R}, ${G}, ${B}, ${parseFloat(A) / 100})`;
    }

    function updateFillOpacityNumberInput(elem, property) {
        elem.previousElementSibling.value = elem.value;
        updateOpacitySliderColor(elem, property);
    }

    function updateFillOpacityRangeInput(elem, property) {
        elem.nextElementSibling.value =  elem.value;
        updateOpacitySliderColor(elem, property);
    }

    function valueWithinBounds(inputElem) {
        let value = parseFloat(inputElem.value),
            max = parseFloat(inputElem.max),
            min = parseFloat(inputElem.min);
        if (Number.isNaN(value)) {
            return false;
        }
        if (value > max) {
            inputElem.value = inputElem.max;
        } else if (value < min) {
            inputElem.value = inputElem.min;
        }
        return true;
    }

    function setCursorTypeAccordingToCurrentType() {
        const cursors = {
            drag: {
                locked: "var(--cursor-default)",
                draggable: "var(--cursor-move-shape)",
                canvas: "var(--cursor-default)",
            },
            freehand: {
                locked: "var(--cursor-freehand)",
                draggable: "var(--cursor-freehand)",
                canvas: "var(--cursor-freehand)",
            },
            default: {
                locked: "var(--cursor-crosshair)",
                draggable: "var(--cursor-crosshair)",
                canvas: "",
            },
        };
        setCursors(cursors[drawingApp.params.currentTool] ?? cursors.default);
    }

    function setCursors(cursor) {
        UI.svgCanvas.style.setProperty("--cursor-type-locked", cursor.locked);
        UI.svgCanvas.style.setProperty("--cursor-type-draggable", cursor.draggable);
        UI.svgCanvas.style.setProperty("cursor", cursor.canvas);
    }

    function enableSpecificPropSelectInputs() {
        for (const child of UI.properties.children) {
            child.style.display = "none";
        }
        shapePropertiesAvailableToUser[drawingApp.params.currentTool].forEach((prop) => {
            rootElement.querySelector(`#${prop}`).style.display = "flex";
        });
    }

    function makeSelectedBtnActive(selectedBtn) {
        const btnGroupName = selectedBtn.getAttribute("data-button-group");
        const activeBtnsOfBtnGroup = rootElement.querySelectorAll(
            `[data-button-group=${btnGroupName}].active`
        );
        for (const btn of [...activeBtnsOfBtnGroup]) btn.classList.remove("active");
        selectedBtn.classList.add("active");
    }

    function getPanGroupSize() {
        const gridLayerHidden = !Canvas.layers.grid.shape.isHidden();
        const questionLayerHidden = Canvas.layers.question.isHidden();
        const answerLayerHidden = Canvas.layers.answer.isHidden();

        if (questionLayerHidden) {
            Canvas.layers.question.unhide()
        }
        if (answerLayerHidden) {
            Canvas.layers.answer.unhide()
        }
        if (gridLayerHidden) {
            Canvas.layers.grid.shape.hide()
        }

        const panGroupSize = UI.svgPanZoomGroup.getBBox();

        if (questionLayerHidden) {
            Canvas.layers.question.hide()
        }
        if (answerLayerHidden) {
            Canvas.layers.answer.hide()
        }
        if (gridLayerHidden) {
            Canvas.layers.grid.shape.show()
        }

        return handleMinPanGroupSizes(panGroupSize);
    }

    function handleMinPanGroupSizes(panGroupSize) {
        const minPanGroupWidth = 820;
        const minPanGroupHeight = 500;
        let resultWidth;
        let resultHeight;
        let resultX;
        let resultY;

        resultWidth = panGroupSize.width;
        resultX = panGroupSize.x;

        if (panGroupSize.width < minPanGroupWidth) {
            resultWidth = minPanGroupWidth;
            resultX = panGroupSize.x - ((minPanGroupWidth - panGroupSize.width) / 2)
        }

        resultHeight = panGroupSize.height;
        resultY = panGroupSize.y;

        if (panGroupSize.height < minPanGroupHeight) {
            resultHeight = minPanGroupHeight;
            resultY = panGroupSize.y - ((minPanGroupHeight - panGroupSize.height) / 2)
        }

        return {
            x: resultX,
            y: resultY,
            width: resultWidth,
            height: resultHeight,
        }
    }

    function handleDisabledZoomButtonStates(newFactor) {
        if (newFactor === zoomParams.MAX) {
            UI.incrZoom.disabled = true;
            return;
        }
        if (newFactor === zoomParams.MIN) {
            UI.decrZoom.disabled = true;
            return;
        }

        UI.incrZoom.disabled = false;
        UI.decrZoom.disabled = false;
    }

    function getBoundsForInputElement(element) {
        const currentValue = parseFloat(element.value);
        const min = parseFloat(element.min);
        const max = parseFloat(element.max);

        return {currentValue, min, max};
    }

    function getButtonsForElement(element) {
        const decrButton = UI[`decr${element.capitalize()}`];
        const incrButton = UI[`incr${element.capitalize()}`];

        return {decrButton, incrButton};
    }

    function getShapeFromElemId(elem) {
        const parts = elem.id.split("-");
        return parts[parts.length - 1];
    }

    function getInputsAndButtonsForProperty(propertyGroup, property, shape) {
        const input = propertyGroup.querySelector(`#${property}-${shape}`);
        const decrButton = propertyGroup.querySelector(`#decr-${property}-${shape}`);
        const incrButton = propertyGroup.querySelector(`#incr-${property}-${shape}`);

        return {input, decrButton, incrButton};
    }

    function disableButtonsWhenNecessary(UIElementString) {
        const {decrButton, incrButton} = getButtonsForElement(UIElementString);
        const {currentValue, min, max} = getBoundsForInputElement(UI[UIElementString]);

        decrButton.disabled = currentValue === min;
        incrButton.disabled = currentValue === max;
    }

    function handleTextSizeButtonStates() {
        disableButtonsWhenNecessary('textSize');
    }

    function handleGridSizeButtonStates() {
        disableButtonsWhenNecessary('gridSize');
    }

    function toggleDisableButtonStates(elem, property) {
        const propertGroup = elem.closest('.property-group');
        if(!propertGroup) return;

        const shape = getShapeFromElemId(propertGroup);
        const {input, decrButton, incrButton} = getInputsAndButtonsForProperty(propertGroup, property, shape);
        const {currentValue, min, max} = getBoundsForInputElement(input);

        decrButton.disabled = currentValue === min;
        incrButton.disabled = currentValue === max;
    }

    function checkIfShouldeditShape(selectedShape) {
        return selectedShape && drawingApp.toolAndShapeOfSameType(selectedShape)
    }

    function editShape(functionName) {
        const selectedShape = rootElement.querySelector('.editing');
        if(!checkIfShouldeditShape(selectedShape)) return;

        const selectedSvgShapeClass = Canvas.getShapeDataObject(selectedShape).svg;
        functionName in selectedSvgShapeClass && selectedSvgShapeClass[functionName]();
    }

    function ShouldEditTextOnClick() {
        const selectedShape = rootElement.querySelector('.editing');
        if(!checkIfShouldeditShape(selectedShape)) return;

        return drawingApp.currentToolIs('text') && Canvas.params.editingTextInZone;
    }

    return {UI, Canvas, drawingApp}
}

function clearPreviewGrid(rootElement) {
    const gridContainer = rootElement.querySelector('#grid-preview-svg')

    if (gridContainer !== null && gridContainer.firstChild !== null) {
        gridContainer.firstChild.remove();
    }
}

window.makePreviewGrid = function (drawingApp, gridSvg) {

    const rootElement = drawingApp.params.root

    clearPreviewGrid(rootElement);

    const props = {
        group: {
            style: "",
        },
        main: {},
        origin: {
            stroke: "var(--teacher-blueGrey)",
            id: "grid-origin",
        },
        size: gridSvg,
    }
    let parent = rootElement.querySelector('#grid-preview-svg')
    return new svgShape.Grid(0, props, parent, drawingApp, null);
}

window.calculatePreviewBounds = function (parent) {
    const matrix = new DOMMatrix();
    const height = parent.clientHeight,
        width = parent.clientWidth;
    let scale = parent.viewBox.baseVal.width / width;

    if ((parent.viewBox.baseVal.width * parent.viewBox.baseVal.height) > (width * height)) {
        scale = (width * height) / (parent.viewBox.baseVal.width * parent.viewBox.baseVal.height)
    }
    return {
        top: -(matrix.f + (height)) / scale,
        bottom: (height - matrix.f) / scale,
        height: (height * 2) / scale,
        left: -(matrix.e + (width)) / scale,
        right: (width - matrix.e) / scale,
        width: (width * 2) / scale,
        cx: -matrix.e + (width / 2),
        cy: -matrix.f + (height / 2),
    };
}
