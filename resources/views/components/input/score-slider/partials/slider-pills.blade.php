<div @class(["flex w-full h-full justify-between items-center score-slider-pill-container", 'gap-0.5' => !$halfPoints])
     wire:ignore>

    @if($halfPoints)
        <template x-for="scoreOption in bars">
            <div class="score-slider-pill | rounded-10 h-3 min-w-[1rem] flex-grow -mt-[1px] border"
                 :class="sliderPillClasses(scoreOption)"
            ></div>
        </template>
    @else
        <template x-for="scoreOption in bars">
            <div class="score-slider-pill | rounded-10 h-3 min-w-[1rem] flex-grow border"
                 :class="scoreOption <= score ? 'bg-primary border-primary' : 'bg-offwhite border-bluegrey'"
            ></div>
        </template>
    @endif
</div>
<div class="w-full absolute top-0 left-0  flex items-center h-full z-[2]">
    <input type="range"
           min="0"
           max="{{$maxScore}}"
           :step="halfPoints ? 0.5 : 1"
           @class(["score-slider-input w-full hide-thumb", "invisible" => $hideThumb])
           x-model="score"
           @if(!$hideThumb)
               :class="{'hide-thumb': score === null}"
           x-on:click="noChangeEventFallback; $nextTick(() => { setThumbOffset(); }) "
           x-on:input="setThumbOffset();"
           x-on:change="syncInput(); "
           x-init="setThumbOffset(); $nextTick(() => { setThumbOffset(); })"
           @endif
           x-on:click="noChangeEventFallback"
           x-on:change="syncInput()"
            @disabled($disabled)
           x-bind:value="score ?? 0"
           x-on:mousedown="if(score === null) {$el.classList.add('moving')}"
    >
</div>