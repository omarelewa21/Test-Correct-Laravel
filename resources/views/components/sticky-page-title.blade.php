<div {{ $attributes->merge(['class' =>"flex w-full border-b border-secondary pb-1 sticky bg-lightGrey z-1 sticky-pseudo-bg"]) }}>
    <div class="w-full max-w-screen-2xl mx-auto px-10 z-1">
        <div class="flex w-full justify-between">
            <div class="flex items-center space-x-2.5">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>