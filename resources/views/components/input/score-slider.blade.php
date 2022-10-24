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
{{--    <div class="flex space-x-1">--}}
{{--        @foreach(range(1,$maxScore) as $value)--}}
{{--            <div class="rounded-10 h-3 w-6 {{ $value <= $score ? 'bg-primary border-primary' : 'bg-offwhite border-secondary' }} border"--}}
{{--                 @click="score = {{$value}}"--}}
{{--            ></div>--}}
{{--        @endforeach--}}
{{--    </div>--}}

    <div class="flex relative w-[250px] h-12">
        <div class="flex w-full h-full justify-between items-center space-x-1 pl-[18px] pr-[12px]">
        @foreach(range(1,$maxScore) as $value)
                <div class="rounded-10 h-3 min-w-6 flex-grow {{ $value <= $score ? 'bg-primary border-primary' : 'bg-offwhite border-secondary' }} border "
                {{--@click="score = {{$value}}" --}}
                ></div>
            @endforeach
        </div>
        <div class="w-full absolute top-0 left-0  flex items-center h-full">
            <input type="range" min="0" max="{{$maxScore}}" class="score-slider-input w-full" value="0">
        </div>
    </div>

    <div
            class="w-16 h-10 border border-blue-grey bg-offwhite flex items-center justify-center rounded-10"
            x-text="score"
    >
        {{$score ?: '-'}}
    </div>
</div>