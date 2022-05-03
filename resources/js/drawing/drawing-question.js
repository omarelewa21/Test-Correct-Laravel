import {panParams, shapePropertiesAvailableToUser, zoomParams} from "./constants.js";
import * as svgShape from "./svgShape.js";
import {UIElements, warningBox} from "./uiElements.js";
import * as sidebar from "./sidebar.js";
import { v4 as uuidv4 } from 'uuid';

window.initDrawingQuestion = function (rootElement, isTeacher, isPreview) {

    /**
     * @typedef Cursor
     * @type {Object}
     * @property {number} x
     * @property {number} y
     *
     * @typedef propObj
     * @type {Object.<string, string|number>}
     *
     * @typedef ELOptions
     * @type {Object.<string, boolean|AbortSignal>|boolean}
     * @typedef ELEvent
     * @type {Object.<string, Function|ELOptions>}
     * @typedef ELEvents
     * @type {Object.<string, ELEvent>}
     * @typedef EventListenerSettings
     * @type {Object.<string, HTMLElement|ELEvents>}
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

                    processGridToggleChange();
                    clearLayers();
                    retrieveSavedDrawingData();

                    Canvas.setCurrentLayer(Canvas.params.currentLayer);

                    drawingApp.firstInit = false;
                    clearInterval(pollingFunction);
                }
            });

            setCorrectZIndex();
            setCursorTypeAccordingToCurrentType();
            updateOpacitySliderColor();

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
         * @param {} thisArg Specific this context when needed.
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
            if(elem) {
                elem.addEventListener(type, (evt) => {
                    func(evt);
                }, options);
            }
        },
        currentToolIs(toolname) {
            return this.params.currentTool === toolname;
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
                pan: {
                    enabled: false,
                    startCoordinates: {x: 0, y: 0},
                },
                domMatrix: new DOMMatrix(),
                zoomFactor: 1,
            },
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
                    }
                }
            }
        },
        {
            element: UI.elemOpacityNumber,
            events: {
                "input": {
                    callback: updateElemOpacityRangeInput,
                }
            }
        },
        {
            element: UI.elemOpacityRange,
            events: {
                "input": {callback: updateElemOpacityNumberInput},
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
            element: UI.strokeWidth,
            events: {
                "input": {
                    callback: () => {
                        valueWithinBounds(UI.strokeWidth);
                    }
                },
                "blur": {
                    callback: () => {
                        handleStrokeButtonStates()
                    }
                }
            }
        },
        {
            element: UI.decrStroke,
            events: {
                "click": {
                    callback: () => {
                        UI.strokeWidth.stepDown();
                        handleStrokeButtonStates()
                    },
                },
                "focus": {
                    callback: () => {
                        UI.strokeWidth.classList.add("active");
                    },
                },
                "blur": {
                    callback: () => {
                        UI.strokeWidth.classList.remove("active");
                    },
                },
            }
        },
        {
            element: UI.incrStroke,
            events: {
                "click": {
                    callback: () => {
                        UI.strokeWidth.stepUp();
                        handleStrokeButtonStates()
                    },
                },
                "focus": {
                    callback: () => {
                        UI.strokeWidth.classList.add("active");
                    },
                },
                "blur": {
                    callback: () => {
                        UI.strokeWidth.classList.remove("active");
                    },
                },
            }
        },
        {
            element: UI.textSize,
            events: {
                "input": {
                    callback: () => {
                        valueWithinBounds(UI.textSize);
                    }
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
                        handleTextSizeButtonStates()
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
                        handleTextSizeButtonStates()
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
            element: UI.fillColor,
            events: {
                "input": {
                    callback: updateOpacitySliderColor,
                }
            }
        },
        {
            element: UI.fillOpacityNumber,
            events: {
                "input": {
                    callback: updateFillOpacityRangeInput,
                }
            }
        },
        {
            element: UI.fillOpacityRange,
            events: {
                "input": {callback: updateFillOpacityNumberInput},
                "focus": {
                    callback: () => {
                        UI.fillOpacityNumber.classList.add("active");
                    },
                },
                "blur": {
                    callback: () => {
                        UI.fillOpacityNumber.classList.remove("active");
                    },
                },
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
                        } else if(hasNoAnswerObjects()) {
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
                        if(hasNoAnswerObjects()){
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
            question: btoa(Canvas.layers.question.svg.innerHTML),
            answer: btoa(Canvas.layers.answer.svg.innerHTML),
            grid: btoa(Canvas.layers.grid.svg.innerHTML)
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
            const parentID = `question`;
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
            const decodedString = atob(layerData.data);
            UI.svgLayerToRender.innerHTML = decodedString;
            renderShapesFromSvgLayerString(layerData.name);
        }
    }

    function fitDrawingToScreen() {
        // panDrawingCenterToScreenCenter();

        while (!drawingFitsScreen()) {
            zoomOutOneStep();
        }
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
        if(centerDrawingToOrigin.dy !== 0 || centerDrawingToOrigin.dx !== 0) {
            if(UI.gridToggle.checked) {
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
        if (bbox.x < screenBounds.left
            || bbox.y < screenBounds.top) return false;
        else return true;
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
            const shapeID = groupElement.id,
                shapeType = shapeID.substring(0, shapeID.indexOf("-"));
            const newShape = makeNewSvgShapeWithSidebarEntry(
                shapeType,
                props,
                layerName,
                true,
                !(!drawingApp.isTeacher() && layerName === "question")
            );
            Canvas.layers[layerName].shapes[shapeID] = newShape;
            newShape.svg.addHighlightEvents();
        }
        UI.svgLayerToRender.innerHTML = "";
    }

    function copyAllAttributesFromElementToObject(element) {
        const attributes = {};
        for (const attr of element.attributes) {
            attributes[attr.name] = attr.value;
        }

        if (element.nodeName === "TEXT" && !attributes["data-textcontent"])
            attributes["data-textcontent"] = element.textContent;
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
            question: btoa(clearImageSources(Canvas.layers.question.svg)),
            answer: btoa(clearImageSources(Canvas.layers.answer.svg))
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

        const b64Strings = encodeSvgLayersAsBase64Strings();
        const grid = (Canvas.layers.grid.params.hidden) ? "0.00" : drawingApp.params.gridSize.toString();

        const panGroupSize = getPanGroupSize();

        const livewireComponent = getClosestLivewireComponentByAttribute(rootElement, 'questionComponent');

        const cleanedSvg = cleanedBase64EncodedStrings();

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

        return new Promise((resolve, reject) => {
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
        newImage.setAttribute('src', 'data:image/svg+xml;base64,' + btoa(new XMLSerializer().serializeToString(svg)));

        return await getDataUrlFromCanvasByImage(newImage);
    }

    async function compressedImageUrl(image, scaleFactor) {
        const newImage = new Image(image.width * scaleFactor, image.height * scaleFactor)
        newImage.src = image.src;

        return await getDataUrlFromCanvasByImage(newImage);
    }

    function prepareSvgForConversion(svg, panGroupSize) {
        svg.setAttribute('viewBox', `${panGroupSize.x} ${panGroupSize.y} ${panGroupSize.width} ${panGroupSize.height}`);
        svg.setAttribute('width', `${panGroupSize.width}`);
        svg.setAttribute('height', `${panGroupSize.height}`);
        svg.querySelector('#svg-pan-zoom-group').setAttribute('transform', '');
        svg.querySelector('#svg-grid-group').setAttribute('stroke', '#c3d0ed');
        return svg;
    }

    function hasNoAnswerObjects() {
        return ! Object.keys(Canvas.layers.answer.shapes).length;
    }

    function toggleSaveNoAnswersConfirm() {
        UI.saveNoAnswersConfirm.classList.toggle('open');
    }

    function toggleSaveConfirm() {
        UI.saveConfirm.classList.toggle('open');
    }

    function hasHiddenLayers() {
        if(drawingApp.isTeacher()){
            if(Canvas.params.currentLayer == 'question'){
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
        if(!layerObject.props.id.includes(layerObject.Canvas.params.currentLayer)) return;

        const selectedEl = rootElement.querySelector('.selected');
        const selectedSvgShape = evt.target.closest("g.shape");

        if (selectedEl) removeSelectState(selectedEl);
        if (selectedEl === selectedSvgShape) return;

        addSelectState(selectedSvgShape);
    }

    function removeSelectState(element) {
        element.classList.remove('selected')
        rootElement.querySelector('#shape-'+element.id).classList.remove('selected')
    }

    function addSelectState(element) {
        element.classList.add('selected')
        rootElement.querySelector('#shape-'+element.id).classList.add('selected')
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

        if (diffX < delta && diffY < delta) {
            return false;
        }

        return true;
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
        updateCursorPosition(evt);

        setMousedownPosition(evt)

        if (Canvas.params.focusedShape)
            Canvas.params.focusedShape = null;

        Canvas.unhighlightShapes();

        if (evt.touches?.length == 2) {
            startPan(evt);
        } else if (drawingApp.params.currentTool == "drag") {
            startDrag(evt);
        } else {
            startDraw(evt);
        }
    }

    function startDrag(evt) {
        const shapeGroup = evt.target.closest(".shape");
        if (!shapeGroup) return;

        const layerID = shapeGroup.parentElement.id;
        const layerObject = Canvas.layers[Canvas.layerID2Key(layerID)];
        if (!shapeMayBeDragged(shapeGroup, layerObject)) return;

        const selectedSvgShape = evt.target.closest("g.shape");
        let existingTransforms = selectedSvgShape.transform.baseVal;

        if (!elementHasTransforms(existingTransforms)) {
            createNewTranslateTransform(selectedSvgShape);
        } else if (!firstTransformIsOfTypeTranslate(existingTransforms)) {
            createNewTranslateTransform(selectedSvgShape);
        }

        const translateOfSvgShape = getFirstTransform(existingTransforms);

        Canvas.params.drag = {
            enabled: true,
            offsetCursorToMidPoint: calculateCursorToMidPointOffset(translateOfSvgShape),
            translateOfSvgShape: translateOfSvgShape,
        };

        selectedSvgShape.classList.add("dragging");
        selectedSvgShape.parentElement.classList.add("child-dragging");
    }

    function shapeMayBeDragged(shapeGroup, layerObject) {
        return shapeGroup.classList.contains("draggable") && !layerObject.params.locked && layerObject.props.id.includes(layerObject.Canvas.params.currentLayer);
    }

    function elementHasTransforms(transforms) {
        return transforms.length !== 0;
    }

    function createNewTranslateTransform(shape) {
        let translate = UI.svgCanvas.createSVGTransform();
        translate.setTranslate(0, 0);
        shape.transform.baseVal.insertItemBefore(translate, 0);
    }

    function firstTransformIsOfTypeTranslate(transforms) {
        return getFirstTransform(transforms)?.type === SVGTransform.SVG_TRANSFORM_TRANSLATE;
    }

    function getFirstTransform(transforms) {
        if(transforms.numberOfItems > 0) return transforms.getItem(0);
    }

    function calculateCursorToMidPointOffset(translate) {
        const cursorPosition = Canvas.params.cursorPosition;
        return {
            x: cursorPosition.x - translate.matrix.e,
            y: cursorPosition.y - translate.matrix.f,
        };
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
        switch (type) {
            case "rect":
                return {
                    "x": cursorPosition.x,
                    "y": cursorPosition.y,
                    "width": 0,
                    "height": 0,
                    "fill":
                        UI.fillOpacityNumber.value == 0 ? "none" : UI.fillColor.value,
                    "fill-opacity": parseFloat(UI.fillOpacityNumber.value / 100),
                    "stroke": UI.strokeColor.value,
                    "stroke-width": UI.strokeWidth.value,
                    "opacity": parseFloat(UI.elemOpacityNumber.value / 100),
                };
            case "circle":
                return {
                    "cx": cursorPosition.x,
                    "cy": cursorPosition.y,
                    "r": 0,
                    "fill":
                        UI.fillOpacityNumber.value == 0 ? "none" : UI.fillColor.value,
                    "fill-opacity": parseFloat(UI.fillOpacityNumber.value / 100),
                    "stroke": UI.strokeColor.value,
                    "stroke-width": UI.strokeWidth.value,
                    "opacity": parseFloat(UI.elemOpacityNumber.value / 100),
                };
            case "line":
                return {
                    "x1": cursorPosition.x,
                    "y1": cursorPosition.y,
                    "x2": cursorPosition.x,
                    "y2": cursorPosition.y,
                    "marker-end": `url(#svg-${drawingApp.params.endmarkerType}-line)`,
                    "stroke": UI.strokeColor.value,
                    "stroke-width": UI.strokeWidth.value,
                    "opacity": parseFloat(UI.elemOpacityNumber.value / 100),
                };
            case "freehand":
                return {
                    "d": `M ${cursorPosition.x},${cursorPosition.y}`,
                    "fill": "none",
                    "stroke": UI.strokeColor.value,
                    "stroke-width": UI.strokeWidth.value,
                    "opacity": parseFloat(UI.elemOpacityNumber.value / 100),
                };
            case "text":
                return {
                    "x": cursorPosition.x,
                    "y": cursorPosition.y,
                    "fill": UI.textColor.value,
                    "stroke-width": 0,
                    "value": 'abc',
                    "opacity": parseFloat(UI.elemOpacityNumber.value / 100),
                    "style": `${drawingApp.params.boldText ? "font-weight: bold;" : ""} font-size: ${UI.textSize.value / 16}rem`,
                };
            default:
        }
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
        } else if (Canvas.drawing()) {
            Canvas.params.draw.newShape.svg.onDraw?.(evt, cursorPosition);
        }

        Canvas.params.cursorPosition = cursorPosition;

        if(evt.type === 'touchmove') {
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
        const difference = calculateDistanceCursor(
            Canvas.params.drag.offsetCursorToMidPoint,
            Canvas.params.cursorPosition
        );
        Canvas.params.drag.translateOfSvgShape.setTranslate(difference.dx, difference.dy);
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
        } else if (Canvas.panning()) {
            stopPan();
        }
    }

    function stopDraw(evt) {
        const newShape = Canvas.params.draw.newShape;
        newShape.svg.onDrawEnd?.(
            evt,
            Canvas.params.cursorPosition
        );
        newShape.svg.addHighlightEvents();
        Canvas.params.highlightedShape = newShape;
        Canvas.params.draw.newShape = null;
    }

    function stopDrag() {
        UI.svgCanvas.querySelector("g.dragging").classList.remove("dragging");
        UI.svgCanvas.querySelector(".child-dragging").classList.remove("child-dragging");
        Canvas.params.drag.enabled = false;
    }

    function stopPan() {
        Canvas.params.pan.enabled = false;
    }

    function processToolChange(evt) {
        let currentTool = drawingApp.params.currentTool,
            newTool = determineNewTool(evt);
        if (currentTool == newTool) return;
        drawingApp.params.currentTool = newTool;
        makeSelectedBtnActive(evt.currentTarget);
        enableSpecificPropSelectInputs();
        setCursorTypeAccordingToCurrentType();
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
        if (endOfSlice == startOfSlice - 1 || endOfSlice == -1) {
            endOfSlice = startOfSlice - 1;
            startOfSlice = 0;
        }
        return id.slice(startOfSlice, endOfSlice);
    }

    function processEndmarkerTypeChange(evt) {
        drawingApp.params.endmarkerType = determineNewEndmarkerType(evt);
        makeSelectedBtnActive(evt.currentTarget);
    }

    function determineNewEndmarkerType(evt) {
        return evt.currentTarget.id;
    }


    function processUploadedImages(evt) {
        const livewireComponent = getClosestLivewireComponentByAttribute(drawingApp.params.root, 'questionComponent')

        for (const fileURL of evt.target.files) {
            if (fileURL.size/ (1024 * 1024) > 4 ) {
                Notify.notify ('max 4 mb please');
                return false;
            }
            const reader = new FileReader();

            const identifier = uuidv4();
            UI.submitBtn.disabled = true
            livewireComponent.upload(`cmsPropertyBag.images.${Canvas.params.currentLayer}.${identifier}`, fileURL, (fileName) => {
                // Success callback.
                UI.submitBtn.disabled = false
            }, () => {
                // Error callback.
                UI.submitBtn.disabled = false
            }, (event) => {
                // Progress callback.
            })

            reader.readAsDataURL(fileURL);

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
        }
    }

    function updateElemOpacityNumberInput() {
        valueWithinBounds(UI.elemOpacityRange);
        UI.elemOpacityNumber.value = UI.elemOpacityRange.value;
        updateOpacitySliderColor();
    }

    function updateElemOpacityRangeInput() {
        if (!valueWithinBounds(UI.elemOpacityNumber)) return;
        UI.elemOpacityRange.value = UI.elemOpacityNumber.value;
        updateOpacitySliderColor();
    }

    function updateOpacitySliderColor() {
        setSliderColor(UI.fillOpacityRange, UI.fillColor.value);
        setSliderColor(UI.elemOpacityRange);
    }

    /**
     * Sets the '--slider-color' property on the _slider_ to a linear-gradient
     * where the color left of the knob is determined by _leftColorHexValue_.
     * @param {HTMLElement} slider The slider to update.
     * @param {?string} leftColorHexValue The hexadecimal value for the color left of the knob.
     */
    function setSliderColor(
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

    function updateFillOpacityNumberInput() {
        valueWithinBounds(UI.fillOpacityRange);
        UI.fillOpacityNumber.value = UI.fillOpacityRange.value;
        updateOpacitySliderColor();
    }

    function updateFillOpacityRangeInput() {
        if (!valueWithinBounds(UI.fillOpacityNumber)) return;
        UI.fillOpacityRange.value = UI.fillOpacityNumber.value;
        updateOpacitySliderColor();
    }

    function valueWithinBounds(inputElem) {
        let value = parseFloat(inputElem.value),
            max = parseFloat(inputElem.max),
            min = parseFloat(inputElem.min);
        if (Number.isNaN(value) || value == 0) {
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

        return {
            x: panGroupSize.x,
            y: panGroupSize.y,
            width: panGroupSize.width,
            height: panGroupSize.height
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

    function handleStrokeButtonStates() {
        const {currentValue, min, max} = getBoundsForInputElement(UI.strokeWidth);

        UI.decrStroke.disabled = currentValue === min;
        UI.incrStroke.disabled = currentValue === max;
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
        scale = (width*height) / (parent.viewBox.baseVal.width * parent.viewBox.baseVal.height)
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

