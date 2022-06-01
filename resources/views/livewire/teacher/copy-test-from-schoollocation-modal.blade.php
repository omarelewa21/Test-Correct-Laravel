<x-modal-with-footer wire:key="planningModal" maxWidth="2xl" wire:model="showModal" show-cancel-button="false">
    <x-slot name="title">
        <div class="flex justify-between">
            <span>{{ __('teacher.copy for schoollocation') }}</span>
            <span wire:click="$set('showModal', false)" class="cursor-pointer">x</span>
        </div>
    </x-slot>
    <x-slot name="body">
        <div class="mb-4 w-full">
            <x-input.group class="mb-4 input-group w-full" label="{{ __('teacher.Naam toets of opdracht') }}">
                <x-input.text wire:model="test.name" class="w-full"/>
            </x-input.group>
        </div>
        <div class="mb-4 w-full flex space-x-2">
            <div class="mb-4 flex-1">
                <x-input.group class="mb-4 input-group w-full" label="{{ __('teacher.examenvak') }}">
                    <x-input.select disabled class="w-full">
                        <option>{{ $base_subject }}</option>
                    </x-input.select>
                </x-input.group>
            </div>
            <div class="mb-4 flex-1">
                <x-input.group class="mb-4 input-group w-full" label="{{ __('teacher.kies een vak') }}">
                    <x-input.select class="w-full" wire:model="test.subject_id">
                        @foreach($this->allowedSubjectsForExamnSubjects as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </x-input.select>
                </x-input.group>
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
