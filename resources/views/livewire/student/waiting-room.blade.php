<div id="planned-body"
     x-data="{}"
     x-init="addRelativePaddingToBody('planned-body'); makeHeaderMenuActive('student-header-tests');"
     x-cloak
     class="w-full flex flex-col items-center student-bg"
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('planned-body')"
     wire:ignore.self
>
    <div class="flex w-full justify-center border-b border-system-secondary">
        <div class="flex w-full mx-4 lg:mx-8 xl:mx-12 max-w-7xl space-x-4">
            <div class="py-2 border-b-2 border-system-base border-primary-hover">
                <x-button.text-button type="link" href="{{ route('student.test-takes', ['tab' => 'planned']) }}">{{ __('student.planned') }}</x-button.text-button>
            </div>
            <div class="py-2">
                <x-button.text-button type="link" href="{{ route('student.test-takes', ['tab' => 'discuss']) }}">{{ __('student.discuss') }}</x-button.text-button>
            </div>
            <div class="py-2">
                <x-button.text-button type="link" href="{{ route('student.test-takes', ['tab' => 'review']) }}">{{ __('student.review') }}</x-button.text-button>
            </div>
            <div class="py-2">
                <x-button.text-button type="link" href="{{ route('student.test-takes', ['tab' => 'graded']) }}">{{ __('student.graded') }}</x-button.text-button>
            </div>
        </div>
    </div>
    <div class="flex flex-col w-full mt-10">
        <div class="w-full px-4 lg:px-8 xl:px-12">
            <div class="flex flex-col mx-auto max-w-7xl space-y-4">
                <div class="flex body2 bold items-center space-x-2">
                    <div class="flex items-center space-x-2">
                        <x-icon.schedule/>
                        <span>{{ __('student.planned') }}</span>
                    </div>
                    <x-icon.chevron-small class="opacity-50 w-2 h-3"/>
                    <div class="flex items-center space-x-2 opacity-50">
                        <x-icon.discuss/>
                        <span>{{ __('student.discuss') }}</span></div>
                    <x-icon.chevron-small class="opacity-50"/>
                    <div class="flex items-center space-x-2 opacity-50">
                        <x-icon.preview/>
                        <span>{{ __('student.review') }}</span></div>
                    <x-icon.chevron-small class="opacity-50"/>
                    <div class="flex items-center space-x-2 opacity-50">
                        <x-icon.grade/>
                        <span>{{ __('student.graded') }}</span></div>
                </div>
                <div>
                    <x-button.text-button class="rotate-svg-180" type="link" href="{{ route('student.test-takes', ['tab' => 'planned']) }}">
                        <x-icon.arrow/>
                        <span class="text-[32px]">{{ $waitingTestTake->test->name }}</span>
                    </x-button.text-button>
                </div>
                <div>
                    <x-partials.waiting-room-grid :waitingTestTake="$waitingTestTake"/>
                </div>
                <div class="flex w-full items-center">
                    @if($waitingTestTake->test_take_status_id == \tcCore\TestTakeStatus::STATUS_TAKING_TEST)
                        <div class="divider flex flex-1 pulse-left"></div>
                        <div class="flex flex-col justify-center">
                            <x-button.cta>
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
                </div>
                <div class="flex w-full justify-center @if($waitingTestTake->test_take_status_id == \tcCore\TestTakeStatus::STATUS_TAKING_TEST) opacity-50 @endif">
                    <x-illustrations.waiting-room/>
                </div>

            </div>
        </div>
        <div class="flex bg-light-grey items-center justify-center py-12">
            <div class="content-section flex flex-col w-full max-w-2xl p-8 space-y-4">
                <h4 class="px-3">{{ __('student.teacher_introduction_title') }}</h4>
                <div class="divider"></div>
                <div class="px-3">
                    @if($waitingTestTake->test_take_status_id == \tcCore\TestTakeStatus::STATUS_TAKING_TEST)
                        {!! $waitingTestTake->test->introduction ?: __('student.teacher_introduction_unavailable') !!}
                    @else
                        {{ __('student.teacher_introduction_long') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>