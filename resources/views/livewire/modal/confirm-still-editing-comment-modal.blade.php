<x-modal.base-modal force-close="true">
    <x-slot name="title">
        <h2>{{ __('modal.Feedback_in_bewerking_title') }}</h2>
    </x-slot>
    <x-slot name="content">
        @lang('modal.Feedback_in_bewerking_text')
    </x-slot>
    <x-slot name="footer">
        <div class="flex justify-end w-full gap-4">
            <x-button.text-button wire:click.prevent="closeModal">
                <span>{{__('modal.Terug')}}</span>
            </x-button.text-button>
            <x-button.cta wire:click.prevent="continue()">
                <x-icon.checkmark/>
                <span>{{__('test-take.Doorgaan')}}</span>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>
