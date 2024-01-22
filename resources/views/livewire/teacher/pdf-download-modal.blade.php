<x-modal.base-modal x-data="pdfDownload(
            {{ js($translation) }},
            {{ js($this->downloadLinks()) }}
        )"
>
    <x-slot name="title">
        <h2>{{__("teacher.Toets exporteren")}}</h2>
    </x-slot>
    <x-slot name="content">
        @if($displayValueRequiredMessage)
            <div class="mb-4 text-red-500 text-sm">{{ __('cms.Kies een waarde') }}</div>
        @endif
        <div class="flex">
            <div name="block-container" class="grid gap-4 grid-cols-2">
                <div class="col-span-2">
                    {{ __('teacher.Kies een of meerdere onderdelen') }}
                </div>

                @foreach($this->downloadOptions() as $key => $data)
                    <button @class([
                                "test-change-option transition",
                                'hidden' => !$data['show'],
                                'opacity-25' =>!$data['active']
                            ])
                            :class="{'active': selected(@js($key))}"
                            @if($data['active'])
                                x-on:click="select(@js($key))"
                            @endif
                    >
                        <div class="flex">
                            <x-dynamic-component :component="'stickers.'.$data['sticker']" />
                        </div>

                        <div x-show="selected(@js($key))">
                            <x-icon.checkmark class="absolute top-2 right-2 overflow-visible" />
                        </div>
                        <div class="ml-2.5 text-left">
                            <span class="text-base bold">{{ $data['title'] }}</span>
                            <p class="note text-sm">{{ $data['text'] }}</p>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </x-slot>
    <x-slot name="footer">
        <div class="flex justify-end items-center gap-4">
            <x-button.text wire:click="$emit('closeModal')"><span>{{ __('modal.sluiten') }}</span></x-button.text>
            <x-button.cta size="md" x-on:click="export_pdf()"><span>{{ __('cms.pdf_exporteren') }}</span>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>
