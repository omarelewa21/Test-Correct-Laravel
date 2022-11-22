@props([
    'maxScore',
    'score',
    'modelName',
    'allowHalfPoints' => true,
    'continuousScoreSlider' => false,
    'disabled' => false
])
@php
    if ($allowHalfPoints && $maxScore > 7) {
        $continuousScoreSlider = true;
    }
    if (!$allowHalfPoints && $maxScore > 15) {
        $continuousScoreSlider = true;
    }
@endphp

<div wire:ignore
     x-data="{
        score: @js($score ?? null),
        modelName: '{{$modelName}}',
        maxScore: {{ $maxScore }},
        timeOut: null,
        allowHalfPoints: @js($allowHalfPoints),
        disabled: @js($disabled),
        skipSync: false
        }"
     x-init="$watch('score', (value, oldValue) => {
           if(disabled || value === oldValue || skipSync){
                skipSync = false;
               return;
           }

           if(value >= maxScore){
            score = value = maxScore;
           }
           if(value <= 0) {
            score = value = 0;
           }

           score = value = allowHalfPoints ? Math.round(value*2)/2 : Math.round(value)

           if(timeOut){
                clearTimeout(timeOut);
           }
           timeOut = setTimeout(() => {
                $wire.sync(modelName, value);
           }, 300)
        });

        "
     x-on:updated-score.window="skipSync = true; score = $event.detail.score"
        {{ $attributes->except('wire:model')->merge(['class'=>'flex score-slider-container w-fit justify-between items-center space-x-4 relative '.($disabled ? 'opacity-50': '')]) }}
>

    <span class="bold mb-1">{{ __('Score') }}</span>

    <div class="flex relative min-w-[calc(10.375rem+12px)] max-w-[calc(16.75rem+30px)] h-12">
        @if($continuousScoreSlider)
            <div class="flex w-full h-full justify-between items-center pl-[12px] pr-[15px]">
                <input type="range" min="0" max="{{$maxScore}}" :step="allowHalfPoints ? 0.5 : 1" value="0"
                       x-model="score">
            </div>
        @else
            <div class="flex w-full h-full justify-between items-center pl-[12px] pr-[15px] space-x-[0.125rem]"
                 wire:ignore>

                @if($allowHalfPoints)
                    <template x-for="scoreOption in maxScore">
                        <div class="flex relative rounded-10 h-3 min-w-6 flex-grow border"
                             :class="scoreOption <= score ? 'bg-primary border-primary' : 'border-bluegrey bg-offwhite'">
                            <div class="rounded-10 h-3 min-w-[1rem] flex-grow -mt-[1px] -ml-[1px]"
                                 :class="scoreOption-0.5 <= score ? 'border bg-primary border-primary' : 'opacity-100'"
                            ></div>
                            <div class="h-[0.375rem] w-[0.375rem] rounded-full absolute bottom-1/2 translate-y-1/2 right-1/2 translate-x-1/2 "
                                 :class="scoreOption <= score ? 'bg-teacherPrimaryLight' : 'bg-system-secondary'"
                            ></div>
                            <div class="rounded-10 h-3 min-w-[1rem] flex-grow -mt-[1px]"
                                 :class="scoreOption <= score ? 'border bg-primary border-primary' : 'opacity-100'"
                            ></div>
                        </div>

                    </template>
                @else
                    <template x-for="scoreOption in maxScore">
                        <div class="rounded-10 h-3 min-w-[1rem] flex-grow border "
                             :class="scoreOption <= score ? 'bg-primary border-primary' : 'bg-offwhite border-bluegrey'"
                        ></div>
                    </template>
                @endif
            </div>
            <div class="w-full absolute top-0 left-0  flex items-center h-full">
                <input type="range" min="0" max="{{$maxScore}}" :step="allowHalfPoints ? 0.5 : 1"
                       class="score-slider-input w-full" x-model="score"
                       :class="{'hide-thumb': score === null}"
                       x-on:click="score === null ? score = maxScore/2 : ''"
                >
            </div>
        @endif
    </div>

    <input class="w-16 h-10 border border-blue-grey bg-offwhite flex items-center justify-center rounded-10 text-center"
           x-model="score" type="number" max="{{$maxScore}}" min="0" onclick="this.select()"
    >

    @if($disabled)
        <div class="absolute w-full h-full z-10 "></div>
    @endif
</div>