<div class="flex items-center space-x-3 ml-2 mr-2"
     x-data="{  }"
     x-transition:leave.duration.3000ms
     x-show="Alpine.store('connection').offline"
     x-cloak
>

    <x-tooltip  tooltip-classes="left-1/4 -translate-x-1/4" class="w-[40px] h-[40px]" :position-top="true" idle-classes="bg-all-red" :icon-height="true" :icon-width="true" activeTooltipIconClasses="scale-150">
        <x-slot:idleIcon>
            <span class="flex items-center bg-all-red text-white" x-show="!tooltip" x-cloak>
                <x-icon.wifi style="transform: scale(1.5)"/>
            </span>
        </x-slot:idleIcon>
        <div class="flex flex-col">
            <div class="flex w-full items-center">
                <div class="flex items-center justify-center text-white w-[40px] h-[40px] bg-all-red rounded-full"><x-icon.wifi style="transform: scale(1.5)"/></div>
                <span class="ml-2 text-base flex bold">{{ __('student.Geen internetverbinding') }}</span>
            </div>
            <div class="text-base mt-2">
                {{__('student.Geen internetverbinding uitleg') }}
            </div>
        </div>

    </x-tooltip>

{{--    <div>--}}
{{--        <h6 class="all-red">{{__('test_take.attention_required')}}</h6>--}}
{{--    </div>--}}
</div>
