<div id="planned-body"
     x-data="{startCountdown: false, isTakeOpen: @entangle('isTakeOpen'), countdownNumber: {{ $this->getCountdownNumber() }} }"
     x-init="
        addRelativePaddingToBody('planned-body');
        makeHeaderMenuActive('student-header-tests');
     "
     x-cloak
     class="w-full flex flex-col items-center student-bg"
     :class="{'overflow-hidden h-screen' : startCountdown}"
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('planned-body')"
     wire:ignore.self
     wire:poll.10000ms="isTestTakeOpen()"
>
    <div class="flex w-full justify-center border-b border-system-secondary transition-all duration-500"
         :class="{'opacity-0': startCountdown}">
        <div class="flex w-full mx-4 lg:mx-8 xl:mx-12 max-w-7xl space-x-4">
            <div class="py-2 border-b-2 border-system-base border-primary-hover">
                <x-button.text-button type="link"
                                      href="{{ route('student.test-takes', ['tab' => 'planned']) }}">{{ __('student.planned') }}</x-button.text-button>
            </div>
            <div class="py-2">
                <x-button.text-button type="link"
                                      href="{{ route('student.test-takes', ['tab' => 'discuss']) }}">{{ __('student.discuss') }}</x-button.text-button>
            </div>
            <div class="py-2">
                <x-button.text-button type="link"
                                      href="{{ route('student.test-takes', ['tab' => 'review']) }}">{{ __('student.review') }}</x-button.text-button>
            </div>
            <div class="py-2">
                <x-button.text-button type="link"
                                      href="{{ route('student.test-takes', ['tab' => 'graded']) }}">{{ __('student.graded') }}</x-button.text-button>
            </div>
        </div>
    </div>
    <div class="flex flex-col w-full mt-10">
        <div class="w-full px-4 lg:px-8 xl:px-12 transition-all duration-500">
            <div class="flex flex-col mx-auto max-w-7xl space-y-4 transition-all duration-500">
                <div>
                    <x-partials.test-take-breadcrumbs step="1"/>
                </div>
                <div>
                    <x-button.text-button class="rotate-svg-180" type="link"
                                          href="{{ route('student.test-takes', ['tab' => 'planned']) }}">
                        <x-icon.arrow/>
                        <span class="text-[32px]">{{ $waitingTestTake->test_name }}</span>
                    </x-button.text-button>
                </div>
                <div>
                    <x-partials.waiting-room-grid :waitingTestTake="$waitingTestTake"/>
                </div>
                <div class="flex w-full items-center h-10">
                    @if($isTakeOpen)
                        <div class="divider flex flex-1 pulse-left"></div>
                        <div class="flex flex-col justify-center">
                            <x-button.cta x-on:click="startCountdown = true;
                                                        countdownTimer = setInterval(function() {
                                                            console.log(countdownNumber);
                                                            countdownNumber -= 1;
                                                            if (countdownNumber === 0) {
                                                                Livewire.emitTo('student.waiting-room', 'start-test-take')
                                                                clearInterval(countdownTimer);
                                                            }
                                                        },1000);
                                                        ">
                                <span>{{ __('student.start_test') }}</span>
                                <x-icon.arrow/>
                            </x-button.cta>
                        </div>
                        <div class="divider flex flex-1 pulse-right"></div>
                    @elseif($this->isTakeAlreadyTaken)
                        <div class="divider flex flex-1"></div>
                        <div class="flex flex-col justify-center">
                            <div class="mx-4">{{ __('student.test_already_taken') }}</div>
                        </div>
                        <div class="divider flex flex-1"></div>
                    @else
                        <div class="divider flex flex-1"></div>
                        <div class="flex flex-col justify-center">
                            <div class="mx-4">{{ __('student.wait_for_test_take') }}</div>
                        </div>
                        <div class="divider flex flex-1"></div>
                    @endif
                </div>
                <div class="flex w-full justify-center transition-all duration-300"
                     :class="{'opacity-50' : isTakeOpen}">
                    <x-illustrations.waiting-room/>
                </div>

            </div>
        </div>
        <div class="flex bg-light-grey items-center justify-center py-12">
            <div class="content-section flex flex-col w-full max-w-2xl p-8 space-y-4">
                <h4 class="px-3">{{ __('student.teacher_introduction_title') }}</h4>
                <div class="divider"></div>
                <div class="px-3">
                    @if($isTakeOpen)
                        {!! $waitingTestTake->test->introduction ?: __('student.teacher_introduction_unavailable') !!}
                    @else
                        {{ __('student.teacher_introduction_long') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="w-full bg-white fixed bottom-0 footer-shadow transition-all duration-500 z-10"
         :class="{'pb-16 pt-1.5' : startCountdown}"></div>

    <div class="fixed student-bg top-0 left-0 h-full w-full transition-all duration-500 py-[70px]"
         x-show.transition.500ms="startCountdown"
    >
        <div class="w-full h-full px-4 lg:px-8 xl:px-12 transition-all duration-500">
            <div class="flex h-full flex-col mx-auto max-w-7xl transition-all duration-500 pt-16">
                <div class="flex flex-col mb-4">
                    <span class="-mb-2">Geplande toets</span>
                    <x-button.text-button class="rotate-svg-180"
                                          x-on:click="startCountdown = false; clearInterval(countdownTimer); countdownNumber = {{ $this->getCountdownNumber() }}">
                        <x-icon.arrow/>
                        <span class="text-[32px]">{{ $waitingTestTake->test->name }}</span>
                    </x-button.text-button>
                </div>
                <div class="flex flex-col flex-1 w-full items-center mt-16 space-y-3">
                    <span>Toets maken start in</span>
                    <div class="flex w-28 h-28 justify-center items-center text-center bold border-4 border-system-base rounded-full"
                         style="font-size: 64px">
                        <span class="w-full" x-text="countdownNumber"></span>
                    </div>
                </div>
                <div class="flex w-full mt-auto opacity-50 justify-center">
                    <x-illustrations.waiting-room/>
                </div>
            </div>
        </div>
    </div>
</div>