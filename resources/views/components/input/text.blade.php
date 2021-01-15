<div>
    <input {{ $attributes->merge(['class' => 'form-input']) }}/>

    @error($name)
    <div class="notification error mt-4">
        <span class="title">{{ $message }}</span>
    </div>
    @enderror
</div>
