<div>
    <div class="flex-grow" x-data="{showPassword: false, hoverPassword: false, initialPreviewIconState: true}">
        <form autocomplete="off" class="h-full relative" wire:submit.prevent="resetPassword" action="#" method="POST">
            <div class="input-section">
                <h5 class="w-full text-center leading-6 pb-2.5">{{__('password-reset.Maak een nieuw wachtwoord')}}</h5>
                <div class="mb-4">{{ __('password-reset.Vul jouw nieuwe wachtwoord in') }}</div>
                <div class="email-section mb-4 w-full">
                    <div class="mb-4">
                        <div class="input-group w-full">
                            <input id="username" wire:model.lazy="username"
                                   data-focus-tab="reset_password"
                                   autocomplete="new-password"
                                   class="form-input @error('registration.username') border-red @enderror"
                                   >
                            <label for="username"
                                   class="transition ease-in-out duration-150">{{__("password-reset.E-mail")}}</label>
                        </div>
                    </div>
                </div>
                <div class="password ">
                    <div class="input-group w-full mb-4 relative"
                         >
                        <input id="password" wire:model.lazy="password" x-bind:type="showPassword ? 'text' : 'password'"
                               autocomplete="new-password"
                               class="form-input ">
                        <div @mouseenter="hoverPassword = true"
                             @mouseleave="hoverPassword = false"
                             @click="showPassword = !showPassword; hoverPassword = false; initialPreviewIconState = false">
                            <x-icon.preview-off
                                    class="absolute bottom-3 right-3.5 primary-hover cursor-pointer"
                                    x-bind:class="{'opacity-50' : initialPreviewIconState, 'hover:text-sysbase': (!showPassword && !hoverPassword)}"
                                    x-show="(!showPassword && !hoverPassword) || (showPassword && hoverPassword)"/>
                            <div class="absolute bottom-3 right-3.5 flex items-center h-[16px]">
                                <x-icon.preview class="primary-hover cursor-pointer"
                                                x-bind:class="{'hover:text-sysbase': (showPassword && !hoverPassword)}"
                                                x-show="(showPassword && !hoverPassword) || (!showPassword && hoverPassword)"/>
                            </div>
                        </div>

                        <label for="password"
                               class="transition ease-in-out duration-150">{{__("password-reset.Creeër wachtwoord")}}</label>
                    </div>
                    <div
                            class="input-group w-full mb-4 relative"
                            >
                        <input id="password_confirm" wire:model.lazy="password_confirmation"
                               x-bind:type="showPassword ? 'text' : 'password'"
                               class="form-input ">
                        <div    @click="showPassword = !showPassword; hoverPassword = false; initialPreviewIconState = false"
                                @mouseenter="hoverPassword = true"
                                @mouseleave="hoverPassword = false">
                            <x-icon.preview-off
                                    class="absolute bottom-3 right-3.5 primary-hover cursor-pointer"
                                    x-bind:class="{'opacity-50' : initialPreviewIconState, 'hover:text-sysbase': (!showPassword && !hoverPassword)}"
                                    x-show="(!showPassword && !hoverPassword) || (showPassword && hoverPassword)"/>
                            <div class="absolute bottom-3 right-3.5 flex items-center h-[16px]">
                                <x-icon.preview class="primary-hover cursor-pointer"
                                                x-bind:class="{'hover:text-sysbase': (showPassword && !hoverPassword)}"
                                                x-show="(showPassword && !hoverPassword) || (!showPassword && hoverPassword)"/>
                            </div>
                        </div>

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
                @error('username')
                <div class="notification error mt-4">
                    <span class="title">{{ $message }}</span>
                </div>
                @enderror
                @error('password')
                <div class="notification error mt-4">
                    <span class="title">{{ $message }}</span>
                </div>
                @enderror
            </div>

            <div class="mt-4 flex">
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
