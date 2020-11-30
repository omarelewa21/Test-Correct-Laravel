<div>
    <div class="py-5 bg-white onboarding-header">
        <div class="max-w-2xl mx-auto grid grid-cols-3 gap-y-4 mid-grey">
            <div class="col-span-3">
                <a class="mx-auto tc-logo block" href="/">
                    <img class="" src="/svg/logos/Logo-Test-Correct recolored.svg"
                         alt="Test-Correct">
                </a>
            </div>
            <div class="col-span-3 step-indicator">
                @if($this->step === 1)
                    <div>
                        <div class="inline-block rounded-full header-number mr-2 active">1</div>
                        <span class="mr-6 mt-1 bold active">Jouw docentprofiel</span>
                    </div>
                    <div>
                        <div class="inline-block rounded-full header-number mr-2">2</div>
                        <span class="mr-6 mt-1">Jouw schoolgegevens</span>
                    </div>
                    <div>
                        <div class="inline-block rounded-full header-number mr-2">3</div>
                        <span class=" mt-1">Klaar!</span>
                    </div>
                @endif
                @if($this->step === 2)
                    <div>
                        <img class="inline-block header-check" src="/svg/icons/checkmark-circle.svg" alt="">
                        <span class="mr-6 mt-1 bold active">Jouw docentprofiel</span>
                    </div>
                    <div>
                        <div class="inline-block rounded-full header-number mr-2 active">2</div>
                        <span class="mr-6 mt-1 bold active">Jouw schoolgegevens</span>
                    </div>
                    <div>
                        <div class="inline-block rounded-full header-number mr-2">3</div>
                        <span class=" mt-1">Klaar!</span>
                    </div>
                @endif
                @if($this->step === 3)
                    <div>
                        <img class="inline-block header-check" src="/svg/icons/checkmark-circle.svg" alt="">
                        <span class="mr-6 mt-1 bold active">Jouw docentprofiel</span>
                    </div>
                    <div>
                        <img class="inline-block header-check" src="/svg/icons/checkmark-circle.svg" alt="">
                        <span class="mr-6 mt-1 bold active">Jouw schoolgegevens</span>
                    </div>
                    <div>
                        <img class="inline-block header-check" src="/svg/icons/checkmark-circle.svg" alt="">
                        <span class="bold mt-1 active">Klaar!</span>
                    </div>
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
                <div class="bg-white rounded-10 p-4 sm:p-10 content-section">
                    @if($this->step === 1)
                        <div class="" wire:key="step1">
                            {{--content header--}}
                            <div class="mb-6">
                                <img class="inline-block card-header-img mr-3" src="/svg/stickers/profile.svg" alt="">
                                <h1 class="inline-block align-middle">Vul jouw docentprofiel in</h1>
                            </div>

                            {{--content form--}}
                            <div class="">
                                <form wire:submit.prevent="step1" action="#" method="POST">
                                    <div class="gender-section mb-4">
                                        <div class="inline-block male mr-4">
                                            <label for="gender_male"
                                                   class="block bold">Aanhef</label>
                                            @if($this->registration->gender === 'male')
                                                <button type="button"
                                                        class="relative inline-flex w-full items-center p-4 select-button btn-active">
                                                    <x-icon.gender-man></x-icon.gender-man>
                                                    <x-icon.checkmark-circle></x-icon.checkmark-circle>
                                                    Meneer
                                                </button>
                                            @else
                                                <button wire:click="$set('registration.gender', 'male')" type="button"
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
                                                <button type="button"
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

                                        <div class="inline-block different">
                                            <label for="gender_different"
                                                   class="text-sm font-medium leading-5 text-gray-700">&nbsp;</label>
                                            @if($this->registration->gender === 'different')
                                                <button type="button"
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
                                        <div class="password">
                                            <div class="input-group mr-4 mb-4 sm:mb-0">
                                                <input id="password" wire:model.lazy="password" type="password"
                                                       class="form-input @error('password') border-red @enderror">
                                                <label for="password"
                                                       class="transition ease-in-out duration-150">Creeër
                                                    wachtwoord</label>
                                            </div>

                                            <div class="input-group mr-4 mb-4 sm:mb-0">
                                                <input id="password_confirm" wire:model="password_confirmation"
                                                       type="password"
                                                       class="form-input @error('password') border-red @enderror">
                                                <label for="password_confirm"
                                                       class="transition ease-in-out duration-150">
                                                    Herhaal wachtwoord</label>
                                            </div>
                                            <div class="mid-grey">
                                                <div
                                                        class="text-{{$this->minCharRule}}-700">@if($this->minCharRule === 'green')
                                                        <x-icon.checkmark-small></x-icon.checkmark-small> @elseif($this->minCharRule === 'red')
                                                        <x-icon.close-small></x-icon.close-small> @endif Min. 8
                                                    tekens
                                                </div>
                                                <div
                                                        class="text-{{ $this->minDigitRule  }}-700">@if($this->minDigitRule === 'green')
                                                        <x-icon.checkmark-small></x-icon.checkmark-small> @elseif($this->minDigitRule === 'red')
                                                        <x-icon.close-small></x-icon.close-small> @endif Min. 1
                                                    cijfer
                                                </div>
                                                <div
                                                        class="text-{{ $this->specialCharRule  }}-700">@if($this->specialCharRule === 'green')
                                                        <x-icon.checkmark-small></x-icon.checkmark-small> @elseif($this->specialCharRule === 'red')
                                                        <x-icon.close-small></x-icon.close-small> @endif Min. 1
                                                    speciaal
                                                    teken (bijv. $ of @)
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="error-section">
                                        @error('registration.gender')
                                        <div class="notification error mt-5">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
                                        @error('registration.name_first')
                                        <div class="notification error mt-5">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
                                        @error('registration.name')
                                        <div class="notification error mt-5">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
                                        @error('password')
                                        <div class="notification error mt-5">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
                                    </div>

                                    <div class="sm:text-right mt-14">
                                        <span class="text-break">
{{--                                        {{json_encode($this->registration)}}--}}
                                        </span>
                                        @if ($btnDisabled)
                                            <button
                                                    class="button button-md primary-button transition ease-in-out duration-150 btn-disabled">
                                                <span class="mr-2">Ga naar jouw schoolgegevens</span>
                                                <x-icon.chevron></x-icon.chevron>
                                            </button>
                                        @else
                                            <button
                                                    class="button button-md primary-button transition ease-in-out duration-150">
                                                <span class="mr-2">Ga naar jouw schoolgegevens</span>
                                                <x-icon.chevron></x-icon.chevron>
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    @elseif($this->step === 2)
                        <div class="">
                            {{--content header--}}
                            <div class="mb-6">
                                <img class="card-header-img inline-block mr-3" src="/svg/stickers/school.svg" alt="">
                                <h1 class="inline-block align-middle">Wat zijn jouw schoolgegevens?</h1>
                            </div>

                            <div class="">
                                <form wire:submit.prevent="step2" action="#" method="POST">
                                    <div class="input-section">
                                        <div class="flex flex-nowrap mb-4">
                                            <div class="input-group w-6/12 pr-2">
                                                <input id="school_location"
                                                       wire:model.lazy="registration.school_location"
                                                       class="form-input @error('registration.school_location') border-red @enderror">
                                                <label for="school_location"
                                                       class="">Schoolnaam</label>
                                            </div>

                                            <div class="input-group w-6/12 pl-2">
                                                <input id="website_url" wire:model.lazy="registration.website_url"
                                                       class="form-input @error('registration.website_url') border-red @enderror">
                                                <label for="website_url"
                                                       class="">Website</label>
                                            </div>
                                        </div>
                                        <div class="flex flex-nowrap mb-4">
                                            <div class="input-group w-9/12 sm:w-3/5 mr-4">
                                                <input id="address" wire:model.lazy="registration.address"
                                                       class="form-input @error('registration.address') border-red @enderror">
                                                <label for="address"
                                                       class="">Adres</label>
                                            </div>
                                            <div class="input-group w-3/12 sm:w-32">
                                                <input id="house_number" wire:model.lazy="registration.house_number"
                                                       class="form-input @error('registration.house_number') border-red @enderror">
                                                <label for="house_number"
                                                       class="">Huisnummer</label>
                                            </div>
                                        </div>
                                        <div class="flex flex-nowrap mb-4">
                                            <div class="input-group  w-3/12 sm:w-32 mr-4">
                                                <input id="postcode" wire:model.lazy="registration.postcode"
                                                       class="form-input  @error('registration.postcode') border-red @enderror">
                                                <label for="postcode"
                                                       class="">Postcode</label>
                                            </div>
                                            <div class="input-group w-9/12 sm:w-3/5">
                                                <input id="city" wire:model.lazy="registration.city"
                                                       class="form-input @error('registration.city') border-red @enderror">
                                                <label for="city"
                                                       class="">Plaatsnaam</label>
                                            </div>
                                        </div>
                                        <div class="mb-4">

                                            {{--                                            @foreach($this->getErrorBag()->toArray() as $key => $value)--}}
                                            {{--                                                <div class="notification error mt-4">--}}
                                            {{--                                                    <span class="title">k {{ json_encode($key) }} v {{ json_encode($value) }}</span>--}}
                                            {{--                                                </div>--}}
                                            {{--                                            @endforeach--}}

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
                                        <div class="mb-4 flex flex-wrap justify-between">
                                            <a wire:click="backToStepOne"
                                               class="rotate-svg leading-50 text-button cursor-pointer">
                                                <x-icon.chevron></x-icon.chevron>
                                                <span class="align-middle">Terug naar jouw docentprofiel</span>
                                            </a>
                                            @if ($btnDisabled)
                                                <button
                                                        class="button button-md primary-button btn-disabled">
                                                    <span class="mr-2">Maak mijn Test-Correct account</span>
                                                    <x-icon.chevron></x-icon.chevron>
                                                </button>
                                            @else
                                                <button
                                                        class="button button-md primary-button md:float-right">
                                                    <span class="mr-2">Maak mijn Test-Correct account</span>
                                                    <x-icon.chevron></x-icon.chevron>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @elseif($this->step === 3)
                        <div class="">
                            {{--content header--}}
                            <div class="mb-6">
                                <img class="inline-block card-header-img mr-3" src="/svg/stickers/completed.svg" alt="">
                                <h1 class="inline-block align-middle">Je bent nu klaar! Met Test-Correct kun je...</h1>
                            </div>
                            <div class="">
                                <div class="body1">
                                    <div class="flex flex-wrap mb-4">
                                        <div class="w-6/12">
                                            <img src="/svg/stickers/toetsen-maken-afnemen.svg" alt=""
                                                 class="mr-4 float-left">
                                            <span class="">Toetsen aanmaken en bestaande toetsen omzetten.</span>
                                        </div>
                                        <div class="w-6/12">
                                            <img src="/svg/stickers/toetsen-beoordelen-bespreken.svg" alt=""
                                                 class="mr-4 float-left">
                                            <span class="">Toetsen beoordelen en samen de toets bespreken.</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap mb-4">
                                        <div class="w-6/12">
                                            <img src="/svg/stickers/klassen.svg" alt="" class="mr-4 float-left">
                                            <span>Klassen maken en uitnodigen om een toets af te nemen.</span>
                                        </div>
                                        <div class="w-6/12">

                                            <img src="/svg/stickers/toetsresultaten-analyse.svg" alt=""
                                                 class="mr-4 float-left">
                                            <span>Toetsresultaten delen en analystische feedback inzien.</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap mb-4">
                                        <span class="w-full mb-3">Deel op social media dat je een test-Correct docent account hebt aangemaakt.</span>
                                        <button class="float-left mr-2 button button-sm secondary-button transition">
                                            <img class="w-20"
                                                 src="/svg/logos/Logo-LinkedIn.svg"
                                                 alt=""></button>
                                        <button class="float-left mr-2 button button-sm secondary-button transition">
                                            <img class="w-20"
                                                 src="/svg/logos/Logo-Twitter.svg"
                                                 alt=""></button>
                                        <button class="float-left mr-2 button button-sm secondary-button transition">
                                            <img class="w-20 "
                                                 src="/svg/logos/Logo-Facebook.svg"
                                                 alt=""></button>
                                    </div>

                                    <div class="flex mb-6">
                                        <div class="notification warning">

                                            <span class="title">Verifieer je e-mailadres</span>
                                            <span class="body"></span>
                                        </div>
                                    </div>

                                    <div class="flex sm:justify-end">
                                        <button class=" button button-md cta-button">
                                            <span class="mr-3">Inloggen op Test-Correct</span>
                                            <x-icon.arrow></x-icon.arrow>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endif
                </div>
                <div class="flex justify-center">
                    <div class="inline-block mr-2">
                        <span class="regular">Heb je al een account?</span>
                        <button class="text-button">
                            <span class="bold">Log in</span>
                            <x-icon.arrow></x-icon.arrow>
                        </button>
                    </div>
                    <div class="inline-block ml-2">
                        <span class="regular">Ben je een student?</span>
                        <button class="text-button">
                            <span class="bold">Kijk hier</span>
                            <x-icon.arrow></x-icon.arrow>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
