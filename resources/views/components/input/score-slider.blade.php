<div wire:ignore
     x-data="scoreSlider(
        @js($score),
        @js($modelName),
        @js($maxScore),
        @js($halfPoints),
        @js($disabled),
        @js($coLearning),
        @js($focusInput),
        @js($continuousScoreSlider),
        @js($minScore)
     )"
     x-on:updated-score.window="skipSync = true; score = $event.detail.score; updateContinuousSlider(); setThumbOffset();"
     x-on:new-score="score = $event.detail.score; setThumbOffset();syncInput()"
     x-on:scoring-elements-error.window="markInputElementsWithError()"
        {{ $attributes->except(['wire:model', 'class']) }}
        @class([
            $attributes->get('class'),
            'flex flex-1 score-slider-container w-fit relative',
            'opacity-50' => $disabled,
            'items-center' => in_array($mode, ['default','large']),
            'flex-col gap-0.5' => $mode === 'small',
            'justify-between gap-2' => $mode === 'default',
            'gap-3' => $mode === 'large',
            'untouched' => $useIndicator,
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
                         :$minScore
                         :$inputTemplate
                         :$focusInput
    />

    @if($disabled)
        <div class="absolute w-full h-full z-10 "></div>
    @endif
</div>