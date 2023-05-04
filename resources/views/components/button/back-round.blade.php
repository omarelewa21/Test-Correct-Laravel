@props(['backgroundClass' => 'bg-white/75'])
<button {{ $attributes->merge(['class' => 'back-round-button flex items-center justify-center rounded-full min-w-[40px] w-10 h-10 '. $backgroundClass]) }}>
    <x-icon.arrow-left/>
</button>