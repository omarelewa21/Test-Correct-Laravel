<div>
    <input {{ $attributes }} class="form-input @error($name) border-red @enderror"/>

    @error($name)
    <div class="notification error mt-4">
        <span class="title">{{ $message }}</span>
    </div>
    @enderror
</div>
