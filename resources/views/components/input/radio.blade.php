<label @class(["radio-custom gap-2.5", $labelClasses, "disabled" => $disabled])>
    @if($textLeft)
        <span class="">{{ $textLeft }}</span>
    @endif
    <input type="radio"
           name="{{ $name }}"
           value="{{ $value }}"
            @disabled($disabled)
            @checked($checked)
            {{ $attributes }}
    />
    @if($textRight)
        <span class="">{{ $textRight }}</span>
    @endif
</label>