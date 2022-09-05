<div {{ $attributes->merge(['class' => 'border-b border-secondary sticky sticky-pseudo-bg bg-lightGrey z-1']) }}>
    <div class="w-full max-w-screen-2xl mx-auto px-10">
        <div class="flex w-full h-12.5">
            {{ $slot }}
        </div>
    </div>
</div>