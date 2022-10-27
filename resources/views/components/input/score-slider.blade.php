@props([
    'maxScore',
    'score',
    'modelName'
])
<div
        x-data="{
        score: @js($score ?? null),
        modelName: '{{$modelName}}',
        maxScore: {{ $maxScore }},
        timeOut: null,
        }"
        x-init="$watch('score', (value) => {
           if(timeOut){
                clearTimeout(timeOut);
           }
           timeOut = setTimeout(() => {
                $wire.sync(modelName, value);
           }, 1000)
        })"
        {{ $attributes->except('wire:model')->merge(['class'=>'flex w-fit justify-between items-center space-x-4']) }}
>

    <span class="bold mb-1">{{ __('Score') }}</span>

    <div class="flex relative w-[250px] h-12">
        <div class="flex w-full h-full justify-between items-center space-x-1 pl-[18px] pr-[12px]">
            <template x-for="scoreOption in maxScore">
                <div class="rounded-10 h-3 min-w-6 flex-grow border "
                     :class="scoreOption <= score ? 'bg-primary border-primary' : 'bg-offwhite border-secondary'"
                ></div>
            </template>
        </div>
        <div class="w-full absolute top-0 left-0  flex items-center h-full">
            <input type="range" min="0" max="{{$maxScore}}" class="score-slider-input w-full" value="0" x-model="score"
                   :class="{'hide-thumb': score === null}">
        </div>
    </div>

    <div class="w-16 h-10 border border-blue-grey bg-offwhite flex items-center justify-center rounded-10"
         x-text="score ? score : '-'"
    >
        -
    </div>
</div>