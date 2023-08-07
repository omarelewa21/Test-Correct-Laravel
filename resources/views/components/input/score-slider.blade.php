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
     x-on:new-score="score = $event.detail.score; setThumbOffset();"
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
    <x-dynamic-component :component="'input.score-slider.'.$mode"
                         :$mode
                         :$title
                         :$tooltip
                         :$maxScore
                         :$halfPoints
                         :$disabled
                         :$continuousScoreSlider
                         :$hideThumb
    />

    @if($disabled)
        <div class="absolute w-full h-full z-10 "></div>
    @endif
</div>