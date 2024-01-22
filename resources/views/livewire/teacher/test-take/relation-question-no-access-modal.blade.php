<x-modal.base-modal>
    <x-slot:title>
        <h2>@lang('general.Geen toegang')</h2>
    </x-slot:title>
    <x-slot:content>@lang('test.test_contains_relation_question')</x-slot:content>
    <x-slot:footer>
        <div class="flex justify-end">
            <x-button.primary wire:click="closeModal" size="md">
                <span>@lang('modal.sluiten')</span>
            </x-button.primary>
        </div>
    </x-slot:footer>
</x-modal.base-modal>