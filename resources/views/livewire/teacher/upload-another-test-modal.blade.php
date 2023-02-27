<x-modal.base-modal>
    <x-slot name="title">
        <div class="flex items-center gap-2">
            <span class="w-[30px] h-[30px] inline-flex items-center justify-center rounded-full bg-primary text-white">
                <x-icon.copy/>
            </span>
            <h1>@lang('upload.Toetsgegevens overnemen?')</h1>
        </div>
    </x-slot>

    <x-slot name="content">
        <p>@lang('upload.copy_test_info_text')</p>
    </x-slot>

    <x-slot name="footer">
        <div class="ml-auto flex gap-4" wire:click="$emit('closeModal')">
            <x-button.text-button size="md" x-on:click="$dispatch('upload-another-test', false)">
                <span>@lang('upload.Niet overnemen')</span>
            </x-button.text-button>
            <x-button.cta size="md" x-on:click="$dispatch('upload-another-test', true)">
                <span>@lang('upload.Overnemen')</span>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>