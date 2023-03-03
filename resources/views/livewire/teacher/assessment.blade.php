<div id="assessment-page"
     class="min-h-screen w-full flex"
>
    <x-partials.header.assessment>
        <x-slot:title>
            <h6 class="text-white">Start nakijken: </h6>
            <h4 class="text-white">{!!  clean($testName) !!}</h4>
        </x-slot:title>
        <x-slot:subtitle>Kies je nakijkmethode</x-slot:subtitle>

        <x-slot:collapsedLeft>
            <div>
                Hier navigatie
            </div>
        </x-slot:collapsedLeft>
        <x-slot:panels>
            <x-partials.header.panel>
                <x-slot:sticker>
                    <x-stickers.questions-all />
                </x-slot:sticker>
                <x-slot:title>{{  str(__('co-learning.all_questions'))->ucfirst() }}</x-slot:title>
                <x-slot:subtitle>
                    <div>{{ __('co-learning.all_questions_text') }}</div>
                </x-slot:subtitle>
                <x-slot:button>
                    <x-button.cta size="md">
                        <span>{{ __('co-learning.start') }}</span>
                        <x-icon.arrow />
                    </x-button.cta>
                </x-slot:button>
            </x-partials.header.panel>

            <x-partials.header.panel>
                <x-slot:sticker>
                    <x-stickers.questions-open-only />
                </x-slot:sticker>
                <x-slot:title>{{ str(__('co-learning.open_questions_only'))->ucfirst() }}</x-slot:title>
                <x-slot:subtitle>
                    <div>{{ __('co-learning.open_questions_text') }}</div>
                </x-slot:subtitle>
                <x-slot:button>
                    <x-button.cta size="md"
                                  @click.prevent="handleHeaderCollapse(['OPEN_ONLY', true])"
                    >
                        <span>{{ __('co-learning.start') }}</span>
                        <x-icon.arrow />
                    </x-button.cta>
                </x-slot:button>
            </x-partials.header.panel>
        </x-slot:panels>

    </x-partials.header.assessment>
</div>