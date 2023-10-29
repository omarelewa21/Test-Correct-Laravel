<div @class(['bold mb-1'])>
    <span>{{ $title }}</span>
    @if($tooltip)
        {{ $tooltip }}
    @endif
</div>
<div @class(['flex relative min-w-[calc(10.375rem+12px)] max-w-[calc(16.75rem+30px)] h-12 score-slider-track-container'])>
    @include($inputTemplate)
</div>

@include('components.input.score-slider.partials.manual-input', ['classes' => 'w-14 items-center justify-center'])
