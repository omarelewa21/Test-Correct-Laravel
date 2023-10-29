<x-modal.base-modal>
    <x-slot:title><h2>@lang('test-take.Naar CO-Learning')?</h2></x-slot:title>
    <x-slot:content>@lang('test-take.re discuss test text')</x-slot:content>
    <x-slot:footer>
        <div class="flex justify-end gap-4 items-center">
            <x-button.text wire:click="closeModal"><span>@lang('general.cancel')</span></x-button.text>
            <x-button.cta type="link" :href="$this->continue" size="md"><span>@lang('test-take.Naar CO-Learning')</span></x-button.cta>
        </div>
    </x-slot:footer>
</x-modal.base-modal>