<x-modal.base-modal>
    <x-slot:title>
        <h2>@lang('test-take.Weet u het zeker')?</h2>
    </x-slot:title>
    <x-slot:content>
        <div class="flex flex-col gap-2">
            @foreach($this->displayWarnings as $warning)
            <div class="notification warning stretched">
                <span class="title"><x-icon.exclamation/>{{ $warning['title'] }}</span>
                <span class="body">{{ $warning['body'] ?? '' }}</span>
            </div>
            @endforeach
        </div>

    </x-slot:content>

    <x-slot:footer>
        <div class="flex justify-end w-full gap-4 items-center">
            <x-button.text wire:click="closeModal">
                <span>{{__('general.cancel')}}</span>
            </x-button.text>

            <x-button.cta size="md" wire:click="continue">
                <span>@lang('test-take.Afnemen')</span>
                <x-icon.arrow/>
            </x-button.cta>
        </div>
    </x-slot:footer>

</x-modal.base-modal>