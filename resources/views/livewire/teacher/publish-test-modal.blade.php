<x-modal.base-modal>
    <x-slot name="title">
        <div class="flex w-full justify-between items-center">
            <h2>{{__("test.Wil je de toets publiceren")}}?</h2>

            <span>
                <x-tooltip :always-left="true" :useClicks="true">
                    <div class="block">
                        <span>{{ __('test.publish_test_explanation') }}</span>
                        <x-button.text-button class="text-base"
                                              size="xs"
                                              type="link"
                                              href="{{ $knowledgebankUrl }}"
                                              target="_blank"
                        >
                            <span>{{ __('general.Lees meer') }}</span>
                            <x-icon.arrow-small/>
                        </x-button.text-button>
                    </div>
                </x-tooltip>
            </span>
        </div>
    </x-slot>

    <x-slot name="content">
        @if($showInfo)
            <div class="notification info stretched mb-2.5">
                <div class="title">{{ __('test.publish_test_explanation_title') }}</div>
                <div class="body">
                    <span>{{ __('test.publish_test_explanation') }}</span>
                    <x-button.text-button class="text-sm primary hover:underline"
                                          size="xs"
                                          type="link"
                                          href="{{ $knowledgebankUrl }}"
                                          target="_blank"
                    >
                        <span>{{ __('general.Lees meer') }}</span>
                        <x-icon.arrow-small/>
                    </x-button.text-button>
                </div>
            </div>
        @endif
        <div>{{ __('test.publish_test_text') }}</div>

        @notempty($testErrors)
        <div class="flex flex-col gap-2.5 mt-2.5">
            @foreach($testErrors as $title => $message)
                <x-notification-message :title="$title" :message="$message"/>
            @endforeach
        </div>
        @endnotempty
    </x-slot>

    <x-slot name="footer">
        <div class="flex w-full justify-end gap-4">
            <x-button.text-button wire:click="closeModal()">
                <span>{{ __('modal.cancel') }}</span>
            </x-button.text-button>

            <x-button.cta wire:click="handle()" :disabled="!empty($testErrors)">
                <x-icon.publish/>
                <span>{{ __('test.publish') }}</span>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>