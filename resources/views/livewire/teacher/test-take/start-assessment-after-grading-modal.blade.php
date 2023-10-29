<x-modal.base-modal>
    <x-slot:title><h2>@lang('test-take.Naar nakijken')?</h2></x-slot:title>
    <x-slot:content>@lang('test-take.re assess test text')</x-slot:content>
    <x-slot:footer>
        <div class="flex justify-end gap-4 items-center">
            <x-button.text wire:click="closeModal"><span>@lang('general.cancel')</span></x-button.text>
            <x-button.cta type="link" :href="$this->continue" size="md"><span>@lang('test-take.Naar nakijken')</span></x-button.cta>
        </div>
    </x-slot:footer>
</x-modal.base-modal>