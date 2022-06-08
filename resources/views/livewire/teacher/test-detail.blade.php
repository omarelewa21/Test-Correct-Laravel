<div id="test-detail"
     class="flex flex-col relative w-full min-h-full bg-lightGrey border-t border-secondary overflow-auto"
>
    <div class="flex w-full border-b border-secondary">
        <div class="flex w-full space-x-4 justify-between">
            <div class="flex align-middle">
                <div class="h-[48px] w-[48px] bg-white rounded-[24px] mr-8 align-middle content-center">
                    <x-icon.arrow-left/>
                </div>
                <div>  {{ __('toets') }}: {{ $test->name }}</div>
            </div>
            <div> {{ __('laatst gewijzigd') }} {{ $test->created_at }}</div>
        </div>
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
