<div class="inline-flex relative"
     x-data="{pValue: {{ $pValueNumber  }} }"
>
                                <span x-show="pValue" class="pvalue-indicator"
                                      style="--pvalue-indicator-ball-left: -2px"
                                      :style="{'left': `${pValue * 100}%`}"
                                ></span>
    <div class="inline-flex rounded-md overflow-hidden w-[70px] h-2.5 @if($disabled) border-slate-300 border @endif">
        @php
            $colors = ['bg-allred', 'bg-orange', 'bg-student', 'bg-lightgreen', 'bg-cta', 'bg-ctamiddark', 'bg-ctadark'];
            if ($disabled) {
                $colors = ['bg-slate-300', 'bg-slate-200', 'bg-slate-100', 'bg-slate-50', 'bg-slate-100', 'bg-slate-200', 'bg-slate-300'];
            }
        @endphp

        @foreach($colors as $color)
            <span class="flex-1 inline-flex {{ $color }} "></span>
        @endforeach

    </div>
</div>
<span class="note text-xs">1.00</span>