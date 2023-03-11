<x-modal.base-modal :closable="false">
    <x-slot name="title">
        <div class="flex items-center">
            <h1>@lang('upload.Remark')</h1>
        </div>
    </x-slot>

    <x-slot name="content">
        <p>@lang('upload.Not allowed due to temp school')</p>
    </x-slot>

    <x-slot name="footer">
        <div class="ml-auto">
            <x-button.cta size="md" wire:click="close">
                <span>@lang('modal.Terug')</span>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>