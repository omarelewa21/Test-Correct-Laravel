<div class="flex flex-col py-5 px-7 bg-white rounded-10 overflow-hidden shadow-xl transform transition-all sm:w-full">    <div class="px-2.5">
        <h2>
            <div class="flex justify-between">
                <span>{{ __('teacher.test_take_planned', ['testName' => $testTake->test->name]) }}</span>
                <span wire:click="forceCloseModal" class="cursor-pointer">x</span>
            </div>
        </h2>
    </div>

    <div class="divider mb-5 mt-2.5"></div>

    <div class="body1 mb-5 flex flex-col justify-items-center">
        <x-button.secondary class="mt-6">
            <span>{{__('teacher.copyTestLink')}}</span>
        </x-button.secondary>
        <x-button.primary class="mt-6">
            <span>{{__('teacher.goToPlannedTests')}}</span>
        </x-button.primary>
    </div>
</div>
