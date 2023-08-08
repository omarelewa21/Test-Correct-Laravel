@if($title)
    <div @class(['bold mb-1'])>
        <span>large {{ $title }}</span>
        @if($tooltip)
            {{ $tooltip }}
        @endif
    </div>
@endif
<div @class(['flex relative h-12 score-slider-track-container'])>
    @include('components.input.score-slider.partials.continuous-guard')

    @include('components.input.score-slider.partials.slider-pills')
</div>

@include('components.input.score-slider.partials.manual-input', ['classes' => 'w-14 items-center justify-center'])

