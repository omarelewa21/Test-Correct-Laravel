<div class="flex flex-col w-full">
    <div class="w-full px-4 lg:px-8 xl:px-12">
        <div class="flex flex-col mx-auto max-w-7xl space-y-4">
            <div class="flex body2 bold items-center space-x-2">
                <div class="flex items-center space-x-2">
                    <x-icon.schedule/>
                    <span>{{ __('student.planned') }}</span></div>
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
                <x-button.text-button class="rotate-svg-180" wire:click="">
                    <x-icon.arrow/>
                    <span class="text-[32px]">{{ $waitingTestTake->test->name }}</span>
                </x-button.text-button>
            </div>
            <div>
                <div class="grid gap-4 grid-cols-2 md:grid-cols-3 lg:grid-cols-4 body2">

                    <div class="flex flex-col space-y-2">
                        <span>{{ __('student.subject') }}</span>
                        <h6>{!! $waitingTestTake->test->subject->name !!}</h6>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <span>{{ __('student.take_date') }}</span>
                        @if($waitingTestTake->time_start == \Carbon\Carbon::today())
                            <h6 class="capitalize">{{ __('student.today') }}</h6>
                        @else
                            <h6>{{ \Carbon\Carbon::parse($waitingTestTake->time_start)->format('d-m-Y') }}</h6>
                        @endif
                    </div>
                    <div class="flex flex-col space-y-2">
                        <span>{{ __('student.logged_in_students') }}</span>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <span>{{ __('student.clas(ses)') }}</span>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <span>{{ __('student.teacher') }}</span>
                        <h6>{{ $waitingTestTake->user->getFullNameWithAbbreviatedFirstName() }}</h6>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <span>{{ __('student.invigilators') }}</span>
                        <h6>
                            <x-partials.invigilator-list
                                    :invigilators="$this->giveInvigilatorNamesFor($waitingTestTake)"/>
                        </h6>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <span>{{ __('student.weight') }}</span>
                        <h6>{{ $waitingTestTake->weight }}</h6>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <span>{{ __('student.type') }}</span>
                        <x-partials.test-take-type-label type="{{ $waitingTestTake->retake }}"/>
                    </div>

                </div>
            </div>
            <div class="flex w-full items-center space-x-4">
                <div class="divider flex flex-1"></div>
                <div class="pulse-div flex justify-center">
                    @if($waitingTestTake->test_take_status_id == 3)
                        <x-button.cta><span>{{ __('student.start_test') }}</span>
                            <x-icon.arrow/>
                        </x-button.cta>
                    @else
                        <div class="mx-4">{{ __('student.wait_for_test_take') }}</div>
                    @endif
                </div>
                <div class="divider flex flex-1"></div>
            </div>
            <div class="flex w-full justify-center @if($waitingTestTake->test_take_status_id == 3) opacity-50 @endif">
                <x-illustrations.waiting-room/>
            </div>

        </div>
    </div>
    <div class="flex bg-light-grey items-center justify-center py-12">
        <div class="content-section flex flex-col p-6 max-w-2xl space-y-4">
            <h4 class="px-4">{{ __('student.teacher_introduction_title') }}</h4>
            <div class="divider"></div>
            <div class="px-4">
                {{ __('student.teacher_introduction_long') }}
            </div>
        </div>
    </div>
</div>
