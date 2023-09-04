@props(['shape'])

<div class="property-group" id="fill-{{$shape}}">
    <x-input.color-picker  :name="'fill-color-'.$shape" title="{{ __('drawing-modal.Opvulkleur') }}"/>
    <input type="number" name="fill-opacity" id="fill-opacity-number" min="0" max="100" value="25"
           step="1" autocomplete="off" title="{{ __('drawing-modal.Doorzichtigheid opvulkleur') }}">
    <input class="drawing-toolbar-slider" x-ref="slider" type="range" name="fill-opacity" id="fill-opacity-range" style="cursor: grab"
            min="0" max="100" value="25" step="1" autocomplete="off" title="{{ __('drawing-modal.Doorzichtigheid opvulkleur') }}">
</div>