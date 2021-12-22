<div x-data="{tooltip: false}"
     @mouseover="tooltip = true"
     @mouseleave="tooltip = false"
     {{ $attributes->merge(['class' => 'relative bg-system-secondary rounded-full flex py-1.5 px-2']) }}
>
    <x-icon.questionmark-small/>
    <div x-show="tooltip"
         class="absolute max-w-sm w-max bg-off-white rounded-10 p-6 main-shadow z-50 flex top-8 left-1/2 -translate-x-1/2"
    >
        {{ $slot }}
    </div>
</div>