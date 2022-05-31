<x-modal-with-footer wire:key="planningModal" maxWidth="4xl" wire:model="showModal" show-cancel-button="false">
    <x-slot name="title">
        <div class="flex justify-between">
            <span>{{ __('teacher.copy for schoollocation') }}</span>
            <span wire:click="$set('showModal', false)" class="cursor-pointer">x</span>
        </div>
    </x-slot>
    <x-slot name="body">
        <div class="email-section mb-4 w-full">
            <div class="mb-4">
                <label>{{ __('teacher.Naam toets of opdracht') }}</label>
                <div class="border-blue-100 form-input w-full p-2 transition ease-in-out duration-150">{{ $test->name }}</div>

            </div>

        </div>
    </x-slot>
    <x-slot name="footer">
        <div class="flex justify-between w-full px-2">
            <x-button.text-button size="sm" wire:click="$set('showModal', false)">
                <span>{{__('Annuleren')}}</span>
            </x-button.text-button>


            <x-button.cta size="sm" wire:click="copy">
                <x-icon.checkmark/>
                <span>{{__('teacher.make copy')}}</span>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal-with-footer>
