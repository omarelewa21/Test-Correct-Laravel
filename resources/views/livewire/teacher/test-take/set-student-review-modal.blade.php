<x-modal.base-modal>
    <x-slot:title>
        <h2>@lang('test-take.Inzien instellen')</h2>
    </x-slot:title>
    <x-slot:content>
        <div class="flex flex-col gap-2">
            <span>@lang('test-take.Van wanneer tot wanneer mogen studenten de toets inzien?')</span>
            <div class="flex gap-4">
                <x-input.group class="flex flex-1 {{ $errors->has('showResults') ? 'datepicker-error' : '' }}"
                               :label="sprintf('%s %s', __('test-take.Datum & tijd'), __('test-take.tot'))"
                >
                    <x-input.datepicker wire:model="showResults"
                                        locale="nl"
                                        minDate="today"
                                        class="bg-offwhite"
                                        date-format="Y-m-d H:i:S"
                                        alt-format="d-m-Y H:i"
                                        :enable-time="true"
                    />
                </x-input.group>
            </div>
            <div>
                <x-input.toggle-row-with-title wire:model="showCorrectionModel"
                                               container-class="!border-0"
                >
                    <x-icon.preview />
                    <span>@lang('account.Antwoordmodel tonen')</span>
                </x-input.toggle-row-with-title>
            </div>

            @foreach($errors->all() as $error)
                <div class="notification error stretched">
                    <div class="title">{{ $error }}</div>
                </div>
            @endforeach
        </div>

    </x-slot:content>

    <x-slot:footer>
        <div class="flex justify-end items-center w-full gap-4">
            <x-button.text size="md" wire:click="closeModal">
                <span>{{__('general.cancel')}}</span>
            </x-button.text>

            <x-button.cta size="md" wire:click="continue">
                <span>@lang('test-take.Inzien openzetten')</span>
                <x-icon.arrow />
            </x-button.cta>
        </div>
    </x-slot:footer>

</x-modal.base-modal>