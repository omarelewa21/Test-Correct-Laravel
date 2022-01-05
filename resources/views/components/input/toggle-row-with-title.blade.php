@props(['toolTip' => ''])
<div class="border-b flex w-full justify-between items-center pt-2.5 pb-[11px]">
    <div class="flex items-center space-x-2.5 text-base">
        {{ $slot }}
    </div>
    <div class="flex items-center">
        @if($toolTip)
            <x-tooltip class="mr-2">
                <span class="text-base">{{ $toolTip }}</span>
            </x-tooltip>
        @endif
        <label class="switch">
            <input type="checkbox" {{ $attributes->merge() }} value="1" autocapitalize="none" autocorrect="off"
                   autocomplete="off" spellcheck="false" class="verify-ok">
            <span class="slider round"></span>
        </label>
    </div>
</div>
