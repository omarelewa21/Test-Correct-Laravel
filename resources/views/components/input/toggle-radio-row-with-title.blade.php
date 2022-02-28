@props([
'valueOff' => 0,
'valueOn' => 1,
'disabled' => false
])
<div class="border-b flex w-full justify-between items-center py-2">
    <div {{ $attributes->merge(['class' => 'flex items-center space-x-2.5 text-base']) }}>
        {{ $slot }}
    </div>
    <div>
        <label class="switch @if($disabled) disabled @endif"
               x-data="{ value: @entangle($attributes->wire('model')) }"
               @if(!$disabled)
               @click="if (value=='{{ $valueOff }}') {
                            value='{{ $valueOn }}'
                        } else {
                            value='{{ $valueOff }}'
                        }"
                @endif
        >
            <span class="slider round" :class="{'checked': value === '{{ $valueOn }}' }"></span>
        </label>
    </div>
</div>
