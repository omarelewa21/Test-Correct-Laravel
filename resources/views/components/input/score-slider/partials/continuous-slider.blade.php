<div class="flex w-full h-full justify-between items-center pl-[12px] pr-[15px]">
    <input type="range" min="0" max="{{$maxScore}}"
           :step="halfPoints ? 0.5 : 1"
           x-model="score"
           class="score-slider-continuous-input hide-thumb"
           x-ref="score_slider_continuous_input"
           x-init="setSliderBackgroundSize($el); $nextTick(() => { setSliderBackgroundSize($el); })"
           x-on:input="setSliderBackgroundSize($el)"
           x-on:change="syncInput()"
           @if(!$hideThumb)
               :class="{'hide-thumb': score === null}"
            @endif
            @disabled($disabled)
           x-on:mousedown="if(score === null) {$el.classList.add('moving')}"
    >
</div>