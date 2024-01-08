<div class="flex items-center space-x-3 ml-2"
     x-data="{  }"
     x-show="Alpine.store('connection').offline"
     x-transition:enter.duration.200ms
     x-transition:leave.duration.3000ms
     x-cloak
>


        <x-tooltip class="mr-2"
                :always-left="false"
        >
            <span class="text-base text-left">Heel veel uitleg met wat er te doen staat als je offline bent</span>
        </x-tooltip>

    <div class="rounded-full bg-all-red text-white flex justify-center items-center connection-offline"
         style="width:40px;height:40px">
        <x-icon.wifi style="transform: scale(1.5)"/>
    </div>
{{--    <div>--}}
{{--        <h6 class="all-red">{{__('test_take.attention_required')}}</h6>--}}
{{--    </div>--}}
</div>
