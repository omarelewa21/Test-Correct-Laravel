@props([
'title' => '',
'onlyInteger' => false,
'disabled' => false,
'error' => false
])

<input {{ $attributes->except(['class']) }}
        @class([
         $attributes->get('class'),
         'form-input',
         'border-allred' => $error
        ])
       @if($title != '') title="{{ $title }}" @endif
       @if($onlyInteger) type="number" @keypress="[',','.'].includes($event.key) ? $event.preventDefault() : ''" @endif
       autocomplete="off"
       @disabled($disabled)
/>
