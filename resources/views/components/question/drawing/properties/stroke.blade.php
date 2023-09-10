@props(['shape'])

<div class="property-group" id="stroke-{{$shape}}">
    <x-input.color-picker  :name="'stroke-color-'.$shape" title="{{ __('drawing-modal.Randkleur') }}"/>
    <div class="input-with-button-group">
        <button id="decr-stroke-width-{{$shape}}" class="Secondary" title="{{ __('drawing-modal.Vergroot randdikte') }}">
            <div>
                <x-icon.min/>
            </div>
        </button>
        <input type="number" name="stroke-width-{{$shape}}" id="stroke-width-{{$shape}}" min="0" max="100" value="1"
               autocomplete="off" title="{{ __('drawing-modal.Randdikte') }}">
        <button id="incr-stroke-width-{{$shape}}" class="Secondary" title="{{ __('drawing-modal.Verklein randdikte') }}">
            <div>
                <x-icon.plus/>
            </div>
        </button>
    </div>
</div>