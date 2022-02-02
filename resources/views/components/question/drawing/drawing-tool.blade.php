<div id="drawing-tool"
     wire:ignore
     x-init="$watch('show', show => { if (show) drawingApp.init(); })"
>
    <div class="section-container">
        <section>
            <div id="tools">
                <div id="elements" class="tools-group">
                    <button id="drag-btn" class="active" title="Verplaatsen" data-button-group="tool">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16">
                                <g stroke="currentColor" stroke-width="2" fill="none" fill-rule="evenodd"
                                   stroke-linecap="round">
                                    <path d="M2 8h12m-1-2l2 2-2 2M3 6L1 8l2 2M8 2v12M10 12.5l-2 2-2-2M6 3.5l2-2 2 2"/>
                                </g>
                            </svg>
                        </div>
                    </button>
                    <button id="add-rect-btn" title="Rechthoek" data-button-group="tool">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16">
                                <path d="M1 1h14v14H1z" stroke="currentColor" stroke-width="2" fill="none"
                                      fill-rule="evenodd" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </button>
                    <button id="add-circle-btn" title="Cirkel" data-button-group="tool">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16">
                                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2" fill="none"
                                        fill-rule="evenodd" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </button>
                    <button id="add-line-btn" title="Rechte lijn" data-button-group="tool">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16">
                                <path d="M1 15L15 1" stroke="currentColor" stroke-width="2" fill="none"
                                      fill-rule="evenodd" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </button>
                    <button id="add-freehand-btn" title="Penlijn" data-button-group="tool">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                <g fill="none" fill-rule="evenodd" stroke-linecap="round">
                                    <g stroke="currentColor" stroke-width="2">
                                        <path d="M5 15c-2.667-.333-4-1.5-4-3.5 0-4.14 3.454-5.084 6.977-3.477C12.326 10.008 14.667 7.667 15 1"
                                              transform="translate(-244 -37) translate(10 10) translate(222 15) translate(12 12)"/>
                                    </g>
                                </g>
                            </svg>
                        </div>
                    </button>
                    <button id="add-text-btn" title="Tekst" data-button-group="tool">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16">
                                <path d="M11.703 16v-1.164l-1.549-.129-.407-.372V1.362h3.854l.35.303.407 2.55H16V0H0v4.215h1.642l.407-2.55.361-.303h4.111v12.973l-.396.372-1.56.129V16z"
                                      fill="currentColor" fill-rule="nonzero"/>
                            </svg>
                        </div>
                    </button>
                    @if(Auth::user()->isA('teacher'))
                        <button title="Afbeelding">
                            <label for="img-upload" id="img-upload-label">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16">
                                    <g fill="none" fill-rule="evenodd">
                                        <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                                              d="M1 1h14v14H1z"/>
                                        <circle cx="4.5" cy="5.5" r="1.5" fill="currentColor"/>
                                        <path fill="currentColor"
                                              d="M13.014 6.767C12.296 6.283 11.431 6 10.5 6c-1.27 0-1.968.242-3.234 1.37-1.011.903-2.81 1.403-4.258.804C3.003 8.39 3 9.667 3 12h10c0-2.333.005-4.078.014-5.233z"/>
                                    </g>
                                </svg>
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
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16">
                                    <g stroke="currentColor" stroke-width="2" fill="none" fill-rule="evenodd">
                                        <path stroke-linejoin="round" d="M1 1h14v14H1z"/>
                                        <path stroke-linecap="round" d="M2 8h12m-6 6V2"/>
                                    </g>
                                </svg>
                            </label>
                        </button>
                        <input type="number" id="grid-size" min="0.5" max="5" value="1" step="0.5" title="Afmeting grid"
                               disabled>
                        <button id="decr-grid-size" class="Secondary" title="Verklein grid" disabled>
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16">
                                    <path d="M2 8h10" stroke="currentColor" stroke-width="3" fill="none"
                                          fill-rule="evenodd" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </button>
                        <button id="incr-grid-size" class="Secondary" title="Vergroot grid" disabled>
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16">
                                    <g stroke="currentColor" stroke-width="3" fill="none" fill-rule="evenodd"
                                       stroke-linecap="round" transform="translate(2.861 .5)">
                                        <path d="M-.861 7.5h10M4.139 12.5v-10"/>
                                    </g>
                                </svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16">
                                <path d="M2 8h10" stroke="currentColor" stroke-width="3" fill="none" fill-rule="evenodd"
                                      stroke-linecap="round"/>
                            </svg>
                        </div>
                    </button>
                    <button id="incr-text-size" class="Secondary" title="Veklein tekst">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16">
                                <g stroke="currentColor" stroke-width="3" fill="none" fill-rule="evenodd"
                                   stroke-linecap="round" transform="translate(2.861 .5)">
                                    <path d="M-.861 7.5h10M4.139 12.5v-10"/>
                                </g>
                            </svg>
                        </div>
                    </button>
                    <input type="checkbox" id="bold-toggle" style="display: none;" autocomplete="off">
                    <button title="Zet dikgedrukt aan/uit">
                        <label for="bold-toggle" id="bold-text">
                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="16">
                                <path fill="currentColor"
                                      d="M5.486 16c.937-.046 1.779-.244 2.525-.594.313-.145.621-.334.926-.566.305-.232.575-.514.812-.846.236-.331.426-.724.571-1.177.145-.453.217-.977.217-1.571 0-.122-.006-.284-.017-.486-.011-.202-.046-.423-.103-.663-.057-.24-.145-.487-.263-.743-.118-.255-.282-.503-.491-.743-.21-.24-.47-.46-.783-.662-.312-.202-.697-.368-1.154-.498.053-.015.144-.059.274-.131.13-.072.278-.171.446-.297.167-.126.34-.276.52-.452.179-.175.343-.38.491-.617.149-.236.27-.5.366-.794.095-.293.143-.619.143-.977 0-.693-.128-1.301-.383-1.823s-.606-.958-1.052-1.309C8.086.701 7.564.438 6.966.263 6.368.088 5.73 0 5.05 0H1.737C1.303 0 .967.074.731.223.495.37.324.568.217.81c-.107.244-.17.522-.188.835-.02.312-.029.632-.029.96V13.37c0 .336.01.66.029.972.019.312.081.592.188.84.107.247.278.446.514.594.237.149.572.223 1.006.223h3.749zm-.549-9.417H3.474V2.697h1.372c.343.03.651.118.925.263.115.069.227.152.338.251.11.1.21.22.297.36.087.141.158.309.211.503.053.195.08.421.08.68 0 .229-.025.43-.074.606-.05.175-.116.326-.2.451-.084.126-.18.235-.286.326-.107.092-.213.164-.32.217-.259.137-.552.214-.88.229zm.206 6.72H3.474V9.006h1.577c.374.03.709.125 1.006.285.122.069.244.159.366.269.122.11.23.244.326.4.095.156.171.339.228.549.057.21.086.455.086.737 0 .259-.027.485-.08.68-.053.194-.126.365-.217.514-.092.149-.195.272-.309.371-.114.1-.232.18-.354.24-.282.153-.602.237-.96.252z"
                                      fill-rule="nonzero"/>
                            </svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16">
                                <path d="M2 8h10" stroke="currentColor" stroke-width="3" fill="none" fill-rule="evenodd"
                                      stroke-linecap="round"/>
                            </svg>
                        </div>
                    </button>
                    <button id="incr-stroke" class="Secondary" title="Verklein randdikte">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16">
                                <g stroke="currentColor" stroke-width="3" fill="none" fill-rule="evenodd"
                                   stroke-linecap="round" transform="translate(2.861 .5)">
                                    <path d="M-.861 7.5h10M4.139 12.5v-10"/>
                                </g>
                            </svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                <g fill="none" fill-rule="evenodd" stroke-linecap="round">
                                    <g stroke="currentColor" stroke-width="2">
                                        <path d="M1 8L15 8"
                                              transform="translate(-1097 -37) translate(10 10) translate(637 15) translate(438) translate(12 12)"/>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <button class="endmarker-type" id="filled-arrow" data-button-group="endmarker-type">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                <g fill="none" fill-rule="evenodd" stroke-linecap="round">
                                    <g stroke-width="2">
                                        <path stroke="currentColor" d="M1 8L13 8"
                                              transform="translate(-1137 -37) translate(10 10) translate(637 15) translate(438) translate(52 12)"/>
                                        <path fill="currentColor" d="M10 5L15 8 10 11z"
                                              transform="translate(-1137 -37) translate(10 10) translate(637 15) translate(438) translate(52 12)"/>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <button class="endmarker-type" id="two-lines-arrow" data-button-group="endmarker-type">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                <g fill="none" fill-rule="evenodd" stroke-linecap="round">
                                    <g stroke="currentColor" stroke-width="2">
                                        <path d="M1 8L13 8M11 5L15 8 11 11"
                                              transform="translate(-1177 -37) translate(10 10) translate(637 15) translate(438) translate(92 12)"/>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <button class="endmarker-type" id="filled-dot" data-button-group="endmarker-type">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(-1217 -37) translate(10 10) translate(637 15) translate(438) translate(132 12)">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                              d="M1 8L13 8"/>
                                        <circle cx="13" cy="8" r="3" fill="currentColor"/>
                                    </g>
                                </g>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>
        <button id="exit-btn" title="Sluiten" @click="show = false">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16">
                <g stroke="currentColor" stroke-width="3" fill="none" fill-rule="evenodd" stroke-linecap="round">
                    <path d="M1.5 13.5l11-11M12.5 13.5l-11-11"/>
                </g>
            </svg>
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
                    <g id="svg-question-group"></g>
                    <g id="svg-grid-group" stroke="var(--all-BlueGrey)" stroke-width="1"></g>
                    <g id="svg-answer-group"></g>
                </g>
            </svg>
            <div id="zoom-component" class="percentfield-container">
                <button id="decr-zoom" class="Secondary min-btn" title="Zoom out">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16">
                        <path d="M2 8h10" stroke="currentColor" stroke-width="3" fill="none" fill-rule="evenodd"
                              stroke-linecap="round"/>
                    </svg>
                </button>
                <input type="text" id="zoom-level" class="percentfield" value="100%" disabled>
                <button id="incr-zoom" class="Secondary plus-btn" title="Zoom in">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16">
                        <g stroke="currentColor" stroke-width="3" fill="none" fill-rule="evenodd" stroke-linecap="round"
                           transform="translate(2.861 .5)">
                            <path d="M-.861 7.5h10M4.139 12.5v-10"/>
                        </g>
                    </svg>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="16">
                        <defs>
                            <filter id="a" width="132.4%" height="113.2%" x="-13.8%" y="-5.5%"
                                    filterUnits="objectBoundingBox">
                                <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
                                <feGaussianBlur in="shadowOffsetOuter1" result="shadowBlurOuter1" stdDeviation="9"/>
                                <feColorMatrix in="shadowBlurOuter1" result="shadowMatrixOuter1"
                                               values="0 0 0 0 0.301960784 0 0 0 0 0.341176471 0 0 0 0 0.560784314 0 0 0 0.2 0"/>
                                <feMerge>
                                    <feMergeNode in="shadowMatrixOuter1"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>
                        <g filter="url(#a)" transform="translate(-12 -28)" stroke="currentColor" stroke-width="3"
                           fill="none" fill-rule="evenodd" stroke-linecap="round">
                            <path d="M13.5 30.5l5 5-5 5"/>
                        </g>
                    </svg>
                </label>
                <div id="layers-container"></div>
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
        <span class="shape-title">If you read this, report a bug</span>
        <div class="btn-group">
            <button class="shape-btn remove-btn" title="Verwijderen">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                    <g fill="none" fill-rule="evenodd" stroke-linecap="round">
                        <g stroke="currentColor">
                            <path stroke-width="2"
                                  d="M13.9 5.09l-.817 9c-.047.516-.479.91-.996.91H3.913c-.517 0-.949-.394-.996-.91L2 4h0"/>
                            <path d="M9.5 7.5L9.5 11.5M6.5 7.5L6.5 11.5"/>
                            <path stroke-width="3" d="M1.5 3.5L14.5 3.5"/>
                            <path d="M5.5 2.5v-1c0-.552.448-1 1-1h3c.552 0 1 .448 1 1v1h0"/>
                        </g>
                    </g>
                </svg>
            </button>
            <button class="shape-btn lock-btn" data-title-locked="Ontgrendelen" data-title-unlocked="Vergrendelen"
                    title="Vergrendelen">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16">
                    <defs>
                        <filter id="a" width="132.4%" height="113.2%" x="-13.8%" y="-5.5%"
                                filterUnits="objectBoundingBox">
                            <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
                            <feGaussianBlur in="shadowOffsetOuter1" result="shadowBlurOuter1" stdDeviation="9"/>
                            <feColorMatrix in="shadowBlurOuter1" result="shadowMatrixOuter1"
                                           values="0 0 0 0 0.301960784 0 0 0 0 0.341176471 0 0 0 0 0.560784314 0 0 0 0.2 0"/>
                            <feMerge>
                                <feMergeNode in="shadowMatrixOuter1"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    <g filter="url(#a)" transform="translate(-209 -12)" stroke="currentColor" fill="none"
                       fill-rule="evenodd" stroke-linecap="round">
                        <path stroke-width="3"
                              d="M212 19.5h8c.828 0 1.5.672 1.5 1.5v4c0 .828-.672 1.5-1.5 1.5h-8c-.828 0-1.5-.672-1.5-1.5v-4c0-.828.672-1.5 1.5-1.5z"/>
                        <path stroke-linejoin="round" stroke-width="2"
                              d="M219 15c0-1.105-.895-2-2-2h-2c-1.105 0-2 .895-2 2v4h0"/>
                    </g>
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16">
                    <defs>
                        <filter id="a" width="132.4%" height="113.2%" x="-13.8%" y="-5.5%"
                                filterUnits="objectBoundingBox">
                            <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
                            <feGaussianBlur in="shadowOffsetOuter1" result="shadowBlurOuter1" stdDeviation="9"/>
                            <feColorMatrix in="shadowBlurOuter1" result="shadowMatrixOuter1"
                                           values="0 0 0 0 0.301960784 0 0 0 0 0.341176471 0 0 0 0 0.560784314 0 0 0 0.2 0"/>
                            <feMerge>
                                <feMergeNode in="shadowMatrixOuter1"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    <g filter="url(#a)" transform="translate(-209 -352)" stroke="currentColor" fill="none"
                       fill-rule="evenodd" stroke-linecap="round">
                        <path stroke-width="3"
                              d="M212 359.5h8c.828 0 1.5.672 1.5 1.5v4c0 .828-.672 1.5-1.5 1.5h-8c-.828 0-1.5-.672-1.5-1.5v-4c0-.828.672-1.5 1.5-1.5z"/>
                        <path stroke-linejoin="round" stroke-width="2"
                              d="M213 359v-4c0-1.105.895-2 2-2h2c1.105 0 2 .895 2 2v4h0"/>
                    </g>
                </svg>
            </button>
            <button class="shape-btn hide-btn" data-title-hidden="Tonen" data-title-unhidden="Verbergen"
                    title="Verbergen">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="16">
                    <defs>
                        <filter id="a" width="132.4%" height="113.2%" x="-13.8%" y="-5.5%"
                                filterUnits="objectBoundingBox">
                            <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
                            <feGaussianBlur in="shadowOffsetOuter1" result="shadowBlurOuter1" stdDeviation="9"/>
                            <feColorMatrix in="shadowBlurOuter1" result="shadowMatrixOuter1"
                                           values="0 0 0 0 0.301960784 0 0 0 0 0.341176471 0 0 0 0 0.560784314 0 0 0 0.2 0"/>
                            <feMerge>
                                <feMergeNode in="shadowMatrixOuter1"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    <g filter="url(#a)" transform="translate(-239 -12)" fill="none" fill-rule="evenodd">
                        <g transform="translate(239 12)">
                            <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                                  d="M10 14c3.314 0 6.314-2 9-6-2.686-4-5.686-6-9-6S3.686 4 1 8c2.686 4 5.686 6 9 6z"/>
                            <circle cx="10" cy="8" r="5.5" stroke="currentColor"/>
                            <path fill="currentColor"
                                  d="M10 5c.1 0 .197.005.294.014C9.563 5.114 9 5.742 9 6.5c0 .828.672 1.5 1.5 1.5S12 7.328 12 6.5c0-.417-.17-.795-.445-1.067C12.42 5.96 13 6.912 13 8c0 1.657-1.343 3-3 3S7 9.657 7 8s1.343-3 3-3z"/>
                        </g>
                    </g>
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="16">
                    <defs>
                        <filter id="a" width="132.4%" height="113.2%" x="-13.8%" y="-5.5%"
                                filterUnits="objectBoundingBox">
                            <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
                            <feGaussianBlur in="shadowOffsetOuter1" result="shadowBlurOuter1" stdDeviation="9"/>
                            <feColorMatrix in="shadowBlurOuter1" result="shadowMatrixOuter1"
                                           values="0 0 0 0 0.301960784 0 0 0 0 0.341176471 0 0 0 0 0.560784314 0 0 0 0.2 0"/>
                            <feMerge>
                                <feMergeNode in="shadowMatrixOuter1"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    <g filter="url(#a)" transform="translate(-239 -257)" fill="none" fill-rule="evenodd">
                        <g transform="translate(239 257)">
                            <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                                  d="M10 14c3.314 0 6.314-2 9-6-2.686-4-5.686-6-9-6S3.686 4 1 8c2.686 4 5.686 6 9 6z"/>
                            <circle cx="10" cy="8" r="5.5" stroke="currentColor"/>
                            <path fill="currentColor"
                                  d="M10 5c.1 0 .197.005.294.014C9.563 5.114 9 5.742 9 6.5c0 .828.672 1.5 1.5 1.5S12 7.328 12 6.5c0-.417-.17-.795-.445-1.067C12.42 5.96 13 6.912 13 8c0 1.657-1.343 3-3 3S7 9.657 7 8s1.343-3 3-3z"/>
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M17 15L3 1"/>
                        </g>
                    </g>
                </svg>
            </button>
            <button class="shape-btn drag-btn" title="Verslepen">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16">
                    <defs>
                        <filter id="a" width="132.4%" height="113.2%" x="-13.8%" y="-5.5%"
                                filterUnits="objectBoundingBox">
                            <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
                            <feGaussianBlur in="shadowOffsetOuter1" result="shadowBlurOuter1" stdDeviation="9"/>
                            <feColorMatrix in="shadowBlurOuter1" result="shadowMatrixOuter1"
                                           values="0 0 0 0 0.301960784 0 0 0 0 0.341176471 0 0 0 0 0.560784314 0 0 0 0.2 0"/>
                            <feMerge>
                                <feMergeNode in="shadowMatrixOuter1"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    <g filter="url(#a)" transform="translate(-275 -12)" stroke="currentColor" stroke-width="2"
                       fill="none" fill-rule="evenodd" stroke-linecap="round">
                        <path d="M276 17h14m-14 6h14"/>
                    </g>
                </svg>
            </button>
        </div>
    </div>
</template>
<template id="layer-group-template">
    <div class="layer-group">
        <div class="header">
            <div class="header-container">
                <span class="header-title"></span>
                <div class="btn-group">
                    <button class="layer-btn remove-btn" style="display: none;" title="Verwijderen">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                            <g fill="none" fill-rule="evenodd" stroke-linecap="round">
                                <g stroke="currentColor">
                                    <path stroke-width="2"
                                          d="M13.9 5.09l-.817 9c-.047.516-.479.91-.996.91H3.913c-.517 0-.949-.394-.996-.91L2 4h0"/>
                                    <path d="M9.5 7.5L9.5 11.5M6.5 7.5L6.5 11.5"/>
                                    <path stroke-width="3" d="M1.5 3.5L14.5 3.5"/>
                                    <path d="M5.5 2.5v-1c0-.552.448-1 1-1h3c.552 0 1 .448 1 1v1h0"/>
                                </g>
                            </g>
                        </svg>
                    </button>
                    <button class="layer-btn lock-btn" style="display: none;" data-title-locked="Ontgrendelen"
                            data-title-unlocked="Vergrendelen" title="Vergrendelen">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16">
                            <defs>
                                <filter id="a" width="132.4%" height="113.2%" x="-13.8%" y="-5.5%"
                                        filterUnits="objectBoundingBox">
                                    <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
                                    <feGaussianBlur in="shadowOffsetOuter1" result="shadowBlurOuter1" stdDeviation="9"/>
                                    <feColorMatrix in="shadowBlurOuter1" result="shadowMatrixOuter1"
                                                   values="0 0 0 0 0.301960784 0 0 0 0 0.341176471 0 0 0 0 0.560784314 0 0 0 0.2 0"/>
                                    <feMerge>
                                        <feMergeNode in="shadowMatrixOuter1"/>
                                        <feMergeNode in="SourceGraphic"/>
                                    </feMerge>
                                </filter>
                            </defs>
                            <g filter="url(#a)" transform="translate(-209 -12)" stroke="currentColor" fill="none"
                               fill-rule="evenodd" stroke-linecap="round">
                                <path stroke-width="3"
                                      d="M212 19.5h8c.828 0 1.5.672 1.5 1.5v4c0 .828-.672 1.5-1.5 1.5h-8c-.828 0-1.5-.672-1.5-1.5v-4c0-.828.672-1.5 1.5-1.5z"/>
                                <path stroke-linejoin="round" stroke-width="2"
                                      d="M219 15c0-1.105-.895-2-2-2h-2c-1.105 0-2 .895-2 2v4h0"/>
                            </g>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" style="display: none;">
                            <defs>
                                <filter id="a" width="132.4%" height="113.2%" x="-13.8%" y="-5.5%"
                                        filterUnits="objectBoundingBox">
                                    <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
                                    <feGaussianBlur in="shadowOffsetOuter1" result="shadowBlurOuter1" stdDeviation="9"/>
                                    <feColorMatrix in="shadowBlurOuter1" result="shadowMatrixOuter1"
                                                   values="0 0 0 0 0.301960784 0 0 0 0 0.341176471 0 0 0 0 0.560784314 0 0 0 0.2 0"/>
                                    <feMerge>
                                        <feMergeNode in="shadowMatrixOuter1"/>
                                        <feMergeNode in="SourceGraphic"/>
                                    </feMerge>
                                </filter>
                            </defs>
                            <g filter="url(#a)" transform="translate(-209 -352)" stroke="currentColor" fill="none"
                               fill-rule="evenodd" stroke-linecap="round">
                                <path stroke-width="3"
                                      d="M212 359.5h8c.828 0 1.5.672 1.5 1.5v4c0 .828-.672 1.5-1.5 1.5h-8c-.828 0-1.5-.672-1.5-1.5v-4c0-.828.672-1.5 1.5-1.5z"/>
                                <path stroke-linejoin="round" stroke-width="2"
                                      d="M213 359v-4c0-1.105.895-2 2-2h2c1.105 0 2 .895 2 2v4h0"/>
                            </g>
                        </svg>
                    </button>
                    <button class="layer-btn hide-btn" style="display: none;" data-title-hidden="Tonen"
                            data-title-unhidden="Verbergen" title="Verbergen">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="16">
                            <defs>
                                <filter id="a" width="132.4%" height="113.2%" x="-13.8%" y="-5.5%"
                                        filterUnits="objectBoundingBox">
                                    <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
                                    <feGaussianBlur in="shadowOffsetOuter1" result="shadowBlurOuter1" stdDeviation="9"/>
                                    <feColorMatrix in="shadowBlurOuter1" result="shadowMatrixOuter1"
                                                   values="0 0 0 0 0.301960784 0 0 0 0 0.341176471 0 0 0 0 0.560784314 0 0 0 0.2 0"/>
                                    <feMerge>
                                        <feMergeNode in="shadowMatrixOuter1"/>
                                        <feMergeNode in="SourceGraphic"/>
                                    </feMerge>
                                </filter>
                            </defs>
                            <g filter="url(#a)" transform="translate(-239 -12)" fill="none" fill-rule="evenodd">
                                <g transform="translate(239 12)">
                                    <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                                          d="M10 14c3.314 0 6.314-2 9-6-2.686-4-5.686-6-9-6S3.686 4 1 8c2.686 4 5.686 6 9 6z"/>
                                    <circle cx="10" cy="8" r="5.5" stroke="currentColor"/>
                                    <path fill="currentColor"
                                          d="M10 5c.1 0 .197.005.294.014C9.563 5.114 9 5.742 9 6.5c0 .828.672 1.5 1.5 1.5S12 7.328 12 6.5c0-.417-.17-.795-.445-1.067C12.42 5.96 13 6.912 13 8c0 1.657-1.343 3-3 3S7 9.657 7 8s1.343-3 3-3z"/>
                                </g>
                            </g>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="16" style="display: none;">
                            <defs>
                                <filter id="a" width="132.4%" height="113.2%" x="-13.8%" y="-5.5%"
                                        filterUnits="objectBoundingBox">
                                    <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
                                    <feGaussianBlur in="shadowOffsetOuter1" result="shadowBlurOuter1" stdDeviation="9"/>
                                    <feColorMatrix in="shadowBlurOuter1" result="shadowMatrixOuter1"
                                                   values="0 0 0 0 0.301960784 0 0 0 0 0.341176471 0 0 0 0 0.560784314 0 0 0 0.2 0"/>
                                    <feMerge>
                                        <feMergeNode in="shadowMatrixOuter1"/>
                                        <feMergeNode in="SourceGraphic"/>
                                    </feMerge>
                                </filter>
                            </defs>
                            <g filter="url(#a)" transform="translate(-239 -257)" fill="none" fill-rule="evenodd">
                                <g transform="translate(239 257)">
                                    <path stroke="#929DAF" stroke-linejoin="round" stroke-width="2"
                                          d="M10 14c3.314 0 6.314-2 9-6-2.686-4-5.686-6-9-6S3.686 4 1 8c2.686 4 5.686 6 9 6z"/>
                                    <circle cx="10" cy="8" r="5.5" stroke="#929DAF"/>
                                    <path fill="#929DAF"
                                          d="M10 5c.1 0 .197.005.294.014C9.563 5.114 9 5.742 9 6.5c0 .828.672 1.5 1.5 1.5S12 7.328 12 6.5c0-.417-.17-.795-.445-1.067C12.42 5.96 13 6.912 13 8c0 1.657-1.343 3-3 3S7 9.657 7 8s1.343-3 3-3z"/>
                                    <path stroke="#929DAF" stroke-linecap="round" stroke-width="2" d="M17 15L3 1"/>
                                </g>
                            </g>
                        </svg>
                    </button>
                    <button class="layer-btn add-layer-btn" title="Laag toevoegen">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="16">
                            <g stroke="currentColor" stroke-width="3" fill="none" fill-rule="evenodd"
                               stroke-linecap="round" transform="translate(2.861 .5)">
                                <path d="M-.861 7.5h10M4.139 12.5v-10"/>
                            </g>
                        </svg>
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
            <svg xmlns="http://www.w3.org/2000/svg" width="4" height="14">
                <g fill="currentColor" fill-rule="evenodd">
                    <path d="M1.615 0h.77c.828 0 1.5.672 1.5 1.5 0 .037-.002.074-.004.11l-.45 6.06C3.377 8.42 2.752 9 2 9S.624 8.42.568 7.67L.12 1.61C.06.786.68.066 1.504.005 1.541.001 1.578 0 1.615 0z"/>
                    <circle cx="2" cy="12" r="2"/>
                </g>
            </svg>
        </div>
    </div>
</template>
<template id="svg-layer-to-render"></template>

@push('scripts')
    <script>
        initDrawingQuestion();

        Canvas.data.answer = '{!!  $this->question['answer_svg'] !!}';
        Canvas.data.question = '{!! $this->question['question_svg'] !!}';

        {{--if ({{ $svg_grid ??  "0.00"}} !== "0.00") {--}}
        {{--    let parsedGrid = parseFloat({{ $svg_grid }});--}}
        {{--    if (drawingApp.isTeacher()) {--}}
        {{--        UI.gridSize.value = parsedGrid;--}}
        {{--        UI.gridToggle.checked = true;--}}
        {{--    } else {--}}
        {{--        drawingApp.params.gridSize = parsedGrid;--}}
        {{--        Canvas.layers.grid.params.hidden = false;--}}
        {{--    }--}}
        {{--}--}}

        // drawingApp.init();

        @if(Auth::user()->isA('teacher'))
        Canvas.layers.answer.enable();
        Canvas.setCurrentLayer("answer");
        @endif

        window.drawingSaveUrl = '/questions/save_drawing';
        window.drawingCallback = function () {
{{--            window.parent.Answer.drawingPadClose('{{ $question_id  ?? ''}}');--}}
            window.parent.Answer.drawingPadClose('1');
        };
    </script>
@endpush