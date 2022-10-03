<div class="grid grid-cols-1 gap-x-6 mt-4">
    <span class="bold">{{ $this->title() }}</span>
    <x-input.group label="{{ __('cms.Domein') }}" class="text-base">
        <x-input.select-search name="domains"
                               wire:model="domainId"
                               level="top"
                               placeholder="{{ __('cms.select_a_value') }}"
                               :disabled="$disabled">

        </x-input.select-search>
    </x-input.group>
    <x-input.group label="{{ __('cms.Subdomein') }}" class="text-base sub-select">
        <x-input.select-search name="subdomains"
                               wire:model="subdomainId"
                               level="sub"
                               placeholder="{{ __('cms.select_a_value') }}"
                               :disabled="$disabled">

        </x-input.select-search>
    </x-input.group>
    <x-input.group label="{{ __('cms.Subsubdomein') }}" class="text-base subsub-select">
        <x-input.select-search name="subsubdomains" wire:model="subsubdomainId" level="subsub"
                               placeholder="{{ __('cms.select_a_value') }}" :disabled="$disabled">

        </x-input.select-search>
    </x-input.group>
</div>

