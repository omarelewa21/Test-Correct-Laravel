<div id="drawing-tool"
     class="rounded-10 @student student @else teacher @endstudent"
     wire:ignore
>
    <div class="section-container">
        <section>
            <div id="tools">
                <div id="elements" class="tools-group">
                    <button id="drag-btn" class="active" title="{{ __('drawing-modal.Verplaatsen') }}" data-button-group="tool">
                        <div>
                            <x-icon.drag/>
                        </div>
                    </button>
                    @foreach ($shapes as $shape)
                        <button id="add-{{$shape->id}}-btn" title={{$shape->title}} data-button-group="tool">
                            <div>
                                <x-dynamic-component :component="'icon.'.$shape->icon" />
                            </div>
                        </button>
                    @endforeach
                    @if(Auth::user()->isA('teacher'))
                        <button id="img-upload-btn" title="{{ __('drawing-modal.Afbeelding') }}">
                            <label for="img-upload" id="img-upload-label">
                                <x-icon.image-upload/>
                            </label>
                        </button>
                        <input type="file" id="img-upload" style="display: none;" accept="image/jpeg, image/png"
                               multiple>
                    @endif
                </div>
                <div id="grid-background" class="tools-group">
                    <input type="checkbox" id="grid-toggle" style="display: none;" autocomplete="off" x-ref="gridinput">
                    <button title="{{ __('drawing-modal.Zet grid aan/uit') }}"
                            @click="$refs.gridinput.checked = !$refs.gridinput.checked; $refs.gridinput.dispatchEvent(new Event('change'))">
                        <label id="grid-toggle-btn">
                            <x-icon.grid/>
                        </label>
                    </button>
                    <div class="input-with-button-group"  x-ref="gridsizegroup">
                        <button x-ref="groupbtn" id="decr-grid-size" class="Secondary decrement"
                                title="{{ __('drawing-modal.Verklein grid') }}" disabled>
                            <div>
                                <x-icon.min/>
                            </div>
                        </button>
                        <input type="number" id="grid-size" class="group-value" min="0.5" max="5" value="1" step="0.5"
                               title="{{ __('drawing-modal.Afmeting grid') }}"
                               disabled>
                        <button id="incr-grid-size" class="Secondary increment"
                                title="{{ __('drawing-modal.Vergroot grid') }}" disabled>
                            <div>
                                <x-icon.plus/>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
            <div id="properties">
                @foreach ($shapes as $shape)
                    {{$shape->render()}}
                @endforeach
            </div>
        </section>
        <button id="exit-btn" title="{{ __('drawing-modal.Sluiten') }}">
            <x-icon.close/>
        </button>
    </div>
    <div id="canvas-sidebar-container" class="overflow-hidden">
        <article id="canvas" class="overflow-hidden">
            <svg id="svg-canvas" x-ref="canvas" xmlns="http://www.w3.org/2000/svg" class="overflow-hidden">
                <defs>
                    <marker id="svg-filled-arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6"
                            orient="auto-start-reverse">
                        <polyline points="0,0 10,5 0,10" stroke="none"/>
                    </marker>
                    <marker id="svg-two-lines-arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6"
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
                <button id="decr-zoom" class="Secondary min-btn" title="{{ __('drawing-modal.Zoom uit') }}">
                    <x-icon.min/>
                </button>
                <input type="text" id="zoom-level" class="percentfield" value="100%" disabled>
                <button id="incr-zoom" class="Secondary plus-btn" title="{{ __('drawing-modal.Zoom in') }}">
                    <x-icon.plus/>
                </button>
            </div>
            <div id="cursor-pos-container" class="coord-box flex items-end">
                <button id="center-btn" class="w-10 h-10 Secondary" Title="{{ __('drawing-modal.Centreren') }}" style="margin-right: .5rem">
                    <label for="center-btn" id="center-btn-label" class="base">
                        <x-icon.center-screen/>
                    </label>
                </button>
                <div>
                    <div id="cursor-pos-title">
                        {{ __('cms.coordinaten')}}
                    </div>
                    <div id="cursor-pos">
                        X 0, Y 0
                    </div>
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
                    <button class="CTA" id="submit-btn" selid="save-drawing-btn">
                        {{ __('drawing-modal.Opslaan') }}
                    </button>
                </div>
            </div>
        </aside>
    </div>
    <div id="delete-confirm" class="confirm-modal absolute inset-0 flex flex-1 items-center justify-center">
        <div class="modal-body">
            <h4 class="title">{{ __('cms.delete') }}</h4>
            <div class="divider"></div>
            <span class="body">{{ __('cms.drawing-question-delete-entry') }}</span>
            <div class="modal-buttons flex items-center justify-end">
                <button id="delete-cancel-btn" class="cancel flex bold hover:text-primary cursor-pointer transition">{{ __('auth.cancel') }}</button>
                <button id="delete-confirm-btn" class="confirm bold">{{ __('cms.delete') }}</button>
            </div>
        </div>
    </div>

    <div id="close-confirm" class="confirm-modal absolute inset-0 flex flex-1 items-center justify-center">
        <div class="modal-body">
            <h4 class="title">{{ __('general.close') }}</h4>
            <div class="divider"></div>
            <span class="body">{{ __('cms.drawing-question-close-without-save') }}</span>
            <div class="modal-buttons flex items-center justify-end">
                <button id="close-cancel-btn" class="cancel flex bold hover:text-primary cursor-pointer transition">{{ __('auth.cancel') }}</button>
                <button id="close-confirm-btn" @click="show = false" class="confirm bold">{{ __('general.close') }}</button>
            </div>
        </div>
    </div>
    <div id="save-confirm" class="confirm-modal absolute inset-0 flex flex-1 items-center justify-center">
        <div class="modal-body">
            <h4 class="title">{{ __('general.save') }}</h4>
            <div class="divider"></div>
            @if(Auth::user()->isA('teacher'))
            <span class="body">{{ __('drawing-modal.Hidden Layers confirmation teacher') }}</span>
            @else
            <span class="body">{{ __('drawing-modal.Hidden Layers confirmation student') }}</span>
            @endif
            <div class="modal-buttons flex items-center justify-end">
                <button id="save-cancel-btn" class="cancel flex bold hover:text-primary cursor-pointer transition">{{ __('auth.cancel') }}</button>
                <button id="save-confirm-btn" class="confirm bold">{{ __('general.save') }}</button>
            </div>
        </div>
    </div>
    <div id="save-no-answers-confirm" class="confirm-modal absolute inset-0 flex flex-1 items-center justify-center">
        <div class="modal-body">
            <h4 class="title">{{ __('general.save') }}</h4>
            <div class="divider"></div>
             <span class="body">{{ __('drawing-modal.Save no answers yet confirm text') }}</span>
            <div class="modal-buttons flex items-center justify-end">
                <button id="save-no-answers-cancel-btn" class="cancel flex bold hover:text-primary cursor-pointer transition">{{ __('auth.cancel') }}</button>
                <button id="save-no-answers-confirm-btn" class="confirm bold">{{ __('general.save') }}</button>
            </div>
        </div>
    </div>
</div>
<template id="shape-group-template">
    <div class="shape-container" id="shape-n" draggable="false" selid="shape">
        <div class="flex items-center w-full justify-between">
            <span class="shape-title">If you read this, report a bug</span>
            <div class="btn-group">
                <button class="shape-btn remove-btn" selid="shape-remove-btn" title="{{ __('drawing-modal.Verwijderen') }}">
                    <x-icon.trash/>
                </button>
                <button class="shape-btn edit-btn" selid="shape-edit-btn" title="{{ __('drawing-modal.edit') }}">
                    <x-icon.edit/>
                </button>
                <button class="shape-btn lock-btn" selid="shape-lock-btn" data-title-locked="{{ __('drawing-modal.Ontgrendelen') }}" data-title-unlocked="{{ __('drawing-modal.Vergrendelen') }}"
                        title="{{ __('drawing-modal.Vergrendelen') }}">
                    <x-icon.unlocked/>
                    <x-icon.locked/>
                </button>
                <button class="shape-btn hide-btn" selid="shape-hide-btn" data-title-hidden="{{ __('drawing-modal.Tonen') }}" data-title-unhidden="{{ __('drawing-modal.Verbergen') }}"
                        title="{{ __('drawing-modal.Verbergen') }}">
                    <x-icon.preview/>
                    <x-icon.preview-off/>
                </button>
                <button class="shape-btn drag-btn" selid="shape-drag-btn" title="{{ __('drawing-modal.Versleep volgorde') }}">
                    <x-icon.reorder/>
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
                    <button class="layer-btn remove-btn hidden" style="display: none;" title="{{ __('drawing-modal.Verwijderen') }}">
                        <x-icon.trash/>
                    </button>
                    <button class="layer-btn lock-btn hidden" style="display: none;" data-title-locked="{{ __('drawing-modal.Ontgrendelen') }}"
                            data-title-unlocked="{{ __('drawing-modal.Vergrendelen') }}" title="{{ __('drawing-modal.Vergrendelen') }}">
                        <x-icon.unlocked/>
                        <x-icon.locked style="display: none"/>
                    </button>
                    <button class="layer-btn hide-btn" style="display: none;" data-title-hidden="{{ __('drawing-modal.Tonen') }}"
                            data-title-unhidden="{{ __('drawing-modal.Verbergen') }}" title="{{ __('drawing-modal.Verbergen') }}">
                        <x-icon.preview/>
                        <x-icon.preview-off class="text-midgrey" style="display: none"/>
                    </button>
                    <button class="layer-btn add-layer-btn hidden" title="{{ __('drawing-modal.Laag toevoegen') }}">
                        <x-icon.plus/>
                    </button>
                </div>
            </div>
        </div>
        <div class="shapes-group" selid="shapes-group">
            <span id="explainer" class="explainer note text-sm text-center inline-block"
                  style="padding: 1.5rem"
                  data-text-closeConfirmation="{{ __('drawing-modal.Close confirmation') }}"
                  @if(Auth::user()->isA('teacher'))
                  data-text-answer="{{ __('drawing-modal.Explainer answer teacher') }}"
                  data-text-question="{{ __('drawing-modal.Explainer question teacher') }}"
                  data-text-hiddenLayersConfirmation="{{ __('drawing-modal.Hidden Layers confirmation teacher') }}"

                  @else
                  data-text-answer="{{ __('drawing-modal.Explainer answer student') }}"
                  data-text-question=""
                  data-text-hiddenLayersConfirmation="{{ __('drawing-modal.Hidden Layers confirmation student') }}"
                  @endif
            >
            </span>
        </div>
    </div>
</template>
<template id="warningbox-template" data-text="{{ __('drawing-question.Stel de opmaak in voordat je het object tekent') }}">
    <div class="warning">
        <div class="warning-text">
            <x-icon.exclamation/>
        </div>
    </div>
</template>
<template id="svg-layer-to-render"></template>
<template id="translation-template"
          data-answer="{{ __('cms.Antwoord') }}"
          data-question="{{ __('drawing-modal.Vraag') }}"
          data-image="{{ __("drawing-modal.Afbeelding") }}"
          data-path="{{ __("drawing-modal.Penlijn") }}"
          data-ellipse="{{ __("drawing-modal.Cirkel") }}"
          @foreach($shapes as $shape)
            data-{{$shape->id}}="{{$shape->title}}"
          @endforeach
></template>

