export const svgNS = "http://www.w3.org/2000/svg";

export const shapeTypeWithRespectiveSvgClass = {
    rect: "Rectangle",
    circle: "Circle",
    line: "Line",
    text: "Text",
    image: "Image",
    freehand: "Path",
};

export const validSvgElementKeys = {
    global: ["style", "class", "id",
        "stroke", "stroke-width", "stroke-dasharray", "fill", "fill-opacity", "opacity",
        "marker-start", "marker-mid", "marker-end"],
    rect: ["x", "y", "width", "height", "rx", "ry", "pathLength"],
    circle: ["cx", "cy", "r", "pathLength"],
    ellipse: ["cx", "cy", "rx", "ry", "r", "pathLength"],
    line: ["x1", "y1", "x2", "y2", "pathLength"],
    path: ["d"],
    image: ["x", "y", "width", "height", "href", "preserveAspectRatio", "identifier"],
    text: ["x", "y", "dx", "dy", "rotate", "lengthAdjust", "data-textcontent", "font-family"],
    g: ["transform"],
};

export const shapePropertiesAvailableToUser = {
    drag: [],
    freehand: ["pen-freehand"],
    rect: ["stroke-rect", "fill-rect"],
    circle: ["stroke-circle", "fill-circle"],
    line: ["pen-line", "endmarker-type"],
    text: ["opacity", "text-style"],
};

export const validHtmlElementKeys = {
    global: ["style", "class", "id", "title"],
    input: ["placeholder", "type", "value", "autocomplete", "spellcheck"],
    button: ["type"],
};

export const nameInSidebarEntryForShape = {
    rect: "Rechthoek",
    circle: "Cirkel",
    line: "Lijn",
    text: "Tekst",
    image: "Afbeelding",
    path: "Penlijn",
};

export const pixelsPerCentimeter = 35.43307;

export const zoomParams = {
    STEP: 0.25,
    MAX: 5,
    MIN: 0.25,
};

export const panParams = {
    STEP: 5,
};

export const elementClassNameForType = {
    "rect": "Rectangle",
    "circle": "Circle",
    "ellipse": "Ellipse",
    "line": "Line",
    "text": "Text",
    "image": "Image",
    "path": "Path"
};

export const resizableSvgShapes = [
  "rect", "circle", "image"
];
