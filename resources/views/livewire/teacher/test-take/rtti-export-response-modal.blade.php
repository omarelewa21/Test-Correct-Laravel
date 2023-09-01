<x-modal.base-modal :closable="false">
    <x-slot:title>
        <h2>@lang('teacher.Exporteer naar RTTI Online')</h2>
    </x-slot:title>
    <x-slot:content>
        <div class="flex w-full"
             x-init="setTimeout(async () => { await $wire.createExport(); }, 1500)"
        >
            @if($this->rttiExportLog)
                @if($this->rttiExportLog->has_errors)
                    <div class="">
                        <span>
                            {{ __("test-take.Er is iets fout gegaan tijdens het exporteren van de gegevens naar RTTI. Neem contact op met de support desk van Test-Correct met als referentie", ['reference' => '']) }}
                        </span>
                        <span class="bold">{{ $this->rttiExportLog->reference }}</span>
                    </div>
                    @else
                    <span>Gelukt!</span>
                @endif
            @else
                <div class="flex flex-col w-full h-full items-center justify-center">
                    <x-icon.loading-large class="animate-spin" />
                    <span>@lang('test-take.Exporteren')...</span>
                </div>
            @endif
        </div>
    </x-slot:content>
    <x-slot:footer>
        <div class="flex items-center justify-end gap-4">
            <x-button.text wire:click="closeModal" :disabled="!$this->rttiExportLog">
                <span>@lang('general.cancel')</span>
            </x-button.text>
            <x-button.cta type="link" size="md" target="_blank" :href="config('app.knowlegde_bank_url')">
                <span>@lang('navigation.support_page')</span>
            </x-button.cta>
        </div>
    </x-slot:footer>
</x-modal.base-modal>
