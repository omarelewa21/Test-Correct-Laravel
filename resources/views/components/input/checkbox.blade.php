@props(['disabled' => false, 'checked' => false, 'containerClasses' => ''])
<label @class(["checkbox-container", "disabled" => $disabled, "checked" => $checked, $containerClasses])>
    <input type="checkbox" name="checkbox" @checked($checked) @disabled($disabled) {{ $attributes->wire('model') }}>
    <span @class(["checkmark", $attributes->get('class')]) {{ $attributes->except(['class', 'wire:model']) }}>
        <x-icon.checkmark-small/>
    </span>
</label>