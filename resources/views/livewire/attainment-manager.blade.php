<div class="flex flex-col flex-1">
    <p class="text-base">{{ __('cms.Selecteer het domein en het subdomein waaraan deze vraag bijdraagt.') }}</p>
    <div class="grid grid-cols-2 gap-x-6 mt-4">
        <x-input.group label="{{ __('cms.Domein') }}" class="text-base">
            <x-input.select-search name="domains"  wire:model="domainId">

            </x-input.select-search>
        </x-input.group>
        <x-input.group label="{{ __('cms.Subdomein') }}" class="text-base">
            <x-input.select-search name="subdomains"  wire:model="subdomainId">

            </x-input.select-search>
        </x-input.group>
        <x-input.group label="{{ __('cms.Subsubdomein') }}" class="text-base hidden">
            <x-input.select-search name="subsubdomains"  wire:model="subsubdomainId">

            </x-input.select-search>
        </x-input.group>
    </div>
</div>
