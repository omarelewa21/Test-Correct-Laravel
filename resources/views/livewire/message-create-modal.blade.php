<x-modal.base-modal :closable="false">
    <x-slot:title>
        <h2>Bericht</h2>
    </x-slot:title>

    <x-slot:content>
        <div>

        </div>
    </x-slot:content>>

    <x-slot:footer>
        <div class="inline self-end">
            <x-button.text-button class="mr-2"
                                  wire:click="$emit('closeModal')">
                <span>{{__("modal.cancel")}}</span>
            </x-button.text-button>
            <x-button.cta
                    wire:click="send">
                <span>{{__("auth.send")}}</span>
            </x-button.cta>
        </div>
    </x-slot:footer>
</x-modal.base-modal>
