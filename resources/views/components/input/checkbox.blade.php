@props(['disabled' => false, 'checked' => false])
<label @class(["checkbox-container", "disabled" => $disabled, "checked" => $checked]) >
    <input type="checkbox" name="checkbox" @checked($checked) @disabled($disabled)>
    <span class="checkmark" {{ $attributes }}>
        <x-icon.checkmark-small/>
    </span>
</label>