@props([
    'maxScore' => 4,
    'score'
])
<div
        x-data="{
        score: @entangle($attributes->wire('model')->value)
        }"
        {{ $attributes->merge(['class'=>'flex w-fit justify-between items-center space-x-4']) }}
        {{ $attributes->wire('key') ? 'wire:key="'. $attributes->wire('key')->value. '"' : '' }}
>

    <span class="bold mb-1">{{ __('Score') }}</span>
    <div class="flex space-x-1">
        @foreach(range(1,$maxScore) as $value)
            <div class="rounded-10 h-3 w-6 {{ $value <= $score ? 'bg-primary border-primary' : 'bg-offwhite border-secondary' }} border"
                 @click="score = {{$value}}"
            ></div>
        @endforeach
    </div>
    <div
            class="w-16 h-10 border border-blue-grey bg-offwhite flex items-center justify-center rounded-10"
    >
        {{$score ?: 0}}
    </div>
</div>