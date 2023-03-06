<div {{ $attributes->merge(['class' => 'co-learning-panel transition-colors']) }}>
    <div>
        {{ $sticker }}
    </div>
    <div class="flex justify-center items-center mt-2">
        <h5 class="text-white">{{ $title }}</h5>
    </div>
    <div class="space-y-6 panel-body">
        {{ $subtitle }}
    </div>
    <div>
        {{ $button }}
    </div>

</div>