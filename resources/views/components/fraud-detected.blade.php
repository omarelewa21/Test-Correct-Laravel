<div class="flex items-center space-x-3" fraud-detection
     x-data="{ fraud: @entangle('fraudDetected') }"
     x-show.transition.duration.200ms="fraud"
     x-cloak
     wire:init="shouldDisplayFraudMessage"
{{--     wire:poll.30000ms="isTestTakeEventConfirmed"--}}
>
    <div class="fraud-detection rounded-full bg-all-red text-white flex justify-center items-center"
         style="width:40px;height:40px">
        <x-icon.exclamation style="transform: scale(1.5)"/>
    </div>
{{--    <div>--}}
{{--        <h6 class="all-red">{{__('test_take.attention_required')}}</h6>--}}
{{--    </div>--}}
</div>
