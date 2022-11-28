@props([
'title' => '',
'onlyInteger' => false,
'disabled' => false
])

<input {{ $attributes->merge(['class' => 'form-input']) }}
       @if($title != '') title="{{ $title }}" @endif
        @if($onlyInteger) type="number" @keypress="[',','.'].includes($event.key) ? $event.preventDefault() : ''" @endif
       autocomplete="off"
       @if($disabled) disabled @endif
/>
