@props([
    'small' => false,
    'selid' => null,
    'disabled' => false,
    'checked' => $checked,
 ])
<label @class(['switch min-w-[var(--switch-width)]', 'small' => $small, $attributes->get('class') ]) @notempty($selid) selid="{{ $selid }}" @endif>
    <input {{ $attributes->except('class') }}
           type="checkbox"
           value="1"
            @disabled($disabled)
            @checked($checked)
    >
    <span class="slider round"></span>
</label>
