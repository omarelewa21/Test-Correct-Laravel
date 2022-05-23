<div>
    <div class="py-5 bg-white onboarding-header">
        <div class="max-w-2xl mx-auto grid grid-cols-3 gap-y-4 mid-grey">
            <div class="col-span-3">
                <a class="mx-auto tc-logo block" href="https://test-correct.nl">
                    <img class="" src="/svg/logos/Logo-Test-Correct-recolored.svg" alt="Test-Correct">
                </a>
            </div>

        </div>
    </div>

    <div class="onboarding-body">
        <div class="max-w-4xl mx-auto">
            <div class=" base px-4 py-5 sm:p-6">
                <div class="pb-5 col-span-2">
                    <div class="text-center">
                        <h2>{{__("password-reset.Maak een nieuw wachtwoord")}}</h2>
                        <h3>{{__("password-reset.Digitaal toetsen dat wél werkt!")}}</h3>
                    </div>
                </div>
                <div class="bg-white rounded-10 p-8 sm:p-10 ">
                    <div >

                        <div class="mb-6 relative">
                            <img class="inline-block card-header-img mr-3" src="/svg/stickers/profile.svg" alt="">
                            <h1 class="card-header-text top-4 mt-2">{{__("password-reset.Vul jouw nieuwe wachtwoord in")}}</h1>
                        </div>

                        <div class="flex-grow">
                            <form autocomplete="off" class="h-full relative" wire:submit.prevent="resetPassword" action="#" method="POST">
                                <div class="input-section">
                                    <div class="email-section mb-4 w-full md:w-1/2">
                                        <div class="mb-4">
                                            <div class="input-group">
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
                                        <div class="input-group w-1/2 md:w-auto order-1 pr-2 mb-4 md:mb-0">
                                            <input id="password" wire:model="password" type="password"
                                                   autocomplete="new-password"
                                                   class="form-input ">
                                            <label for="password" class="transition ease-in-out duration-150">{{__("password-reset.Creeër wachtwoord")}}</label>
                                        </div>
                                        <div
                                            class="input-group w-1/2 md:w-auto order-3 md:order-2 pr-2 md:pl-2 mb-4 md:mb-0">
                                            <input id="password_confirm" wire:model="password_confirmation"
                                                   type="password"
                                                   class="form-input ">
                                            <label for="password_confirm" class="transition ease-in-out duration-150">
                                                {{__("password-reset.Herhaal wachtwoord")}}</label>
                                        </div>

                                        <div
                                            class="flex items-end mid-grey w-1/2 md:w-auto order-2 md:order-3 pl-2 h-16 overflow-visible md:h-auto md:overflow-auto requirement-font-size">
                                            <div
                                                class="text-{{$this->minCharRule}}">@if($this->minCharRule)
                                                    <x-icon.checkmark-small></x-icon.checkmark-small> @elseif($this->minCharRule === 'red')
                                                    <x-icon.close-small></x-icon.close-small> @else
                                                    <x-icon.dot></x-icon.dot> @endif {{__("password-reset.Min. 8 tekens")}}
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
                                        <button
                                            class="flex ml-auto items-center button button-md primary-button">
                                            <span class="mr-2">{{__("password-reset.Wachtwoord resetten")}}</span>
                                            <x-icon.chevron></x-icon.chevron>
                                        </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="sm:flex text-center justify-center pt-4">
                    <div class="w-full sm:w-auto sm:pr-2">
                        <span class="regular">{{__("password-reset.Heb je al een account?")}}</span>
                        <a class="text-button" href="{{ \tcCore\Http\Helpers\BaseHelper::getLoginUrl() }}">
                            <span class="bold">{{__("password-reset.Log in")}}</span>
                            <svg class="inline-block" width="14" height="13" xmlns="http://www.w3.org/2000/svg">
                                <g class="stroke-current" fill="none" fill-rule="evenodd" stroke-linecap="round"
                                   stroke-width="3">
                                    <path d="M1.5 6.5h10M6.5 1.5l5 5-5 5"></path>
                                </g>
                            </svg>

                        </a>
                    </div>
                    <div class="w-full sm:w-auto sm:pl-2 mt-2 sm:mt-0">
                        <span class="regular">{{__("password-reset.Ben je een student?")}}</span>
                        <a class="text-button" href="https://test-correct.nl/student">
                            <span class="bold">{{__("password-reset.Kijk hier")}}</span>
                            <svg class="inline-block" width="14" height="13" xmlns="http://www.w3.org/2000/svg">
                                <g class="stroke-current" fill="none" fill-rule="evenodd" stroke-linecap="round"
                                   stroke-width="3">
                                    <path d="M1.5 6.5h10M6.5 1.5l5 5-5 5"></path>
                                </g>
                            </svg>

                        </a>
                    </div>
                </div>
            </div>
        </div>
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
