<div>
    <p>{{ __('Selecteer het domein en het subdomein waaraan deze vraag bijdraagt.') }}</p>
    <div class="flex space-x-4 mt-4">
        <x-input.group label="{{ __('Domein') }}" class="w-1/2">
            <x-input.select-search name="domains"  wire:model="domainId"  placeholder="{{ __('Selecteer een waarde') }}">

            </x-input.select-search>
        </x-input.group>
        <x-input.group label="{{ __('Subdomein') }}" class="w-1/2">
            <x-input.select-search name="subdomains"  wire:model="subdomainId"  placeholder="{{ __('Selecteer een waarde') }}">

            </x-input.select-search>
        </x-input.group>

    </div>
</div>
