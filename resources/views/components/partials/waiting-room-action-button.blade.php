@if($testTakeStatusStage === 'planned')
    @if($isTakeOpen)
        <div class="divider flex flex-1 pulse-left"></div>
        <div class="flex flex-col justify-center">
            <x-button.cta x-on:click="startCountdown = true; startCountdownTimer($refs.root._x_dataStack[0]);">
                <span>{{ __('student.start_test') }}</span>
                <x-icon.arrow/>
            </x-button.cta>
        </div>
        <div class="divider flex flex-1 pulse-right"></div>
    @else
        <div class="divider flex flex-1"></div>
        <div class="flex flex-col justify-center">
            <div class="mx-4">{{ __('student.wait_for_test_take') }}</div>
        </div>
        <div class="divider flex flex-1"></div>
    @endif

@elseif($testTakeStatusStage === 'discuss')
    @if($isTakeOpen)
        <div class="divider flex flex-1 pulse-left"></div>
        <div class="flex flex-col justify-center">
            <x-button.cta x-on:click="">
                <x-icon.discuss/>
                <span>{{ __('student.start_discuss') }}</span>
            </x-button.cta>
        </div>
        <div class="divider flex flex-1 pulse-right"></div>
    @else
        <div class="divider flex flex-1"></div>
        <div class="flex flex-col justify-center">
            <div class="mx-4">{{ __('student.wait_for_test_take_discuss') }}</div>
        </div>
        <div class="divider flex flex-1"></div>
    @endif
@elseif($testTakeStatusStage === 'review')
    @if($isTakeOpen)
        <div class="divider flex flex-1 pulse-left"></div>
        <div class="flex flex-col justify-center">
            <x-button.cta x-on:click="">
                <x-icon.preview/>
                <span>{{ __('student.start_review') }}</span>
            </x-button.cta>
        </div>
        <div class="divider flex flex-1 pulse-right"></div>
    @else
        <div class="divider flex flex-1"></div>
        <div class="flex flex-col justify-center">
            <div class="mx-4">{{ __('student.wait_for_test_take_review') }}</div>
        </div>
        <div class="divider flex flex-1"></div>
    @endif
@elseif($testTakeStatusStage === 'graded')
    <div class="divider flex flex-1"></div>
    <div class="flex flex-col justify-center">
        <div class="mx-4">{{ __('student.cannot_review_test') }}</div>
    </div>
    <div class="divider flex flex-1"></div>
@endif