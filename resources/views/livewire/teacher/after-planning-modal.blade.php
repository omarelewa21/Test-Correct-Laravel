<div class="flex flex-col pb-5 pt-8 px-5 sm:px-10 bg-white rounded-10 overflow-hidden shadow-xl transform transition-all sm:w-full">
    <div class="flex justify-between items-center">
        <span> {{ __('teacher.test_take_planned', ['testName' => $testTake->test->name]) }} </span>
        <x-icon.close wire:click="forceCloseModal" class="cursor-pointer hover:text-primary"/>
    </div>

    <div class="divider mb-5 mt-2.5"></div>

    <div class="body1 mb-5">
        {{-- {{ $content }} --}}
    </div>

    <div class="flex justify-between w-full px-2">
        <x-button.text-button size="sm" wire:click="forceCloseModal">
            <span>{{__('Annuleren')}}</span>
        </x-button.text-button>
        <div class="flex space-x-2.5">
            <x-button.cta size="sm">
                <x-icon.checkmark/>
                <span>{{__('teacher.Inplannen')}}</span>
            </x-button.cta>
        </div>
    </div>
</div>
