<div @class(['flex'])>
    <span>{{ $title }}</span>
    @if($tooltip)
        {{ $tooltip }}
    @endif
</div>
<div class="flex gap-2 items-center w-full slider-input-wrapper">
    <div @class(['flex relative w-full'])>
        @include($inputTemplate)
    </div>

    @include('components.input.score-slider.partials.manual-input', ['classes' => 'min-w-[3.375rem] w-[3.375rem]'])
</div>
