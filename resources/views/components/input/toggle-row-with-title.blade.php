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
    'indented' => false,
])

<div @class([
        'border-b flex w-full items-center h-[50px] gap-2.5 text-base bold',
        $containerClass,
        'border-red-500' => $error,
        'border-bluegrey' => !$error,
        'indented-toggle-row' => $indented,
     ])
     title="{{ $title }}"
>
    <x-input.toggle @class(['mr-2',  $attributes->get('class') ])
                    {{ $attributes->except('class') }}
                    :small="$small"
                    :selid="$selid"
                    :disabled="$disabled"
                    :checked="$checked"
    />
    {{ $slot }}
    @if($toolTip)
        <div class="min-w-fit ml-auto">
            <x-tooltip :always-left="$tooltipAlwaysLeft">
                <span class="text-base text-left">{{ $toolTip }}</span>
            </x-tooltip>
        </div>
    @endif
</div>