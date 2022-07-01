<div id="test-detail"
     class="flex flex-col relative w-full min-h-full bg-lightGrey border-secondary mt-12"
>
    <div class="flex w-full border-b border-secondary pb-1">
        <div class="flex w-full justify-between">
            <div class="flex items-center space-x-2.5">
                <x-button.back-round wire:click="redirectToTestOverview"/>
                <div class="flex text-lg bold">
                    <span>{{ __('Toets') }}: {{ $test->name }}</span>
                </div>
            </div>

        </div>

    </div>
    <div class="flex w-full justify-between mt-3 items-center">
        <div class="flex space-x-2.5">
            <div class="bold">{{ $test->subject->name }}</div>
            <div class="italic">{{ $test->abbreviation }}</div>
            <div>{{ $test->authors_as_string }}</div>
        </div>
        <div class="flex note text-sm">
            <span>{{ __('general.Laatst gewijzigd') }}: {{ \Carbon\Carbon::parse($test->updated_at)->format('d/m/\'y') }}</span>
        </div>
    </div>
    <div class="flex w-full justify-between mt-1 note text-sm">
        <div class="flex">
            <span class="text-sm">{{ trans_choice('cms.vraag', $this->amountOfQuestions['regular']) }}, {{ trans_choice('cms.group-question-count', $this->amountOfQuestions['group']) }}</span>
        </div>
    </div>

    <div class="flex w-full justify-end mt-3 note text-sm space-x-2.5">
        <x-button.primary  class="pl-[12px] pr-[12px] opacity-20 cursor-not-allowed" >
            <x-icon.trash/>
        </x-button.primary>
        <x-button.primary  class="pl-[12px] pr-[12px] opacity-20 cursor-not-allowed" >
            <x-icon.edit/>
        </x-button.primary>
        <x-button.primary class="pl-[12px] pr-[12px] " wire:click="$emitTo('navigation-bar', 'redirectToCake', 'planned.my_tests.plan')">
            <x-icon.preview/>
        </x-button.primary>
        <x-button.primary class="pl-[12px] pr-[12px] " wire:click="$emitTo('navigation-bar', 'redirectToCake', 'planned.my_tests.plan')">
            <x-icon.pdf  color="var(--off-white)"/>
        </x-button.primary>
        <x-button.primary class="pl-[12px] pr-[12px]" wire:click="$emitTo('navigation-bar', 'redirectToCake', 'planned.my_tests.plan')">
            <x-icon.copy/>
        </x-button.primary>
        <x-button.cta wire:click="$emitTo('navigation-bar', 'redirectToCake', 'planned.my_tests.plan')">
            <x-icon.schedule/>
            <span>{{ __('cms.Inplannen') }}</span>
        </x-button.cta>
    </div>
    <div class="flex w-full">
        <div class="w-full mx-auto divide-y divide-secondary">
            {{-- Content --}}
            <div class="flex flex-col py-4" style="min-height: 500px">
                <x-grid class="mt-4">
                    @foreach(range(1, 6) as $value)
                        <x-grid.loading-card :delay="$value"/>
                    @endforeach

                    @foreach($test->testQuestions as $testQuestion)
                            {{--<x-grid.question-card :question="$testQuestion->question" />--}}
                            <x-grid.question-card-detail :testQuestion="$testQuestion" wire:loading.class="hidden"/>
                    @endforeach
                </x-grid>
            </div>
        </div>
    </div>
    <x-notification/>
</div>
