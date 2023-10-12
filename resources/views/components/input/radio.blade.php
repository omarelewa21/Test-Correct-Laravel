<label @class(["radio-custom", $labelClasses])>
    @if($textLeft)
        <span class="ml-2.5">{{ $textLeft }}</span>
    @endif
    <input type="radio"
           name="{{ $name }}"
           value="{{ $value }}"
           @disabled($disabled)
            @checked($checked)
            {{ $attributes }}
    />
    @if($textRight)
        <span class="ml-2.5">{{ $textRight }}</span>
    @endif
</label>