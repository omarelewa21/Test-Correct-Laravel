<div wire:ignore
     x-data="scoreSlider(
        @js($score),
        @js($modelName),
        @js($maxScore),
        @js($halfPoints),
        @js($disabled),
        @js($coLearning)
     )"
     x-on:updated-score.window="skipSync = true; score = $event.detail.score"
     x-on:new-score="score = $event.detail.score"
     x-on:scoring-elements-error.window="markInputElementsWithError()"
        {{ $attributes->except(['wire:model', 'class']) }}
        @class([
            $attributes->get('class'),
            'flex flex-1 score-slider-container w-fit relative',
            'opacity-50' => $disabled,
            'justify-between items-center space-x-4' => $mode === 'default',
            'flex-col gap-0.5' => $mode === 'small',
        ])
>

    <span @class(['bold mb-1' => $mode === 'default', 'flex' => $mode === 'small'])>{{ __('Score') }}</span>

    @if($mode === 'small')
        <div class="flex gap-2 items-center w-full">
            @endif
            <div @class(['flex relative','min-w-[calc(10.375rem+12px)] max-w-[calc(16.75rem+30px)] h-12' => $mode === 'default', 'w-full' => $mode === 'small'])>
                @if($continuousScoreSlider)
                    <div class="flex w-full h-full justify-between items-center pl-[12px] pr-[15px]"
                    >
                        <input type="range" min="0" max="{{$maxScore}}"
                               :step="halfPoints ? 0.5 : 1"
                               x-model="score"
                               class="score-slider-continuous-input"
                               x-ref="score_slider_continuous_input"
                               x-init="setSliderBackgroundSize($el); $nextTick(() => { setSliderBackgroundSize($el); })"
                               x-on:input="setSliderBackgroundSize($el)"
                               x-on:change="syncInput()"
                               :class="{'hide-thumb': score === null}"
                        >
                    </div>
                @else
                    <div class="flex w-full h-full justify-between items-center  space-x-[0.125rem]"
                         wire:ignore>

                        @if($halfPoints)
                            <template x-for="scoreOption in maxScore">
                                <div class="flex relative rounded-10 h-3 min-w-6 flex-grow border"
                                     :class="scoreOption <= score ? 'bg-primary border-primary' : 'border-bluegrey bg-offwhite'">
                                    <div class="rounded-10 h-3 min-w-[1rem] flex-grow -mt-[1px] -ml-[1px]"
                                         :class="scoreOption-0.75 <= score ? 'border bg-primary border-primary' : 'opacity-100'"
                                    ></div>
                                    <div class="h-[0.375rem] w-[0.375rem] rounded-full absolute bottom-1/2 translate-y-1/2 right-1/2 translate-x-1/2 "
                                         :class="scoreOption <= score ? 'bg-teacherPrimaryLight' : 'bg-system-secondary'"
                                    ></div>
                                    <div class="rounded-10 h-3 min-w-[1rem] flex-grow -mt-[1px]"
                                         :class="scoreOption <= score ? 'border bg-primary border-primary' : 'opacity-100'"
                                    ></div>
                                </div>

                            </template>
                        @else
                            <template x-for="scoreOption in maxScore">
                                <div class="rounded-10 h-3 min-w-[1rem] flex-grow border "
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
                               class="score-slider-input w-full"
                               x-model="score"
                               :class="{'hide-thumb': score === null}"
                               x-on:click="noChangeEventFallback"
                               x-on:change="syncInput()"
                        >
                    </div>
                @endif
            </div>

            <input @class([
                      'h-10 border border-blue-grey bg-offwhite flex rounded-10 text-center',
                      'w-16 items-center justify-center' => $mode === 'default',
                      'min-w-[3.375rem]' => $mode === 'small',
                      ])
                   x-model.number="score"
                   type="number"
                   max="{{$maxScore}}"
                   min="0"
                   onclick="this.select()"
                   :step="halfPoints ? 0.5 : 1"
                   x-ref="scoreInput"
                   x-on:focusout="syncInput($el.value)"
            >
            @if($mode === 'small')
        </div>
    @endif
    @if($disabled)
        <div class="absolute w-full h-full z-10 "></div>
    @endif
</div>