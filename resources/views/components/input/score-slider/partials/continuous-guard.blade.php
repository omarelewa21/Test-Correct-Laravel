<div x-show="score === null"
     @class([
        "score-slider-initial-handle",
        "score-slider-initial-handle-offset" => !$continuousScoreSlider,
     ])
     @click="score = 0; syncInput()"
     @mousedown="score = 0; syncInput(); document.querySelector('#slide-container input[type=range]').focus()"
></div>