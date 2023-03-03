<div {{ $attributes->merge(['class' => 'co-learning-panel']) }}>
    <div>
        {{ $sticker }}
    </div>
    <div class="flex justify-center items-center mt-2">
        <h5 class="text-white">{{ $title }}</h5>
    </div>
    <div id="text-body" class="space-y-6">
        {{ $subtitle }}
    </div>
    <div>
        {{ $button }}
    </div>

</div>