<div id="login-body" class="flex justify-center items-center min-h-screen"
     x-data="{processing: true}"
     x-init="setTimeout(() => {
        processing = false;
     }, 4000)"
>
    <div class="w-full max-w-[800px] space-y-4 mx-4 py-4">
        <div class="content-section shadow-xl flex flex-col relative overflow-hidden" style="min-height: 550px">
            <div class="flex w-full space-x-2.5 primary-gradient p-10 bg-red-200 transition-all"
                 :class="processing ? 'primary-gradient' : 'cta-gradient'"
            >
                <div>
                    <x-stickers.congratulations/>
                </div>
                <div class="space-y-3 mt-4">
                        <h1 class="text-white">{{ __('auth.congratulations_linked')  }}</h1>
                    <template x-if="processing">
                        <div>
                            <h5 class="text-white">{{ __('auth.information_being_processed')  }}</h5>
                        </div>
                    </template>
                    <template x-if="!processing">
                        <div class="flex text-white space-x-2 items-center">
                            <x-icon.checkmark/>
                            <h5 class="text-white">{{ __('auth.information_processed')  }}</h5>
                        </div>
                    </template>
                </div>
            </div>
            <div class="flex flex-1 flex-col p-10 pt-8">
                <div>
                    @if($this->with_account)
                        <span class="text-lg">{{ __('auth.linked_email_with_account') }}</span>
                    @else
                        <span class="text-lg">{{ __('auth.linked_email_no_account_verify') }}</span>
                    @endif
                </div>

                <div class="flex mt-auto w-full justify-end">
                    <x-button.cta size="md" x-bind:disabled="processing" wire:click="logInToTC">
                        <span>{{ __('auth.Inloggen op Test-Correct') }}</span>
                        <x-icon.arrow/>
                    </x-button.cta>
                </div>
            </div>
        </div>
    </div>
</div>
