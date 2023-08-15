<x-modal.base-modal>
    <x-slot:title>
        <h2>@lang('onboarding.Er is helaas iets fout gegaan')</h2>
    </x-slot:title>
    <x-slot:content>
        {{ __("test-take.Er is iets fout gegaan tijdens het exporteren van de gegevens naar RTTI. Neem contact op met de support desk van Test-Correct met als referentie", ['reference' => $this->rttiExportLog->reference]) }}
    </x-slot:content>
    <x-slot:footer>
        <div class="flex items-center justify-end">
            <x-button.text wire:click="closeModal">
                <span>@lang('general.close')</span>
            </x-button.text>
            <x-button.cta type="link" target="_blank" :href="{{  }}">
                <span>@lang('navigation.support_page')</span>
            </x-button.cta>
        </div>
    </x-slot:footer>
</x-modal.base-modal>
