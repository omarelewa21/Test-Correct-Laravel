<x-modal.base-modal>
    <x-slot:title>
        <h2>@lang('message.Stuur bericht')</h2>
    </x-slot:title>

    <x-slot:content>
        <div class="flex flex-col w-full gap-4">
            <x-input.group :label="__('message.Onderwerp')" for="message-subject">
                <x-input.text id="message-subject" wire:model="subject" />
            </x-input.group>

            <x-input.group :label="__('message.Bericht')" for="message-message">
                <x-input.textarea id="message-message" wire:model="message" />
            </x-input.group>

            <div class="flex gap-2 flex-col">
                @if($errors->isNotEmpty())
                    @foreach($errors->all() as $error)
                        <div class="notification error stretched w-full">
                            <span class="title">{{ $error }}</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </x-slot:content>
    >

    <x-slot:footer>
        <div class="inline self-end">
            <x-button.text class="mr-2" wire:click="$emit('closeModal')">
                <span>@lang("modal.cancel")</span>
            </x-button.text>

            <x-button.cta wire:click="send" size="md">
                <span>@lang("auth.send")</span>
            </x-button.cta>
        </div>
    </x-slot:footer>
</x-modal.base-modal>
