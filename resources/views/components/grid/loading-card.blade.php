<div {{ $attributes->merge(['class' => 'grid-loading-card bg-white p-6 rounded-10 card-shadow']) }}>
    <div class="h-16">
        <x-knightrider/>
    </div>
    <div class="h-8 flex w-full justify-between">
        <div class="flex w-1/2 space-x-2.5">
            <div class="w-1/2">
                <x-knightrider/>
            </div>
            <div class="w-1/4">
                <x-knightrider/>
            </div>
        </div>
        <div class="flex w-1/2">
            <div class="w-full justify-self-end">
                <x-knightrider/>
            </div>
        </div>
    </div>
    <div class="h-8 mb-1">
        <x-knightrider/>
    </div>
</div>