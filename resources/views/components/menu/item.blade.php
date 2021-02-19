@props(['label', 'name'])

<div class="menu-item px-2 py-1">
    <button @click="{{ $name }} = !{{ $name }}" @click.away="{{ $name }} = false" class="text-button"
            :class="{ 'active': {{ $name }}}">{{ $label }}
    </button>
</div>