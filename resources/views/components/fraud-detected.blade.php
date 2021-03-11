<div class="flex items-center space-x-3" fraud-detection
     x-data="{ fraud: @entangle('fraudDetected') }"
{{--     x-on:blur.window="@this.createTestTakeEvent('blur'); Notify.notify('Het is niet toegestaan de app te verlaten.', 'error')"--}}
{{--     x-on:resize.window="@this.createTestTakeEvent('resize');"--}}
{{--     x-on:unload.window="@this.createTestTakeEvent('application-closed')"--}}
     x-show.transition.duration.200ms="fraud"
     x-cloak
>
    <div class="fraud-detection rounded-full bg-all-red text-white flex justify-center items-center"
         style="width:40px;height:40px">
        <img src="{{ asset('/svg/icons/exclamation-white.svg') }}" alt="" width="6" height="30">
    </div>
    <div>
        <h6 class="all-red">{{__('test_take.attention_required')}}</h6>
    </div>
</div>
