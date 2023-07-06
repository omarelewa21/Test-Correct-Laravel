<x-modal.base-modal force-close="true">
    <x-slot name="title">
        <h2>{{ __('teacher.copy for schoollocation') }}</h2>
    </x-slot>
    <x-slot name="content">
        <div class="w-full">
            <x-input.group class="mb-4 input-group w-full" label="{{ __('teacher.Naam toets of opdracht') }}">
                <x-input.text wire:model="request.name" class="w-full"/>
            </x-input.group>
        </div>
        <div class="w-full flex gap-4">
            <div class="flex-1">
                <x-input.group class="mb-4 input-group w-full" label="{{ __('teacher.examenvak') }}">
                    <x-input.text class="w-full" value="{{ $base_subject }}" :disabled="true"/>
                </x-input.group>
            </div>
            <div class="flex-1">
                <x-input.group class="mb-4 input-group w-full" label="{{ __('teacher.kies een vak') }}">
                    <x-input.select class="w-full" wire:model="request.subject_id">
                        @foreach($this->allowedSubjectsForExamnSubjects as $key => $value)
                            <x-input.option :value="$key" :label="$value"/>
                        @endforeach
                    </x-input.select>
                </x-input.group>
            </div>
        </div>

    </x-slot>
    <x-slot name="footer">
        <div class="flex justify-end w-full gap-4">
            <x-button.text-button wire:click="closeModal">
                <span>{{__('modal.annuleren')}}</span>
            </x-button.text-button>
            <x-button.cta wire:click="copy('{{ $this->testUuid }}')">
                <x-icon.checkmark/>
                <span>{{__('teacher.make copy')}}</span>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>
