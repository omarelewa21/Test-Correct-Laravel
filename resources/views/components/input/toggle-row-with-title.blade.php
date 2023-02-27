@props([
    'toolTip' => '',
    'disabled' => false,
    'small' => false,
    'tooltipAlwaysLeft' => false,
    'checked' => false,
    'containerClass' => '',
    'error' => false,
    'title' => '',
    'selid' => '',
])
@php
    $borderColor = $error ? 'border-red-500' : 'border-inherit'
@endphp
<div class="border-b flex w-full justify-between items-center pt-2.5 pb-[11px] {{ $borderColor }} {{ $containerClass }}" title="{{ $title }}">
    <div {{ $attributes->merge(['class' => 'flex items-center space-x-2.5 text-base']) }}>
        {{ $slot }}
    </div>
    <div class="flex items-center">
        @if($toolTip)
            <x-tooltip class="mr-2" :always-left="$tooltipAlwaysLeft">
                <span class="text-base text-left">{{ $toolTip }}</span>
            </x-tooltip>
        @endif
        <label class="switch @if($small) small @endif" @notempty($selid) selid="{{ $selid }}" @endif>
            <input type="checkbox" {{ $attributes->merge() }} value="1" autocapitalize="none" autocorrect="off"
                   autocomplete="off" spellcheck="false" class="verify-ok" {{ $checked ? 'checked' : ''}}
                   @if($disabled) disabled @endif
            >
            <span class="slider round"></span>
        </label>
    </div>
</div>
