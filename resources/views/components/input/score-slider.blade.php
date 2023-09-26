<div wire:ignore
     x-data="scoreSlider(
        @js($score),
        @js($modelName),
        @js($maxScore),
        @js($halfPoints),
        @js($disabled),
        @js($coLearning),
        @js($focusInput),
        @js($continuousScoreSlider)
     )"
     x-on:updated-score.window="skipSync = true; score = $event.detail.score; updateContinuousSlider(); setThumbOffset();"
     x-on:new-score="score = $event.detail.score; setThumbOffset();syncInput()"
     x-on:scoring-elements-error.window="markInputElementsWithError()"
        {{ $attributes->except(['wire:model', 'class']) }}
        @class([
            $attributes->get('class'),
            'flex flex-1 score-slider-container w-fit relative',
            'opacity-50' => $disabled,
            'justify-between items-center space-x-2' => $mode === 'default',
            'flex-col gap-0.5' => $mode === 'small',
        ])
>

    <div @class(['bold mb-1' => $mode === 'default', 'flex' => $mode === 'small'])>
        <span>{{ $title }}</span>
        @if($tooltip)
            {{ $tooltip }}
        @endif
    </div>
    @if($mode === 'small')
        <div class="flex gap-2 items-center w-full slider-input-wrapper">
            @endif
            <div @class(['flex relative','min-w-[calc(10.375rem+12px)] max-w-[calc(16.75rem+30px)] h-12 score-slider-track-container' => $mode === 'default', 'w-full' => $mode === 'small'])>
                <div x-show="score === null"
                     @class([
                        "score-slider-initial-handle",
                        "score-slider-initial-handle-offset" => !$continuousScoreSlider,
                     ])
                     @click="score = 0; syncInput()"
                     @mousedown="score = 0; syncInput(); document.querySelector('#slide-container input[type=range]').focus()"
                ></div>
                @if($continuousScoreSlider)
                    <div class="flex w-full h-full justify-between items-center pl-[12px] pr-[15px]"
                    >
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
                        >
                    </div>
                @else
                    <div @class(["flex w-full h-full justify-between items-center score-slider-pill-container", 'gap-0.5' => !$halfPoints])
                         wire:ignore>

                        @if($halfPoints)
                            <template x-for="scoreOption in bars">
                                <div class="score-slider-pill | rounded-10 h-3 min-w-[1rem] flex-grow -mt-[1px] -ml-[1px] border"
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
                    <div class="w-full absolute top-0 left-0  flex items-center h-full">
                        <input type="range"
                               min="0"
                               max="{{$maxScore}}"
                               :step="halfPoints ? 0.5 : 1"
                               class="score-slider-input w-full hide-thumb"
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
                        >
                    </div>
                @endif
            </div>

            <input @class([
                      'h-10 score-slider-number-input',
                      'w-16 items-center justify-center' => $mode === 'default',
                      'min-w-[3.375rem] w-[3.375rem]' => $mode === 'small',
                      ])
                   x-model.number="score"
                   type="number"
                   max="{{$maxScore}}"
                   min="0"
                   onclick="this.select()"
                   :step="halfPoints ? 0.5 : 1"
                   x-ref="scoreInput"
                   x-on:focusout="syncInput($el.value)"
                   x-on:input="setThumbOffset(document.querySelector('.score-slider-input'), score, maxScore)"
                   autofocus
                    @disabled($disabled)
            >
            @if($mode === 'small')
        </div>
    @endif
    @if($disabled)
        <div class="absolute w-full h-full z-10 "></div>
    @endif
</div>