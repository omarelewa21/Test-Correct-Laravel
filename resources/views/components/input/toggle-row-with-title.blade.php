@props([
    'toolTip' => '',
    'disabled' => false,
    'small' => false,
    'checked' => false,
])
<div class="border-b flex w-full justify-between items-center pt-2.5 pb-[11px]">
    <div {{ $attributes->merge(['class' => 'flex items-center space-x-2.5 text-base']) }}>
        {{ $slot }}
    </div>
    <div class="flex items-center">
        @if($toolTip)
            <x-tooltip class="mr-2">
                <span class="text-base">{{ $toolTip }}</span>
            </x-tooltip>
        @endif
        <label class="switch @if($small) small @endif">
            <input type="checkbox" {{ $attributes->merge() }} value="1" autocapitalize="none" autocorrect="off"
                   autocomplete="off" spellcheck="false" class="verify-ok" {{ $checked ? 'checked' : ''}}
                   @if($disabled) disabled @endif
            >
            <span class="slider round"></span>
        </label>
    </div>
</div>
