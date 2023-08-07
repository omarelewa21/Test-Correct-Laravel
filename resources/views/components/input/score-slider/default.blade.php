<div @class(['bold mb-1'])>
    <span>default {{ $title }}</span>
    @if($tooltip)
        {{ $tooltip }}
    @endif
</div>
<div @class(['flex relative min-w-[calc(10.375rem+12px)] max-w-[calc(16.75rem+30px)] h-12 score-slider-track-container'])>
    @include('components.input.score-slider.partials.continuous-guard')

    @if($continuousScoreSlider)
        @include('components.input.score-slider.partials.continuous-slider')
    @else
        @include('components.input.score-slider.partials.slider-pills')
    @endif
</div>

@include('components.input.score-slider.partials.manual-input', ['classes' => 'w-16 items-center justify-center'])
