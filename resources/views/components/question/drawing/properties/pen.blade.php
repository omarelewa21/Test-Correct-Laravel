@props(['shape'])

<div class="property-group" id="pen-{{$shape}}">
    <x-input.color-picker  :name="'pen-color-'.$shape" title="{{ __('drawing-modal.lineColor') }}"/>
    <div class="input-with-button-group">
        <button id="decr-pen-width-{{$shape}}" class="Secondary" title="{{ __('drawing-modal.reduce-line-width') }}" disabled>
            <div>
                <x-icon.min/>
            </div>
        </button>
        <input type="number" name="pen-width-{{$shape}}" id="pen-width-{{$shape}}" min="1" max="100" value="1"
               autocomplete="off" title="{{ __('drawing-modal.lijndikte') }}">
        <button id="incr-pen-width-{{$shape}}" class="Secondary" title="{{ __('drawing-modal.enlarge-line-width') }}">
            <div>
                <x-icon.plus/>
            </div>
        </button>
    </div>
</div>