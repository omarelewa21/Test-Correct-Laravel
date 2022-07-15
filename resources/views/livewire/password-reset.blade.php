<div>
    <div class="flex-grow">
        <form autocomplete="off" class="h-full relative" wire:submit.prevent="resetPassword" action="#" method="POST">
            <div class="input-section">
                <h5 class="w-full text-center leading-6 pb-2.5">{{__('password-reset.Maak een nieuw wachtwoord')}}</h5>
                <div class="mb-4">{{ __('password-reset.Vul jouw nieuwe wachtwoord in') }}</div>
                <div class="email-section mb-4 w-full">
                    <div class="mb-4">
                        <div class="input-group w-full">
                            <input id="username" wire:model.lazy="username"
                                   autocomplete="new-password"
                                   class="form-input @error('registration.username') border-red @enderror"
                                   autofocus>
                            <label for="username"
                                   class="transition ease-in-out duration-150">{{__("password-reset.E-mail")}}</label>
                        </div>
                    </div>
                </div>
                <div class="password ">
                    <div class="input-group w-full mb-4">
                        <input id="password" wire:model="password" type="password"
                               autocomplete="new-password"
                               class="form-input ">
                        <label for="password"
                               class="transition ease-in-out duration-150">{{__("password-reset.Creeër wachtwoord")}}</label>
                    </div>
                    <div
                            class="input-group w-full mb-4">
                        <input id="password_confirm" wire:model="password_confirmation"
                               type="password"
                               class="form-input ">
                        <label for="password_confirm" class="transition ease-in-out duration-150">
                            {{__("password-reset.Herhaal wachtwoord")}}</label>
                    </div>
                    <div
                            class="flex items-end mid-grey w-1/2 md:w-auto order-2 md:order-3 pl-2 overflow-visible md:overflow-auto requirement-font-size">
                        <div
                                class="text-{{$this->minCharRule}}">@if($this->minCharRule)
                                <x-icon.checkmark-small></x-icon.checkmark-small>
                            @elseif($this->minCharRule === 'red')
                                <x-icon.close-small></x-icon.close-small>
                            @else
                                <x-icon.dot></x-icon.dot>
                            @endif {{__("password-reset.Min. 8 tekens")}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="error-section">
                @error('password')
                <div class="notification error mt-4">
                    <span class="title">{{ $message }}</span>
                </div>
                @enderror
            </div>

            <div class="mt-4">
                <x-button.cta size="sm" class="w-full flex justify-center">
                    <x-icon.checkmark/>
                    <span class="mr-2">{{__("password-reset.Wachtwoord resetten")}}</span>
                </x-button.cta>
                <x-button.cta size="sm" class="w-full flex justify-center">
                    <x-icon.checkmark/>
                    <span class="mr-2">{{__("password-reset.Wachtwoord resetten")}}</span>
                </x-button.cta>
            </div>
        </form>
    </div>

    <x-modal maxWidth="lg" wire:model="showSuccessModal" show-cancel-button="false">
        <x-slot name="title">{{ __('passwords.reset_title') }}</x-slot>
        <x-slot name="body">{{ __('passwords.reset') }}</x-slot>
        <x-slot name="actionButton">
            <x-button.primary size="sm" wire:click="redirectToLogin">
                <span>{{__('passwords.login')}}</span>
                <x-icon.chevron/>
            </x-button.primary>
        </x-slot>
    </x-modal>

</div>
