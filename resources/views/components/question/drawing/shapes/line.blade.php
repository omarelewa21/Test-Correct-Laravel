<x-question.drawing.properties.pen :shape="$instance->shape"/>

<div class="property-group" id="endmarker-type" title="{{ __('drawing-modal.Type lijneinde') }}">
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