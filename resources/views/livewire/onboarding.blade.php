<div>
    <div class="flex w-full items-center justify-center pt-7">
        <a class="flex  w-36 md:w-44" href="https://test-correct.nl">
            <img class="" src="{{ asset('svg/logos/Logo-Test-Correct-2.svg') }}" alt="Test-Correct">
        </a>
    </div>

    <div class="pt-12"
         x-data="{step: @entangle('step'), validationErrors: '', setFocusOnError: @entangle('setFocusOnError')}"
         x-init="
                setTimeout(() => document.querySelector('#username')?.focus(), 250);

                Livewire.hook('message.processed', (message, component) => {
                    validationErrors = Object.keys(component.serverMemo.errors);
                    if(setFocusOnError) {
                        setCurrentFocusInput();
                    }
                })

                function setCurrentFocusInput (){
                    if(validationErrors == '') {
                        return;
                    }

                    selector = (validationErrors != '') ? `[data-validation-error='${step}-${validationErrors[0]}']` : '#username';

                    setTimeout(() => document.querySelector(selector)?.focus(), 250);
                }
            "
         x-cloak>
        <div class="">
            <div class="relative px-3 sm:px-10">
                <div class="absolute -top-10 left-1/2 -translate-x-1/2">
                    <x-stickers.aanmelden-zonder-entree/>
                </div>
                <div class="flex flex-col bg-white rounded-10  content-section max-w-xl mx-auto">
                    {{--content header--}}
                    <div class="flex flex-col justify-center pt-10">
                        <div class="flex justify-center relative px-5 mb-4">
                            <h3 class="bold text-xl md:text-[28px]"> {{ __('onboarding.Docent account maken') }}</h3>
                        </div>

                        <div class="entree-step-indicator flex justify-center items-center px-5 sm:px-10 space-x-4 sm:space-x-6 border-b border-secondary">
                            <div class="flex space-x-2 pb-2 border-b-3 border-transparent @if($this->step == 1) border-primary active @endif items-center">
                                @if($this->step == 1)
                                    <div class="flex rounded-full header-number text-white items-center justify-center bold active">
                                        <span>1</span>
                                    </div>
                                @else
                                    <div class="bg-primary rounded-full header-check text-white flex items-center justify-center">
                                        <x-icon.checkmark/>
                                    </div>
                                @endif
                                <span class="text-lg bold @if($this->step == 1) active @endif @if($this->step > 1) primary @endif">{{ __("onboarding.Docentprofiel") }}</span>
                            </div>

                            <div class="flex space-x-2 pb-2 border-b-3 border-transparent @if($this->step == 2) border-primary active @endif items-center">
                                @if($this->step >= 3)
                                    <div class="bg-primary rounded-full header-check text-white flex items-center justify-center">
                                        <x-icon.checkmark/>
                                    </div>
                                @else
                                    <div class="flex rounded-full header-number text-white items-center justify-center bold @if($this->step == 2) active @endif">
                                        <span>2</span>
                                    </div>
                                @endif
                                <span class="text-lg bold text-midgrey @if($this->step == 2) active @endif @if($this->step > 2) primary @endif">{{ __("onboarding.Schoolgegevens") }}</span>
                            </div>

                            <div class="flex space-x-2 pb-2 border-b-3 border-transparent @if($this->step >= 3) border-primary active @endif items-center">
                                @if($this->step > 3)
                                    <div class="bg-primary rounded-full header-check text-white flex items-center justify-center">
                                        <x-icon.checkmark/>
                                    </div>
                                @else
                                    <div class="flex rounded-full header-number text-white items-center justify-center bold @if($this->step == 3) active @endif">
                                        <span>3</span>
                                    </div>
                                @endif
                                <span class="text-lg bold text-midgrey @if($this->step >= 3) active @endif">{{ __("onboarding.Klaar") }}!</span>
                            </div>
                        </div>
                    </div>
                    @if($this->entree_message)
                        <div class="px-5 pt-5 sm:px-10">
                            <div class="notification error stretched mb-4">
                                <div class="flex items-center space-x-3">
                                    <x-icon.exclamation/>
                                    <span class="title">{{ __('auth.entree_error') }}</span>
                                </div>
                                <span class="body">{{ __($this->entree_message) }}</span>
                            </div>
                        </div>
                    @endif

                    @if($this->step === 1)
                        <div class="content-form p-5 sm:p-10" wire:key="step1">
                            {{--content form--}}
                            <div class="flex-grow">
                                <form class="h-full relative" wire:submit.prevent="step1" action="#" method="POST">
                                    <div class="email-section mb-4 w-full">
                                        <div class="mb-4">
                                            <div class="input-group">
                                                <input id="username" wire:model.lazy="registration.username" data-focus-tab="1"
                                                       data-validation-error='1-registration.username'
                                                       class="form-input @error('registration.username') border-red @enderror"
                                                       >
                                                <label for="username"
                                                       class="transition ease-in-out duration-150">{{ __("onboarding.your_school_email") }}</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="gender-section mb-1.5 flex-wrap"
                                         x-data="{gender: @entangle('registration.gender')}">
                                        <div class="flex space-x-2 items-center flex-1 mb-2.5 hover:text-primary transition cursor-pointer"
                                             @click="gender = 'male'"
                                             :class="gender === 'male' ? 'primary bold' : 'text-midgrey'"
                                             style="min-width: 100px;"
                                        >
                                            <div class="flex">
                                                <x-icon.man class="text-inherit"/>
                                            </div>
                                            <span class="flex">Dhr.</span>

                                        </div>
                                        <div class="flex space-x-2 items-center flex-1 mb-2.5 hover:text-primary transition cursor-pointer"
                                             @click="gender = 'female'"
                                             :class="gender === 'female' ? 'primary bold' : 'text-midgrey'"
                                             style="min-width: 100px;"
                                        >
                                            <div class="flex">
                                                <x-icon.woman class="text-inherit"/>
                                            </div>
                                            <span class="flex">Mevr.</span>
                                        </div>
                                        <div class="flex space-x-2 items-center flex-1 mb-2.5 hover:text-primary transition cursor-pointer"
                                             @click="gender = 'different'; $nextTick(() => $root.querySelector('input').focus())"
                                             :class="gender === 'different' ? 'primary bold' : 'text-midgrey'"
                                        >
                                            <div class="flex">
                                                <x-icon.other class="text-inherit"/>
                                            </div>
                                            <label for="gender_different"
                                                   class="flex"
                                            >
                                                Anders:
                                            </label>
                                            <input id="gender_different"
                                                   wire:model.lazy="registration.gender_different"
                                                   data-validation-error='1-registration.gender_different'
                                                   class="form-input other-input flex flex-1 w-full"
                                                   style="min-width: 130px;"
                                                   :disabled="gender !== 'different'"
                                                   :class="gender !== 'different' ? 'disabled' : ''"
                                            >
                                        </div>

                                    </div>

                                    <div class="input-section">
                                        <div class="name mb-4 space-y-4 md:space-y-0">
                                            <div class="input-group flex w-full md:w-auto mr-0 md:mr-4">
                                                <input id="name_first" wire:model.lazy="registration.name_first"
                                                       data-validation-error='1-registration.name_first'
                                                       class="form-input @error('registration.name_first') border-red @enderror">
                                                <label for="name_first"
                                                       class="transition ease-in-out duration-150">{{ __("onboarding.Voornaam") }}</label>
                                            </div>
                                            <div class="input-group flex mr-4">
                                                <input id="name_suffix" wire:model.lazy="registration.name_suffix"
                                                       data-validation-error='1-registration.name_suffix'
                                                       class="form-input @error('registration.name_suffix') border-red @enderror">
                                                <label for="name_suffix"
                                                       class="transition ease-in-out duration-150">{{ __("onboarding.Tussenv.") }}</label>
                                            </div>
                                            <div class="input-group flex flex-1">
                                                <input id="name" wire:model.lazy="registration.name"
                                                       data-validation-error='1-registration.name'
                                                       class="form-input md:w-full inline-block @error('registration.name') border-red @enderror">
                                                <label for="name"
                                                       class="transition ease-in-out duration-150">{{ __("onboarding.Achternaam") }}</label>
                                            </div>
                                        </div>

                                        <div class="password md:space-x-4">
                                            <div class="input-group relative md:flex-1 w-full mb-4 md:mb-0"
                                                 x-data="{password: '', showPassword: false}">
                                                <div class="flex items-center"
                                                     :class="password.length >= 8 ? 'text-cta' : 'text-midgrey'">
                                                    <span class="mr-2" x-show="password.length >= 8" x-cloak><x-icon.checkmark-small/></span>
                                                    <span class="text-sm mt-1">Min. 8 {{ __("onboarding.tekens") }}</span>
                                                </div>
                                                <input id="password"
                                                       wire:model.lazy="password"
                                                       class="form-input @error('password') border-red @enderror"
                                                       :type="showPassword ? 'text' : 'password'"
                                                       data-validation-error='1-password'
                                                       x-model="password">
                                                <label for="password"
                                                       class="transition ease-in-out duration-150">{{ __("onboarding.Creeër wachtwoord") }}</label>
                                                <x-icon.preview
                                                        class="absolute top-[37px] right-3.5 primary-hover cursor-pointer"
                                                        @click="showPassword = !showPassword"/>
                                            </div>

                                            <div class="input-group relative md:flex-1 w-full mb-4 md:mb-0"
                                                 x-data="{showPassword: false}">
                                                <input id="password_confirm"
                                                       wire:model.lazy="password_confirmation"
                                                       :type="showPassword ? 'type' : 'password'"
                                                       class="form-input @error('password') border-red @enderror"
                                                >
                                                <label for="password_confirm"
                                                       class="transition ease-in-out duration-150">
                                                    {{ __("onboarding.Herhaal wachtwoord") }}</label>
                                                <x-icon.preview
                                                        class="absolute top-[37px] right-3.5 primary-hover cursor-pointer"
                                                        @click="showPassword = !showPassword"/>
                                            </div>
                                        </div>

                                        @if($this->hasNoSubjects())

                                        @elseif($this->useDomainInsteadOfSubjects())
                                            <div class="mt-4">
                                                <div class="input-group ">
                                                    <input id="domain" wire:model.lazy="domain" type="text"
                                                           data-validation-error='1-domain'
                                                           class="form-input @error('domain') border-red @enderror">
                                                    <label for="domain"
                                                           class="transition ease-in-out duration-150">{{ __("onboarding.Jouw domein(en)") }}</label>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex flex-col mt-4">
                                                <label for="subjects" id="subjects_label"
                                                       class="transition ease-in-out duration-150">{{__('onboarding.Jouw vak(ken)')}}</label>
                                                <div class="flex">

                                                    <x-input.choices-select
                                                            :multiple="true"
                                                            :options="$this->subjects"
                                                            :withSearch="true"
                                                            placeholderText="{{ __('onboarding.Selecteer vak....') }}"
                                                            wire:model="selectedSubjects"
                                                            filterContainer="onboarding-subjects"
                                                            wire:key="onboarding-subjects"
                                                    />
                                                </div>
                                                <div id="onboarding-subjects"
                                                     wire:ignore
                                                     class="flex flex-wrap gap-2 mt-2 relative"
                                                >
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="error-section md:mb-20">
                                        @if($this->warningStepOne)
                                            <div class="notification stretched warning mt-4" selid="warningStepOne">
                                                <span class="title">{{ __("onboarding.Zijn alle velden correct ingevuld") }}?</span>
                                            </div>
                                        @endif
                                        @foreach($errors->getMessageBag()->toArray() as $key => $error)
                                            <div class="notification stretched error mt-4" selid="error.{{str_replace('registration.', '', $key)}}">
                                                <span class="title">{{ $error[0] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="flex w-full mt-4">
                                        @if ($btnDisabled)
                                            <button
                                                    class="flex ml-auto items-center button button-md primary-button btn-disabled"
                                                    disabled>
                                                <span class="mr-2">{{ __("cms.Volgende") }}</span>
                                                <x-icon.chevron></x-icon.chevron>
                                            </button>
                                        @else
                                            <button
                                                    class="flex ml-auto items-center button button-md primary-button">
                                                <span class="mr-2">{{ __("cms.Volgende") }}</span>
                                                <x-icon.chevron></x-icon.chevron>
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    @elseif($this->step === 2)
                        <div class="content-form relative p-5 md:p-10"
                             wire:key="step2"
                             x-data="{}"
                             x-init="
                             document.querySelector('#school_location').focus();
                             "
                        >
                            {{--content header--}}
                            <div class="input-section">
                                <div class="school-info">
                                    <div class="input-group w-full">
                                        <input id="school_location"
                                               data-focus-tab="2"
                                               wire:model.lazy="registration.school_location"
                                               data-validation-error='2-registration.school_location'
                                               class="form-input @error('registration.school_location') border-red @enderror">
                                        <label for="school_location"
                                               class="">{{ __("onboarding.Schoolnaam") }}</label>
                                    </div>
{{--                                    <div class="input-group flex-0 w-full sm:w-auto sm:flex-1 sm:mr-4">--}}
{{--                                        <input id="location_name"--}}
{{--                                               wire:model.lazy="registration.location_name"--}}
{{--                                               class="form-input @error('registration.location_name') border-red @enderror">--}}
{{--                                        <label for="location_name"--}}
{{--                                               class="">{{ __("onboarding.Locatie") }}</label>--}}
{{--                                    </div>--}}
                                    <div class="input-group flex-1">
                                        <input id="website_url"
                                               wire:model.lazy="registration.website_url"
                                               data-validation-error='2-registration.website_url'
                                               class="form-input @error('registration.website_url') border-red @enderror">
                                        <label for="website_url"
                                               class="">{{ __("onboarding.Website") }}</label>
                                    </div>
                                    <div class="flex w-full">
                                        <div class="input-group flex-1 mr-4">
                                            <input id="address"
                                                   wire:model.lazy="registration.address"
                                                   data-validation-error='2-registration.address'
                                                   class="form-input @error('registration.address') border-red @enderror">
                                            <label for="address"
                                                   class="">{{ __("onboarding.Bezoekadres") }}</label>
                                        </div>
                                        <div class="input-group w-28">
                                            <input id="house_number"
                                                   wire:model.lazy="registration.house_number"
                                                   data-validation-error='2-registration.house_number'
                                                   class="form-input  @error('registration.house_number') border-red @enderror">
                                            <label for="house_number"
                                                   class="">{{ __("onboarding.Huisnummer") }}</label>
                                        </div>
                                    </div>

                                    <div class="flex w-full">
                                        <div class="input-group w-28 mr-4">
                                            <input id="postcode"
                                                   wire:model.lazy="registration.postcode"
                                                   data-validation-error='2-registration.postcode'
                                                   class="form-input  @error('registration.postcode') border-red @enderror">
                                            <label for="postcode"
                                                   class="">{{ __("onboarding.Postcode") }}</label>
                                        </div>
                                        <div class="input-group flex-1">
                                            <input id="city"
                                                   wire:model.lazy="registration.city"
                                                   data-validation-error='2-registration.city'
                                                   class="form-input @error('registration.city') border-red @enderror">
                                            <label for="city"
                                                   class="">{{ __("onboarding.Plaatsnaam") }}</label>
                                        </div>
                                    </div>

                                </div>
                                <div>
                                    <p class="text-sm leading-6">
                                        {{ __('onboarding.general_terms_text_pt_1') }} <a
                                                class="underline primary-hover"
                                                href="https://www.test-correct.nl/algemene-voorwaarden"
                                                target="_blank">{{ __('onboarding.general_terms') }}</a> {{ __('onboarding.general_terms_text_pt_2') }}
                                    </p>
                                </div>
                                <div class="">
                                    @if($this->warningStepTwo)
                                        <div class="notification stretched warning mt-4" selid="warningStepTwo">
                                            <span class="title">{{ __("onboarding.Zijn alle velden correct ingevuld") }}?</span>
                                        </div>
                                    @endif
                                    @foreach($errors->getMessageBag()->toArray() as $key => $error)
                                        <div class="notification stretched error mt-4" selid="error.{{str_replace('registration.', '', $key)}}">
                                            <span class="title">{{ $error[0] }}</span>
                                        </div>
                                    @endforeach

                                </div>
                                <div class="mt-4 flex justify-between items-center">
                                    <x-button.text wire:click="backToStepOne">
                                        <x-icon.chevron class="z-0 rotate-180"/>
                                        <span>{{ __('modal.Terug') }}</span>
                                    </x-button.text>
                                    @if ($btnDisabled)
                                        <x-button.primary size="md" class="btn-disabled" disabled>
                                            <span>{{ __('cms.Volgende') }}</span>
                                            <x-icon.chevron/>
                                        </x-button.primary>
                                    @else
                                        <x-button.primary size="md" wire:click="step2"
                                                      onClick="this.setAttribute('disabled',true);">
                                            <span>{{ __('cms.Volgende') }}</span>
                                            <x-icon.chevron/>
                                        </x-button.primary>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @elseif($this->step === 3 || $this->step === 4)
                        <div class="content-form p-5 md:p-10">
                            {{--content header--}}
                            <div class="flex space-x-2.5">
                                <div>
                                    <x-stickers.congratulations2 class="flex"/>
                                </div>
                                <div class="mt-2">
                                    <h6 class="text-lg md:text-xl">{{ __('onboarding.Gefeliciteerd met je Test-Correct account!') }}</h6>
                                    @if($this->step === 3)
                                        <h7 class="text-base md:text-lg"
                                            x-data="{}"
                                            x-init="setTimeout(() => {$wire.finish() },2000);">{{ __("onboarding.Je gegevens worden nu verwerkt...") }}</h7>
                                    @else
                                        <span class="flex space-x-2.5 items-center">
                                            <x-icon.checkmark/>
                                            <h7 class="text-base md:text-lg">{{ __("onboarding.Je gegevens zijn verwerkt") }}.</h7>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col mt-5 justify-center">
                                <div class="mb-2 text-lg text-center">
                                    <span>{{ __('onboarding.Deel dit op jouw social media') }}:</span>
                                </div>

                                <div class="flex w-full mb-4 space-x-4">
                                    <a class="button button-sm primary-button flex-1 justify-center !px-2 sm:px-5"
                                       target="_blank" href="https://www.linkedin.com/company/9225774">
                                        <x-logos.linkedin/>
                                    </a>
                                    <a class="button button-sm primary-button flex-1 justify-center !px-2 sm:px-5"
                                       target="_blank" href="https://twitter.com/testcorrect">
                                        <span class="flex h-4">
                                            <x-logos.twitter class="w-full h-auto"/>
                                        </span>
                                    </a>
                                    <a class="button button-sm primary-button flex-1 justify-center !px-2 sm:px-5"
                                       target="_blank" href="https://www.facebook.com/TestCorrect/">
                                        <x-logos.facebook/>
                                    </a>
                                </div>
                            </div>

                                @if($resendVerificationMail)
                                    <div class="notification info mb-4">
                                        <span class="title">{{ __("onboarding.De verificatie e-mail is opnieuw naar je verzonden") }}.</span>
                                    </div>
                                @endif
                                <div class="notification warning stretched mb-4 md:mb-16">
                                    <span class="title">{{ __("onboarding.Verifieer je e-mailadres") }}</span>
                                    <span class="body">{{ __("onboarding.Open de verificatie mail en klik op 'Verifieer e-mailadres'. Het ontvangen van de e-mail kan enkele minuten duren. Heb je geen mail ontvangen?") }}
                                        <a wire:click="resendEmailVerificationMail"
                                           class="bold cursor-pointer">
                                            {{ __("onboarding.Stuur de verificatiemail opnieuw") }}
                                            <x-icon.arrow-small></x-icon.arrow-small>
                                        </a>
                                        {{ __("onboarding.of") }}
                                        <a href="https://support.test-correct.nl/knowledge"
                                           class="bold"
                                           target="_blank">
                                            {{ __("onboarding.zoek ondersteuning") }}
                                            <x-icon.arrow-small></x-icon.arrow-small>
                                        </a>
                                    </span>
                                </div>

                            <div class="flex mt-auto w-full">
                                <x-button.text class="disabled rotate-svg-180" disabled>
                                    <x-icon.chevron/>
                                    <span>{{ __('modal.Terug') }}</span>
                                </x-button.text>
                                <x-button.cta size="md" class="ml-auto" wire:click="loginUser">
                                    <span class="">{{ __('auth.log_in_verb') }}</span>
                                    <x-icon.arrow></x-icon.arrow>
                                </x-button.cta>
                            </div>

                        </div>
                    @elseif($this->step === 'error')
                        <div class="content-form">
                            {{--content header--}}
                            <div class="mb-4 relative">
                                <h1> {{ __("onboarding.Er is helaas iets fout gegaan") }}...</h1>
                            </div>
                            <div class="flex-grow">
                                <div class="body1 h-full relative">
                                    <div class="notification error stretched">
                                        <span class="title">{{ __("onboarding.Neem contact op met de helpdesk voor") }} <a
                                                    href="https://support.test-correct.nl/knowledge" class="bold"> {{__("onboarding.ondersteuning")}} <x-icon.arrow-small></x-icon.arrow-small></a></span></span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endif
                </div>

                <div class="sm:flex text-center justify-center pt-4">
                    <div class="w-full sm:w-auto sm:pr-2">
                        <span class="regular">{{ __("onboarding.Heb je al een account") }}?</span>
                        <a class="text-button"
                           href="{{\tcCore\Http\Helpers\BaseHelper::getLoginUrl()}}">
                            <span class="bold">{{ __("onboarding.Log in") }}</span>
                            <x-icon.arrow></x-icon.arrow>
                        </a>
                    </div>
                    <div class="w-full sm:w-auto sm:pl-2 mt-2 sm:mt-0">
                        <span class="regular">{{ __("onboarding.Ben je een student") }}?</span>
                        <a class="text-button" href="https://test-correct.nl/student">
                            <span class="bold">{{ __("onboarding.Kijk hier") }}</span>
                            <x-icon.arrow></x-icon.arrow>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>