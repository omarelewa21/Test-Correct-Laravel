<div id="drawing-tool"
     wire:ignore
>
    <div class="section-container">
        <section>
            <div id="tools">
                <div id="elements" class="tools-group">
                    <button id="drag-btn" class="active" title="Verplaatsen" data-button-group="tool">
                        <div>
                            <x-icon.drag/>
                        </div>
                    </button>
                    <button id="add-rect-btn" title="Rechthoek" data-button-group="tool">
                        <div>
                            <x-icon.square/>
                        </div>
                    </button>
                    <button id="add-circle-btn" title="Cirkel" data-button-group="tool">
                        <div>
                            <x-icon.circle/>
                        </div>
                    </button>
                    <button id="add-line-btn" title="Rechte lijn" data-button-group="tool">
                        <div>
                            <x-icon.line/>
                        </div>
                    </button>
                    <button id="add-freehand-btn" title="Penlijn" data-button-group="tool">
                        <div>
                            <x-icon.freehand/>
                        </div>
                    </button>
                    <button id="add-text-btn" title="Tekst" data-button-group="tool">
                        <div>
                            <x-icon.text/>
                        </div>
                    </button>
                    @if(Auth::user()->isA('teacher'))
                        <button title="Afbeelding">
                            <label for="img-upload" id="img-upload-label">
                                <x-icon.image-upload/>
                            </label>
                        </button>
                        <input type="file" id="img-upload" style="display: none;" accept="image/jpeg, image/png"
                               multiple>
                    @endif
                </div>
                @if(Auth::user()->isA('teacher'))
                    <div id="grid-background" class="tools-group">
                        <input type="checkbox" id="grid-toggle" style="display: none;" autocomplete="off">
                        <button title="Zet grid aan/uit">
                            <label id="grid-toggle-btn" for="grid-toggle">
                                <x-icon.grid/>
                            </label>
                        </button>
                        <input type="number" id="grid-size" min="0.5" max="5" value="1" step="0.5" title="Afmeting grid"
                               disabled>
                        <button id="decr-grid-size" class="Secondary" title="Verklein grid" disabled>
                            <div>
                                <x-icon.min-2/>
                            </div>
                        </button>
                        <button id="incr-grid-size" class="Secondary" title="Vergroot grid" disabled>
                            <div>
                                <x-icon.plus-2/>
                            </div>
                        </button>
                    </div>
                @endif
            </div>
            <div id="properties">
                <div class="property-group" id="text-style">
                    <input type="color" name="text-color" id="text-color" autocomplete="off" title="Tekstkleur">
                    <input type="number" name="text-size" id="text-size" min="10" max="50" value="15" step="1"
                           autocomplete="off" title="Tekstgrootte">
                    <button id="decr-text-size" class="Secondary" title="Vergroot tekst">
                        <div>
                            <x-icon.min-2/>
                        </div>
                    </button>
                    <button id="incr-text-size" class="Secondary" title="Veklein tekst">
                        <div>
                            <x-icon.plus-2/>
                        </div>
                    </button>
                    <input type="checkbox" id="bold-toggle" style="display: none;" autocomplete="off">
                    <button title="Zet dikgedrukt aan/uit">
                        <label for="bold-toggle" id="bold-text">
                            <x-icon.bold/>
                        </label>
                    </button>
                </div>

                <div class="property-group" id="opacity" title="Ondoorzichtigheid">
                    <input type="number" name="opacity" id="elem-opacity-number" min="0" max="100" value="100" step="1"
                           autocomplete="off">
                    <input class="drawing-toolbar-slider" type="range" name="opacity" id="elem-opacity-range" min="0"
                           max="100" value="100" step="1" autocomplete="off">
                </div>

                <div class="property-group" id="edge">
                    <input type="color" name="stroke-color" id="stroke-color" autocomplete="off" title="Randkleur">
                    <input type="number" name="stroke-width" id="stroke-width" min="0" max="100" value="1"
                           autocomplete="off" title="Randdikte">
                    <button id="decr-stroke" class="Secondary" title="Vergroot randdikte">
                        <div>
                            <x-icon.min-2/>
                        </div>
                    </button>
                    <button id="incr-stroke" class="Secondary" title="Verklein randdikte">
                        <div>
                            <x-icon.plus-2/>
                        </div>
                    </button>
                </div>

                <div class="property-group" id="fill">
                    <input type="color" name="fill-color" id="fill-color" value="#aaaaaa" autocomplete="off"
                           title="Opvulkleur">
                    <input type="number" name="fill-opacity" id="fill-opacity-number" min="0" max="100" value="0"
                           step="1" autocomplete="off" title="Ondoorzichtigheid opvulkleur">
                    <input class="drawing-toolbar-slider" type="range" name="fill-opacity" id="fill-opacity-range"
                           min="0" max="100" value="0" step="1" autocomplete="off" title="Ondoorzichtigheid opvulkleur">
                </div>

                <div class="property-group" id="endmarker-type" title="Type lijneinde">
                    <div id="endmarker-type-wrapper">
                        <button class="endmarker-type active" id="no-endmarker" data-button-group="endmarker-type">
                            <x-icon.no-endmarker/>
                        </button>
                        <button class="endmarker-type" id="filled-arrow" data-button-group="endmarker-type">
                            <x-icon.filled-arrow/>
                        </button>
                        <button class="endmarker-type" id="two-lines-arrow" data-button-group="endmarker-type">
                            <x-icon.two-lines-arrow/>
                        </button>
                        <button class="endmarker-type" id="filled-dot" data-button-group="endmarker-type">
                            <x-icon.filled-dot/>
                        </button>
                    </div>
                </div>
            </div>
        </section>
        <button id="exit-btn" title="Sluiten" @click="show = false">
            <x-icon.close/>
        </button>
    </div>
    <div id="canvas-sidebar-container" class="overflow-auto">
        <article id="canvas" class="overflow-hidden">
            <svg id="svg-canvas" xmlns="http://www.w3.org/2000/svg" class="overflow-hidden">
                <defs>
                    <marker id="svg-filled-arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6"
                            orient="auto-start-reverse">
                        <polyline points="0,0 10,5 0,10" stroke="none"/>
                    </marker>
                    <marker id="svg-two-lines-arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6"
                            markerHeight="6" orient="auto-start-reverse">
                        <polyline points="1,1 9,5 1,9" fill="none"/>
                    </marker>
                    <marker id="svg-filled-dot" viewBox="0 0 12 12" refX="6" refY="6" markerWidth="6" markerHeight="6"
                            orient="auto-start-reverse">
                        <circle cx="6" cy="6" r="5" stroke="none"/>
                    </marker>
                </defs>
                <g id="svg-pan-zoom-group" transform="matrix(1 0 0 1 0 0)">
                    <g id="svg-grid-group" stroke="var(--all-BlueGrey)" stroke-width="1"></g>
                    <g id="svg-question-group"></g>
                    <g id="svg-answer-group"></g>
                </g>
            </svg>
            <div id="zoom-component" class="percentfield-container">
                <button id="decr-zoom" class="Secondary min-btn" title="Zoom out">
                    <x-icon.min-2/> 
                </button>
                <input type="text" id="zoom-level" class="percentfield" value="100%" disabled>
                <button id="incr-zoom" class="Secondary plus-btn" title="Zoom in">
                    <x-icon.plus-2/>
                </button>
            </div>
            <div id="cursor-pos-container" class="coord-box">
                <div id="cursor-pos-title">
                    {{ __('cms.coordinaten')}}
                </div>
                <div id="cursor-pos">
                    X 0, Y 0
                </div>
            </div>
        </article>
        <aside class="relative">
            <input type="checkbox" name="sidebar-toggle" id="sidebar-toggle">
            <div id="sidebar">
                <label id="sidebar-btn" for="sidebar-toggle">
                    <x-icon.chevron/>
                </label>
                <div id="layers-container">
                    <div id="layers-heading"></div>
                </div>
                <div id="submit">
                    <button class="CTA" id="submit-btn" @click="show = false">
                        Opslaan
                    </button>
                </div>
            </div>
        </aside>
    </div>
</div>
<template id="shape-group-template">
    <div class="shape-container" id="shape-n" draggable="true">
        <div class="flex items-center w-full justify-between">
            <span class="shape-title">If you read this, report a bug</span>
            <div class="btn-group">
                <button class="shape-btn remove-btn" title="Verwijderen">
                    <x-icon.trash/>
                </button>
                <button class="shape-btn lock-btn" data-title-locked="Ontgrendelen" data-title-unlocked="Vergrendelen"
                        title="Vergrendelen">
                    <x-icon.unlocked/>
                    <x-icon.locked/>
                </button>
                <button class="shape-btn hide-btn" data-title-hidden="Tonen" data-title-unhidden="Verbergen"
                        title="Verbergen">
                    <x-icon.preview/>
                    <x-icon.preview-off/>
                </button>
                <button class="shape-btn drag-btn" title="Verslepen">
                    <x-icon.grab/>
                </button>
            </div>
        </div>
    </div>
</template>
<template id="layer-group-template">
    <div class="layer-group">
        <div class="header">
            <div class="header-container">
                <div class="header-title-container flex items-center">
                    <span class="header-title"></span>
                    <span class="indicator"></span>
                </div>
                <div class="btn-group">
                    <button class="layer-btn remove-btn hidden" style="display: none;" title="Verwijderen">
                        <x-icon.trash/>
                    </button>
                    <button class="layer-btn lock-btn hidden" style="display: none;" data-title-locked="Ontgrendelen"
                            data-title-unlocked="Vergrendelen" title="Vergrendelen">
                        <x-icon.unlocked/>
                        <x-icon.locked style="display: none"/>
                    </button>
                    <button class="layer-btn hide-btn" style="display: none;" data-title-hidden="Tonen"
                            data-title-unhidden="Verbergen" title="Verbergen">
                        <x-icon.preview/>
                        <x-icon.preview-off class="text-midgrey" style="display: none"/>
                    </button>
                    <button class="layer-btn add-layer-btn hidden" title="Laag toevoegen">
                        <x-icon.plus-2/>
                    </button>
                </div>
            </div>
        </div>
        <div class="shapes-group"></div>
    </div>
</template>
<template id="warningbox-template">
    <div class="warning">
        <div class="warning-text">
            <x-icon.exclamation/>
        </div>
    </div>
</template>
<template id="svg-layer-to-render"></template>