@if($testTakeStatusStage === 'planned')
    @if($isTakeOpen)
        <div class="divider flex flex-1 pulse-left"></div>
        <div class="flex flex-col justify-center">
            <x-button.cta x-on:click="startCountdown = true; startCountdownTimer($refs.root._x_dataStack[0]);" selid="start-test">
                <span>{{ __('student.start_test') }}</span>
                <x-icon.arrow/>
            </x-button.cta>
        </div>
        <div class="divider flex flex-1 pulse-right"></div>
    @else
        <div class="divider flex flex-1"></div>
        <div class="flex flex-col justify-center">
            <div class="mx-4" selid="waiting-room-text">{{ $this->getButtonTextForPlannedTakes() }}</div>
        </div>
        <div class="divider flex flex-1"></div>
    @endif

@elseif($testTakeStatusStage === 'discuss')
    @if($isTakeOpen)
        <div class="divider flex flex-1 pulse-left"></div>
        <div class="flex flex-col justify-center">
            <x-button.cta wire:click="startDiscussing" selid="start-discuss">
                <x-icon.discuss/>
                <span>{{ __('student.start_discuss') }}</span>
            </x-button.cta>
        </div>
        <div class="divider flex flex-1 pulse-right"></div>
    @else
        <div class="divider flex flex-1"></div>
        <div class="flex flex-col justify-center">
            <div class="mx-4" selid="waiting-room-text">{{ __('student.wait_for_test_take_discuss') }}</div>
        </div>
        <div class="divider flex flex-1"></div>
    @endif
@elseif($testTakeStatusStage === 'review')
    @if($isTakeOpen)
        <div class="divider flex flex-1 pulse-left"></div>
        <div class="flex flex-col justify-center">
            <x-button.cta wire:click="startReview" selid="start-review">
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
    @if($isTakeOpen)
        <div class="divider flex flex-1 pulse-left"></div>
        <div class="flex flex-col justify-center">
            <x-button.cta wire:click="startReview" selid="start-review">
                <x-icon.preview/>
                <span>{{ __('student.start_review') }}</span>
            </x-button.cta>
        </div>
        <div class="divider flex flex-1 pulse-right"></div>
    @else
        <div class="divider flex flex-1"></div>
        <div class="flex flex-col justify-center">
            <div class="mx-4" selid="waiting-room-text">{{ __('student.cannot_review_test') }}</div>
        </div>
        <div class="divider flex flex-1"></div>
    @endif
@endif