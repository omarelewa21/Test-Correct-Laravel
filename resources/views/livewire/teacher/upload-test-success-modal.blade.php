<x-modal.base-modal :closable="false">
    <x-slot name="title">
        <div class="flex items-center gap-2">
            <x-icon.checkmark-circle color="var(--cta-primary)" width="30" height="30"/>
            <h1>@lang('upload.Toets geupload')</h1>
        </div>
    </x-slot>

    <x-slot name="content">
        <p>@lang('upload.test_upload_succes_modal_text')</p>
    </x-slot>

    <x-slot name="footer">
        <div class="ml-auto">
            <x-button.cta size="md" wire:click="close">
                <span>@lang('modal.sluiten')</span>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>