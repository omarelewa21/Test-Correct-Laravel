<div class="property-group" id="text-style">
    <x-input.color-picker  :name="'text-color'" :id="'text-color'" :title="__('drawing-modal.Tekstkleur')"/>
    <div class="input-with-button-group" style="margin-right: .5rem">
        <button id="decr-text-size" class="Secondary" title="{{ __('drawing-modal.Vergroot tekst') }}">
            <div>
                <x-icon.min/>
            </div>
        </button>
        <input type="number" name="text-size" id="text-size" min="10" max="50" value="15" step="1"
               autocomplete="off" title="{{ __('drawing-modal.Tekstgrootte') }}">
        <button id="incr-text-size" class="Secondary" title="{{ __('drawing-modal.Verklein tekst') }}">
            <div>
                <x-icon.plus/>
            </div>
        </button>
    </div>
    <input type="checkbox" id="bold-toggle" style="display: none;" autocomplete="off">
    <button id="bold-toggle-button" title="{{ __('drawing-modal.Zet dikgedrukt aan/uit') }}">
        <label for="bold-toggle" id="bold-text">
            <x-icon.bold/>
        </label>
    </button>
</div>

<div class="property-group" id="opacity" title="{{ __('drawing-modal.Doorzichtigheid') }}" style="display: none">
    <input type="number" name="opacity" id="elem-opacity-number" min="0" max="100" value="100" step="1"
           autocomplete="off">
    <input class="drawing-toolbar-slider" type="range" name="opacity" id="elem-opacity-range" min="0"
           max="100" value="100" step="1" autocomplete="off" style="cursor: grab">
</div>