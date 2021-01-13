<div>
    <textarea class="form-input" {{ $attributes }}></textarea>
    @error($name)
    <div class="notification error mt-4">
        <span class="title">{{ $message }}</span>
    </div>
    @enderror
</div>
