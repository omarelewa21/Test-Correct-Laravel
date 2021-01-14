<x-input.group label="{{ $label }}" for="{{ $name }}" :error="$errors->first('username')">
    <input {{ $attributes->merge(['class' => 'form-input']) }}/>

    @error($name)
    <div class="notification error mt-4">
        <span class="title">{{ $message }}</span>
    </div>
    @enderror
</x-input.group>
