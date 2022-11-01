@props([
    'generalStats'
])

@foreach(['tests', 'assignments'] as $kind)
    <div class="flex-1 gap-4 flex flex-col"
         x-data="{pValue: {{ number_format($generalStats[$kind.'_pvalue_average'], 2)  }} }"
    >
        <div class="flex flex-col">
            <span>{{ __('student.aantal '. $kind. ' gemaakt')}}</span>
            <span class="bold">{{ $generalStats[$kind.'_taken'] }}</span>
        </div>
        <div class="flex flex-col">
            <span>{{ __('student.gemiddelde p-waarde') }}</span>
            <div class="inline-block">
                            <span>
                                {{ __('student.o.b.v. aantal vragen', ['count'=> $generalStats[$kind.'_questions']]) }}
                                <span class="bold px-0.5">P {{ number_format($generalStats[$kind.'_pvalue_average'], 2) }}</span>
                            </span>
                <div class="inline-flex relative">
                                <span x-show="pValue" class="pvalue-indicator"
                                      style="--pvalue-indicator-ball-left: -2px"
                                      :style="{'left': `${pValue * 100}%`}"
                                ></span>
                    <div class="inline-flex rounded-md overflow-hidden w-[70px] h-2.5">
                        <span class="flex-1 inline-flex bg-allred"></span>
                        <span class="flex-1 inline-flex bg-orange"></span>
                        <span class="flex-1 inline-flex bg-student"></span>
                        <span class="flex-1 inline-flex bg-lightgreen"></span>
                        <span class="flex-1 inline-flex bg-cta"></span>
                        <span class="flex-1 inline-flex bg-ctamiddark"></span>
                        <span class="flex-1 inline-flex bg-ctadark"></span>
                    </div>
                </div>
                <span class="note text-xs">1.00</span>
            </div>
        </div>
        <div class="flex flex-col">
            <div>{{ __('student.gemiddeld cijfer') }}</div>
            <div class="flex gap-4 items-center">
                <x-mark-badge :rating="$generalStats[$kind.'_rating_average']"></x-mark-badge>

                <x-button.text-button wire:click="showGrades">
                    <span class="bold">{{ __('student.Bekijk cijferlijst') }}</span>
                    <x-icon.arrow/>
                </x-button.text-button>
            </div>
        </div>
    </div>
@endforeach