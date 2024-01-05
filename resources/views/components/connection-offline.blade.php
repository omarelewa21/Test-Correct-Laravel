<div class="flex items-center space-x-3 ml-2"
     x-data="{ showOffline: true }"
     x-show.transition.duration.200ms="showOffline"
     x-cloak
     wire:init="shouldDisplayFraudMessage"
{{--     wire:poll.30000ms="isTestTakeEventConfirmed"--}}
>
    <div class="rounded-full bg-all-red text-white flex justify-center items-center connection-offline"
         style="width:40px;height:40px">
        <x-icon.wifi style="transform: scale(1.5)"/>
    </div>
{{--    <div>--}}
{{--        <h6 class="all-red">{{__('test_take.attention_required')}}</h6>--}}
{{--    </div>--}}
</div>
