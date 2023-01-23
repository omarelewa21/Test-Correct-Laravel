<div class="flex w-full items-center py-1.5"
     wire:key="pvalue-{{ $pValue->getKey() }}"
     wire:ignore
     x-data="{
         orange: [1,7],
         yellow: [2,6],
         green: [3,5],
         darkgreen: [4],
         indicatorLeft: ({{ $pValue->p_value }} * 100) + '%'
     }"
>
    <div class="flex items-center min-w-[110px]">
        <span class="note text-sm">{{ $pValue->education_level_year }} {{ $pValue->educationLevel->name ??  __('general.unavailable') }}:</span>
    </div>
    <div class="flex items-center">
        <span class="text-sm">
            <span class="bold">P</span>
            {!! number_format( $pValue->p_value, 2) !!}
        </span>
        <div class="flex relative mx-2">
            <div class="flex rounded-10 overflow-hidden">
                <template x-for="i in 7">
                    <span class="w-1.5 h-2 bg-allred"
                          :class="{
                            'bg-orange': orange.includes(i),
                            'bg-lightgreen': green.includes(i),
                            'bg-student': yellow.includes(i),
                            'bg-ctamiddark': darkgreen.includes(i)

                            }"
                    ></span>
                </template>
            </div>
            <span class="pvalue-indicator small" :style="'left:' + indicatorLeft"></span>
        </div>
        <span class="text-xs text-midgrey">1.0</span>
    </div>

    <div class="flex items-center ml-4">
        <span class="inline-flex text-sm">
            {{ $pValue->p_value_count }} {{ __("cms.keer afgenomen") }}
        </span>
    </div>
</div>