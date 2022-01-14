<div class="flex flex-col flex-1">
    <p class="text-base">{{ __('Selecteer het domein en het subdomein waaraan deze vraag bijdraagt.') }}</p>
    <div class="grid grid-cols-2 gap-x-6 mt-4">
        <x-input.group label="{{ __('Domein') }}" class="text-base">
            <x-input.select-search name="domains"  wire:model="domainId"  placeholder="{{ __('Selecteer een waarde') }}">

            </x-input.select-search>
        </x-input.group>
        <x-input.group label="{{ __('Subdomein') }}" class="text-base">
            <x-input.select-search name="subdomains"  wire:model="subdomainId"  placeholder="{{ __('Selecteer een waarde') }}">

            </x-input.select-search>
        </x-input.group>

    </div>
</div>
