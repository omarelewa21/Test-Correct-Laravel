<x-modal.base-modal wire:model="showModal">
    <x-slot name="title"><h2>{{__('co-learning.close_colearning')}}</h2></x-slot>
    <x-slot name="content">
        <span>{{ __('co-learning.close_colearning_text') }}</span>
    </x-slot>
    <x-slot name="footer">
        <div class="flex justify-end w-full gap-4">

            <x-button.primary wire:click="$emit('redirectBack')"
            >
                <x-icon.arrow-left/>
                <span>{{ __('co-learning.continue_later') }}</span>
            </x-button.primary>

            <x-button.cta wire:click="$emit('finishCoLearning')"
            >
                <span>{{ __('co-learning.finish') }}</span>
                <x-icon.checkmark/>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>
