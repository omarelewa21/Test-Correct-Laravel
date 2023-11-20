<div x-data="{
        view: @entangle('sliderButtonSelected'),
        closeOnFirstAdd: @entangle('closeOnFirstAdd'),
        updates: [],
        init() {
            this.$watch('view', value => {
                this.$root.querySelectorAll(`#${value}-view-container .custom-choices`).forEach(choice => {
                    setTimeout(() => {
                        choice.dispatchEvent(new CustomEvent('reset-width'));
                    }, 10)
                });
            })
        },
        done() {
            document.querySelector('.word-list-container').dispatchEvent(
                new CustomEvent(`add-${type}`, {detail: { uuid }} )
            )
            this.openSidePanel = false;
        },
        add(type, uuid) {
            this.updates[type].push(uuid);

            if(this.closeOnFirstAdd) {
                this.done();
            }
        },
        }"
     wire:key="versionable-{{ $sliderButtonSelected }}"
     wire:ignore.self
>
    <div class="py-2 px-6 flex w-full bg-white z-10 border-b border-bluegrey">
        <div class="flex items-center space-x-2.5">
            <x-button.back-round x-on:click="done()" />

            <span class="bold text-lg cursor-default">{{ __('cms.Bestaande vraag toevoegen') }}</span>
        </div>
        <div class="flex ml-auto items-center space-x-2.5 relative">
            @if($showSliderButtons)
                <x-button.slider wire:model="sliderButtonSelected"
                                 wire:key="slider-{{ $sliderButtonSelected }}"
                                 button-width="180px"
                                 :disabled="$sliderButtonDisabled"
                                 :options="$sliderButtonOptions"
                />
            @endif
            <x-button.cta x-on:click="done()">
                <span>{{ __('onboarding.Klaar') }}</span>
            </x-button.cta>
        </div>
    </div>

    <div class="flex flex-col w-full" wire:key="selected-tab-{{ $sliderButtonSelected }}">
        <div id="lists-view-container" x-show="view === 'lists'" class="flex flex-col w-full">
            <livewire:teacher.word-lists-overview :addable="true" view="cms" />
        </div>
        <div id="words-view-container" x-show="view === 'words'" class="flex flex-col w-full">
            <livewire:teacher.words-overview :addable="true" view="cms"/>
        </div>
    </div>
</div>