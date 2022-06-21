<div id="test-detail"
     class="flex flex-col relative w-full min-h-full bg-lightGrey border-t border-secondary overflow-auto"
>
    <div class="flex w-full border-b border-secondary">
        <div class="flex w-full space-x-4 justify-between">
            <div class="flex items-center">
                <button class="flex items-center justify-center rounded-full border bg-white/20 w-10 h-10 rotate-svg-180 hover:scale-105 transition-transform" wire:click="saveAndRedirect">
                    <svg class="inline-block" width="14" height="13" xmlns="http://www.w3.org/2000/svg">
                        <g class="stroke-current" fill="none" fill-rule="evenodd" stroke-linecap="round" stroke-width="3">
                            <path d="M1.5 6.5h10M6.5 1.5l5 5-5 5"></path>
                        </g>
                    </svg>

                </button>
                <div class="font-bold">{{ __('Toets') }}: {{ $test->name }}</div>
            </div>

        </div>

    </div>
    <div class="flex w-full justify-between mt-3">
        <div class="flex space-x-2.5">
            <div class="font-bold">{{ $test->subject->name }}</div>
            <div class="italic">{{ $test->abbreviation }}</div>
            <div>{{ $test->authors_as_string }}</div>
        </div>

    </div>
    <div class="flex w-full justify-between mt-1 note text-sm">
        <div class="flex">
            4 vraaggroep(en), 37 vragen
        </div>
        <div> {{ __('laatst gewijzigd') }}: {{ $test->updated_at }}</div>
    </div>

    <div class="flex w-full justify-end mt-3 note text-sm space-x-2.5">
        <x-button.primary disabled class="pl-[12px] pr-[12px] " wire:click="$emitTo('navigation-bar', 'redirectToCake', 'planned.my_tests.plan')">
            <x-icon.trash/>
        </x-button.primary>
        <x-button.primary disabled class="pl-[12px] pr-[12px] " wire:click="$emitTo('navigation-bar', 'redirectToCake', 'planned.my_tests.plan')">
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
                        <x-grid.question-card-detail :testQuestion="$testQuestion" wire:loading.class="hidden"/>
                    @endforeach
                </x-grid>
            </div>
        </div>
    </div>
    <x-notification/>
</div>
