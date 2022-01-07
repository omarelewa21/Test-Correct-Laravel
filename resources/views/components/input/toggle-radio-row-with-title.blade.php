@props([
'valueOff' => 0,
'valueOn' => 1
])
<div class="border-b flex w-full justify-between items-center py-2">
    <div class="flex items-center space-x-2.5">
        {{ $slot }}
    </div>
    <div>
        <label class="switch" x-data="{ value:@entangle($attributes->wire('model')) }" @click="if (value=='{{ $valueOff }}') {value='{{ $valueOn }}'} else {value='{{ $valueOff }}'} ">
            <span class="slider round" :class="{checked: value=='{{ $valueOn }}'}"></span>
        </label>
    </div>
</div>
