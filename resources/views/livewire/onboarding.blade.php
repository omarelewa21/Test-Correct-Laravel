<div>
    <div class="py-5 bg-white onboarding-header">
        <div class="max-w-2xl mx-auto grid grid-cols-3 gap-y-4 mid-grey">
            <div class="col-span-3">
                <a class="mx-auto tc-logo block" href="https://test-correct.nl">
                    <img class="" src="/svg/logos/Logo-Test-Correct-recolored.svg"
                         alt="Test-Correct">
                </a>
            </div>
            <div class="col-span-3 step-indicator bold leading-30">
                @if($this->step === 1)
                    <div>
                        <div class="inline-block rounded-full header-number mr-2 active">1</div>
                        <span class="mr-6 mt-1 active">Jouw docentprofiel</span>
                    </div>
                    <div>
                        <div class="inline-block rounded-full header-number mr-2">2</div>
                        <span class="mr-6 mt-1">Jouw schoolgegevens</span>
                    </div>
                    <div>
                        <div class="inline-block rounded-full header-number mr-2">3</div>
                        <span class=" mt-1">Klaar!</span>
                    </div>
                    <iframe id="frame-step1" src="https://www.test-correct.nl/bedankt-aanmelding-docent/" style="height: 1px; width:1px"></iframe>
                @endif
                @if($this->step === 2)
                    <div>
                        <img class="inline-block header-check" src="/svg/icons/checkmark-circle.svg" alt="">
                        <span class="mr-6 mt-1 active">Jouw docentprofiel</span>
                    </div>
                    <div>
                        <div class="inline-block rounded-full header-number mr-2 active">2</div>
                        <span class="mr-6 mt-1 active">Jouw schoolgegevens</span>
                    </div>
                    <div>
                        <div class="inline-block rounded-full header-number mr-2">3</div>
                        <span class=" mt-1">Klaar!</span>
                    </div>
                    <iframe id="frame-step2" src="https://www.test-correct.nl/bedankt-aanmelding-docent/" style="height: 1px; width:1px"></iframe>
                @endif
                @if($this->step === 3)
                    <div>
                        <img class="inline-block header-check" src="/svg/icons/checkmark-circle.svg" alt="">
                        <span class="mr-6 mt-1 active ">Jouw docentprofiel</span>
                    </div>
                    <div>
                        <img class="inline-block header-check" src="/svg/icons/checkmark-circle.svg" alt="">
                        <span class="mr-6 mt-1 active">Jouw schoolgegevens</span>
                    </div>
                    <div>
                        <img class="inline-block header-check" src="/svg/icons/checkmark-circle.svg" alt="">
                        <span class="mt-1 active">Klaar!</span>
                    </div>
                    <iframe id="frame-step3" src="https://www.test-correct.nl/bedankt-aanmelding-docent/" style="height: 1px; width:1px"></iframe>
                @endif
            </div>
        </div>
    </div>
    <div class="onboarding-body">
        <div class="max-w-4xl mx-auto">
            <div class=" base px-4 py-5 sm:p-6">
                <div class="pb-5 col-span-2">
                    <div class="text-center">
                        <h2>Maak een Test-Correct docent account</h2>
                        <h3>Digitaal toetsen dat wél werkt!</h3>
                    </div>
                </div>
                <div class="bg-white rounded-10 p-8 sm:p-10 content-section">
                    @if($this->step === 1)
                        <div class="content-form" wire:key="step1">
                            {{--content header--}}
                            <div class="mb-6 relative">
                                <img class="inline-block card-header-img mr-3" src="/svg/stickers/profile.svg" alt="">
                                <h1 class="card-header-text top-4 mt-2">Vul jouw docentprofiel in</h1>
                            </div>
                            {{--content form--}}
                            <div class="flex-grow">
                                <form class="h-full relative" wire:submit.prevent="step1" action="#" method="POST">
                                    @if($this->shouldDisplayEmail)
                                        <div class="email-section mb-4 w-full md:w-1/2">
                                            <div class="mb-4">
                                                <div class="input-group">
                                                    <input id="username" wire:model.lazy="registration.username"
                                                           class="form-input @error('registration.username') border-red @enderror"
                                                           autofocus>
                                                    <label for="username"
                                                           class="transition ease-in-out duration-150">E-mail</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="gender-section mb-4">
                                        <div class="inline-block male mr-4">
                                            <label for="gender_male"
                                                   class="block">Aanhef</label>
                                            @if($this->registration->gender === 'male')
                                                <button wire:key="registration_male" type="button"
                                                        wire:click="$set('registration.gender', 'male')"
                                                        class="relative inline-flex w-full items-center p-4 select-button btn-active">
                                                    <x-icon.gender-man></x-icon.gender-man>
                                                    <x-icon.checkmark-circle></x-icon.checkmark-circle>
                                                    Meneer
                                                </button>
                                            @else
                                                <button wire:key="registration_male"
                                                        wire:click="$set('registration.gender', 'male')" type="button"
                                                        class="inline-flex w-full items-center p-4 select-button ">
                                                    <x-icon.gender-man></x-icon.gender-man>
                                                    Meneer
                                                </button>
                                            @endif
                                        </div>


                                        <div class="inline-block female mr-4">
                                            <label for="gender_female"
                                                   class="text-sm font-medium leading-5 text-gray-700">&nbsp;</label>
                                            @if($this->registration->gender === 'female')
                                                <button type="button" wire:click="$set('registration.gender', 'female')"
                                                        class="relative inline-flex w-full items-center select-button  btn-active">
                                                    <x-icon.gender-woman></x-icon.gender-woman>
                                                    <x-icon.checkmark-circle></x-icon.checkmark-circle>
                                                    Mevrouw
                                                </button>
                                            @else
                                                <button wire:click="$set('registration.gender', 'female')" type="button"
                                                        class="inline-flex w-full items-center select-button ">
                                                    <x-icon.gender-woman></x-icon.gender-woman>
                                                    Mevrouw
                                                </button>
                                            @endif
                                        </div>

                                        <div class="inline-block lg:flex-grow">
                                            <label for="gender_different"
                                                   class="text-sm font-medium leading-5 text-gray-700">&nbsp;</label>
                                            @if($this->registration->gender === 'different')
                                                <button type="button"
                                                        wire:click="$set('registration.gender', 'different')"
                                                        class="relative inline-flex items-center p-4 w-full select-button  btn-active">
                                                    <div class="inline-block w-full text-left">
                                                        <x-icon.gender-other></x-icon.gender-other>
                                                        <x-icon.checkmark-circle></x-icon.checkmark-circle>
                                                        <div class="inline-block">
                                                            <span>Anders: </span>
                                                            <input id="gender_different"
                                                                   wire:model="registration.gender_different"
                                                                   class="form-input sm:ml-2 mr-0 other-input">
                                                        </div>
                                                    </div>
                                                </button>
                                            @else
                                                <button wire:click="$set('registration.gender', 'different')"
                                                        type="button"
                                                        class="inline-flex items-center p-4 w-full select-button ">
                                                    <div class="inline-block w-full text-left">
                                                        <x-icon.gender-other></x-icon.gender-other>
                                                        <div class="inline-block">
                                                            <span>Anders: </span>
                                                            <input id="gender_different"
                                                                   wire:model="registration.gender_different"
                                                                   disabled
                                                                   class="form-input sm:ml-2 mr-0 other-input">
                                                        </div>
                                                    </div>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="input-section">
                                        <div class="name mb-4">
                                            <div class="input-group mr-4 mb-4 sm:mb-0">
                                                <input id="name_first" wire:model.lazy="registration.name_first"
                                                       class="form-input @error('registration.name_first') border-red @enderror">
                                                <label for="name_first"
                                                       class="transition ease-in-out duration-150">Voornaam</label>
                                            </div>
                                            <div class="input-group mr-4 mb-4 sm:mb-0">
                                                <input id="name_suffix" wire:model.lazy="registration.name_suffix"
                                                       class="form-input @error('registration.name_suffix') border-red @enderror">
                                                <label for="name_suffix"
                                                       class="transition ease-in-out duration-150">Tussenvoegsel</label>
                                            </div>
                                            <div class="input-group lastname">
                                                <input id="name" wire:model.lazy="registration.name"
                                                       class="form-input md:w-full inline-block @error('registration.name') border-red @enderror">
                                                <label for="name"
                                                       class="transition ease-in-out duration-150">Achternaam</label>
                                            </div>
                                        </div>
                                        <div class="password items-start">

                                            <div class="input-group w-1/2 md:w-auto order-1 pr-2 mb-4 md:mb-0">
                                                <input id="password" wire:model="password" type="password"
                                                       class="form-input @error('password') border-red @enderror">
                                                <label for="password"
                                                       class="transition ease-in-out duration-150">Creeër
                                                    wachtwoord</label>
                                            </div>

                                            <div
                                                class="input-group w-1/2 md:w-auto order-3 md:order-2 pr-2 md:pl-2 mb-4 md:mb-0">
                                                <input id="password_confirm" wire:model="password_confirmation"
                                                       type="password"
                                                       class="form-input @error('password') border-red @enderror">
                                                <label for="password_confirm"
                                                       class="transition ease-in-out duration-150">
                                                    Herhaal wachtwoord</label>
                                            </div>

                                            <div
                                                class="mid-grey w-1/2 md:w-auto order-2 md:order-3 pl-2 h-16 overflow-visible md:h-auto md:overflow-auto">
                                                <div
                                                    class="text-{{$this->minCharRule}}">@if($this->minCharRule)
                                                        <x-icon.checkmark-small></x-icon.checkmark-small> @elseif($this->minCharRule === 'red')
                                                        <x-icon.close-small></x-icon.close-small> @else
                                                        <x-icon.dot></x-icon.dot> @endif Min. 8
                                                    tekens
                                                </div>
                                                <div
                                                    class="text-{{$this->minDigitRule}}">@if($this->minDigitRule)
                                                        <x-icon.checkmark-small></x-icon.checkmark-small> @elseif($this->minCharRule === 'red')
                                                        <x-icon.close-small></x-icon.close-small> @else
                                                        <x-icon.dot></x-icon.dot> @endif Min. 1
                                                    cijfer
                                                </div>
                                                <div
                                                    class="text-{{$this->specialCharRule}}">@if($this->specialCharRule)
                                                        <x-icon.checkmark-small></x-icon.checkmark-small> @elseif($this->minCharRule === 'red')
                                                        <x-icon.close-small></x-icon.close-small> @else
                                                        <x-icon.dot></x-icon.dot> @endif Min. 1
                                                    speciaal
                                                    teken (bijv. $ of @)
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="error-section md:mb-20">
                                        @if($this->warningStepOne)
                                            <div class="notification warning mt-4">
                                                <span class="title">Zijn alle velden correct ingevuld?</span>
                                            </div>
                                        @endif
                                        @error('registration.username')
                                        <div class="notification error mt-4">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
                                        @error('registration.gender')
                                        <div class="notification error mt-4">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
                                        @error('registration.name_first')
                                        <div class="notification error mt-4">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
                                        @error('registration.name')
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
                                    <div class="mt-4 md:mt-0 md:absolute md:bottom-0 md:right-0">
                                        @if ($btnDisabled)
                                            <button
                                                class="button button-md primary-button btn-disabled" disabled>
                                                <span class="mr-2">Ga naar jouw schoolgegevens</span>
                                                <x-icon.chevron></x-icon.chevron>
                                            </button>
                                        @else
                                            <button wire:click="step1"
                                                    class="button button-md primary-button">
                                                <span class="mr-2">Ga naar jouw schoolgegevens</span>
                                                <x-icon.chevron></x-icon.chevron>
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    @elseif($this->step === 2)
                        <div class="content-form">
                            {{--content header--}}
                            <div class="mb-6 relative">
                                <img class="card-header-img float-left mr-4" src="/svg/stickers/school.svg" alt="">
                                <h1 class="md:mt-2 top-4 card-header-text">Wat zijn jouw schoolgegevens?</h1>
                            </div>

                            <div class="flex-grow">
                                <form class="h-full relative" wire:submit.prevent="step2" action="#" method="POST">
                                    <div class="input-section mb-4">
                                        <div class="school-info">
                                            <div class="input-group w-full sm:w-1/2 sm:pr-2">
                                                <input id="school_location"
                                                       wire:model.lazy="registration.school_location"
                                                       class="form-input @error('registration.school_location') border-red @enderror">
                                                <label for="school_location"
                                                       class="">Schoolnaam</label>
                                            </div>

                                            <div class="input-group w-full sm:w-1/2 sm:pl-2">
                                                <input id="website_url" wire:model.lazy="registration.website_url"
                                                       class="form-input @error('registration.website_url') border-red @enderror">
                                                <label for="website_url"
                                                       class="">Website</label>
                                            </div>
                                            <div class="input-group w-9/12 sm:w-3/5 pr-2">
                                                <input id="address" wire:model.lazy="registration.address"
                                                       class="form-input @error('registration.address') border-red @enderror">
                                                <label for="address"
                                                       class="">Bezoekadres</label>
                                            </div>
                                            <div class="input-group w-3/12 sm:w-32 pl-2 md:mr-16">
                                                <input id="house_number" wire:model.lazy="registration.house_number"
                                                       class="form-input @error('registration.house_number') border-red @enderror">
                                                <label for="house_number"
                                                       class="">Huisnummer</label>
                                            </div>
                                            <div class="input-group  w-3/12 sm:w-32 pr-2">
                                                <input id="postcode" wire:model.lazy="registration.postcode"
                                                       class="form-input  @error('registration.postcode') border-red @enderror">
                                                <label for="postcode"
                                                       class="">Postcode</label>
                                            </div>
                                            <div class="input-group w-9/12 sm:w-3/5 pl-2">
                                                <input id="city" wire:model="registration.city"
                                                       class="form-input @error('registration.city') border-red @enderror">
                                                <label for="city"
                                                       class="">Plaatsnaam</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-16">
                                        @if($this->warningStepTwo)
                                            <div class="notification warning mt-4">
                                                <span class="title">Zijn alle velden correct ingevuld?</span>
                                            </div>
                                        @endif
                                        @error('registration.school_location')
                                        <div class="notification error mt-4">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
                                        @error('registration.website_url')
                                        <div class="notification error mt-4">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
                                        @error('registration.address')
                                        <div class="notification error mt-4">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
                                        @error('registration.house_number')
                                        <div class="notification error mt-4">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
                                        @error('registration.postcode')
                                        <div class="notification error mt-4">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
                                        @error('registration.city')
                                        <div class="notification error mt-4">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
                                    </div>
                                    <div class="mt-4 w-full  sm:absolute bottom-0">
                                        <a wire:click="backToStepOne"
                                           class="rotate-svg leading-50 text-button cursor-pointer">
                                            <x-icon.chevron></x-icon.chevron>
                                            <span class="align-middle">Terug naar jouw docentprofiel</span>
                                        </a>
                                        @if ($btnDisabled)
                                            <button
                                                class="md:float-right button button-md primary-button btn-disabled"
                                                disabled>
                                                <span class="mr-2">Maak mijn Test-Correct account</span>
                                                <x-icon.chevron></x-icon.chevron>
                                            </button>
                                        @else
                                            <button
                                                class="md:float-right button button-md primary-button md:float-right">
                                                <span class="mr-2">Maak mijn Test-Correct account</span>
                                                <x-icon.chevron></x-icon.chevron>
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    @elseif($this->step === 3)
                        <div class="content-form">
                            {{--content header--}}
                            <div class="mb-6 relative">
                                <img class="inline-block card-header-img mr-3" src="/svg/stickers/completed.svg" alt="">
                                <h1 class="sm:mt-2 top-2.5 card-header-text">Je bent nu klaar! Met Test-Correct kun
                                    je...</h1>
                            </div>
                            <div class="flex-grow">
                                <div class="body1 h-full relative">
                                    <div class="flex flex-wrap">
                                        <div class="w-full sm:w-1/2 sm:pr-2 mb-4 relative">
                                            <img src="/svg/stickers/toetsen-maken-afnemen.svg" alt=""
                                                 class="mr-4 float-left">
                                            <span
                                                class="klaar-text">Toetsen aanmaken en bestaande toetsen omzetten.</span>
                                        </div>
                                        <div class="w-full sm:w-1/2 sm:pl-2 mb-4 relative">
                                            <img src="/svg/stickers/toetsen-beoordelen-bespreken.svg" alt=""
                                                 class="mr-4 float-left">
                                            <span
                                                class="klaar-text">Toetsen beoordelen en samen de toets bespreken.</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap mb-4">
                                        <div class="w-full sm:w-1/2 sm:pr-2 mb-4 relative">
                                            <img src="/svg/stickers/klassen.svg" alt="" class="mr-4 float-left">
                                            <span class="klaar-text">Klassen maken en uitnodigen om een toets af te nemen.</span>
                                        </div>
                                        <div class="w-full sm:w-1/2 sm:pl-2 mb-4 relative">
                                            <img src="/svg/stickers/toetsresultaten-analyse.svg" alt=""
                                                 class="mr-4 float-left">
                                            <span class="klaar-text">Toetsresultaten delen en analystische feedback inzien.</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap mb-4">
                                        <span class="w-full mb-3">Deel op social media dat je een Test-Correct docent account hebt aangemaakt.</span>
                                        <a class="float-left mr-2 button button-sm secondary-button transition"
                                           target="_blank" href="https://www.linkedin.com/company/9225774">
                                            <img class="w-20 mt-2"
                                                 src="/svg/logos/Logo-LinkedIn.svg"
                                                 alt=""></a>
                                        <a class="float-left mr-2 button button-sm secondary-button transition"
                                           target="_blank" href="https://twitter.com/testcorrect">
                                            <img class="w-20 mt-3"
                                                 src="/svg/logos/Logo-Twitter.svg"
                                                 alt=""></a>
                                        <a class="float-left mr-2 button button-sm secondary-button transition"
                                           target="_blank" href="https://www.facebook.com/TestCorrect/">
                                            <img class="w-20 mt-3"
                                                 src="/svg/logos/Logo-Facebook.svg"
                                                 alt=""></a>
                                    </div>

                                    @if($resendVerificationMail)
                                        <div class="notification warning mb-4">
                                            <span
                                                class="title">De verificatie e-mail is opnieuw naar je verzonden.</span>
                                        </div>
                                    @endif
                                    <div class="notification warning stretched mb-4 md:mb-16">
                                        <span class="title">Verifieer je e-mailadres</span>
                                        <span class="body">Open de verificatie mail en klik op 'Verifieer e-mailadres'. Het ontvangen van de e-mail kan enkele minuten duren. Heb je geen mail ontvangen?
                                            <a wire:click="resendEmailVerificationMail" class="bold cursor-pointer">Stuur de verificatiemail opnieuw <x-icon.arrow-small></x-icon.arrow-small></a> of
                                            <a href="https://support.test-correct.nl/knowledge" class="bold">zoek ondersteuning <x-icon.arrow-small></x-icon.arrow-small></a></span>
                                    </div>
                                    <div class="md:absolute bottom-0 sm:right-0">
                                        <button class=" button button-md cta-button" wire:click="loginUser">
                                            <span class="mr-3">Inloggen op Test-Correct</span>
                                            <x-icon.arrow></x-icon.arrow>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @elseif($this->step === 'error')
                        <div class="content-form">
                            {{--content header--}}
                            <div class="mb-4 relative">
                                <h1 class="">Er is helaas iets fout gegaan...</h1>
                            </div>
                            <div class="flex-grow">
                                <div class="body1 h-full relative">
                                    <div class="notification error stretched">
                                        <span class="title">Neem contact op met de helpdesk voor <a
                                                href="https://support.test-correct.nl/knowledge" class="bold">ondersteuning <x-icon.arrow-small></x-icon.arrow-small></a></span></span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endif
                </div>
                <div class="sm:flex text-center justify-center pt-4">
                    <div class="w-full sm:w-auto sm:pr-2">
                        <span class="regular">Heb je al een account?</span>
                        <a class="text-button"
                           href="{{config('app.url_login')}}">
                            <span class="bold">Log in</span>
                            <x-icon.arrow></x-icon.arrow>
                        </a>
                    </div>
                    <div class="w-full sm:w-auto sm:pl-2 mt-2 sm:mt-0">
                        <span class="regular">Ben je een student?</span>
                        <a class="text-button" href="https://test-correct.nl/downloads">
                            <span class="bold">Kijk hier</span>
                            <x-icon.arrow></x-icon.arrow>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
