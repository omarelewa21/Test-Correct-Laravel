<div class="py-5 bg-white onboarding-header">
    <div class="max-w-2xl mx-auto grid grid-cols-3 gap-4 mid-grey">
        <div class="col-span-3">
            <img class="mx-auto tc-logo" src="/svg/logos/Logo-Test-Correct recolored.svg" alt="Test-Correct">
        </div>
        <div class="text-center md:text-right col-span-3 sm:col-span-1 {{ $this->step === 1 ? "active" : "" }}">
            @if($this->step === 2)
                <div class="step-finished inline-block align-middle mr-2"></div>
            @else
                <div class="inline-block rounded-full header-number mr-2">1</div>
            @endif
            <span class="inline-block align-middle">Jouw docentprofiel</span>
        </div>
        <div class="text-center col-span-3 sm:col-span-1 {{ $this->step === 2 ? "active" : "" }}">
            @if($this->step === 3)
                <div class="step-finished inline-block align-middle mr-2"></div>
            @else
                <div class="inline-block rounded-full header-number mr-2">2</div>
            @endif
            <span class="inline-block align-middle">Jouw schoolgegevens</span>
        </div>
        <div class="text-center md:text-left col-span-3 sm:col-span-1{{ $this->step === 3 ? "active" : "" }}">
            <div class="inline-block rounded-full header-number mr-2">3</div>
            <span>Klaar!</span>
        </div>
    </div>
</div>
<div class="onboarding-body">
    <div class="max-w-4xl mx-auto">
        <div class="grid grid-cols-2 base px-4 py-5 sm:p-6">
            <div class="pb-5 col-span-2">
                <div class="text-center">
                    <h2>Maak een Test-Correct docent account</h2>
                    <h3>Digitaal toetsen dat wél werkt</h3>
                </div>
            </div>
            <div class="col-span-2 bg-white rounded-10 p-4 sm:p-10 content-section">
                @if($this->step === 2)
                    <div class="grid grid-cols-12">
                        {{--content header--}}
                        <div class="col-span-3 sm:col-span-2 md:col-span-1">
                            <img class="card-header-img" src="/svg/stickers/profile.svg" alt="">
                        </div>
                        <div class="col-span-9 sm:col-span-10 md:col-span-11 sm:mb-5">
                            <h1 class="mt-2.5 ml-2.5">Vul jouw docentprofiel in</h1>
                        </div>

                        {{--content form--}}
                        <div class="col-span-12">
                            <form wire:submit.prevent="step1" action="#" method="POST">
                                <div class="grid sm:grid-cols-12 lg:grid-cols-9 md:grid-cols-12 gap-3 gap-y-5 flex content-start">
                                    <div class="col-span-12 md:col-span-6 lg:col-span-2">
                                        <label for="gender_male"
                                               class="block text-sm font-medium leading-5">Aanhef</label>
                                        @if($this->registration->gender === 'male')
                                            <button type="button"
                                                    class="inline-flex w-full items-center p-4 select-button transition duration-150 ease-in-out">
                                                <div class="btn-img man"></div>
                                                Meneer
                                            </button>
                                        @else
                                            <button wire:click="$set('registration.gender', 'male')" type="button"
                                                    class="inline-flex w-full items-center p-4 select-button transition duration-150 ease-in-out">
                                                <div class="btn-img man"></div>
                                                Meneer
                                            </button>
                                        @endif
                                        @error('registration.gender')
                                        <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                        @enderror
                                    </div>


                                    <div class="col-span-12 md:col-span-6 lg:col-span-2">
                                        <label for="gender_female"
                                               class="hidden md:block text-sm font-medium leading-5 text-gray-700">&nbsp;</label>
                                        @if($this->registration->gender === 'female')
                                            <button type="button"
                                                    class="inline-flex w-full items-center select-button transition duration-150 ease-in-out">
                                                <div class="btn-img woman"></div>
                                                Mevrouw
                                            </button>
                                        @else
                                            <button wire:click="$set('registration.gender', 'female')" type="button"
                                                    class="inline-flex w-full items-center select-button transition duration-150 ease-in-out">
                                                <div class="btn-img woman"></div>

                                                Mevrouw
                                            </button>
                                        @endif
                                    </div>

                                    <div class="col-span-12 sm:col-span-12 lg:col-span-5">
                                        <label for="gender_different"
                                               class="text-sm font-medium leading-5 text-gray-700 hidden lg:block">&nbsp;</label>
                                        @if($this->registration->gender === 'different')
                                            <button type="button"
                                                    class="inline-flex items-center p-4 w-full select-button transition duration-150 ease-in-out">
                                                <div class="btn-img other"></div>
                                                Anders:
                                                <input id="gender_different" wire:model="registration.gender_different"
                                                       class="form-input"
                                                       disabled>
                                            </button>
                                        @else
                                            <button wire:click="$set('registration.gender', 'different')"
                                                    type="button"
                                                    class="inline-flex items-center p-4 w-full select-button transition duration-150 ease-in-out">
                                                <div class="btn-img other"></div>
                                                <div class="w-full text-left"><span>Anders: </span>
                                                    <input id="gender_different"
                                                           wire:model="registration.gender_different"
                                                           class="form-input sm:ml-2 mr-0 w-9/12">
                                                </div>
                                            </button>
                                        @endif
                                    </div>
                                    <div class="col-span-12 sm:col-span-6 lg:col-span-3 input-group">
                                        <input id="name_first" wire:model="registration.name_first"
                                               class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 border-solid border-2">
                                        <label for="name_first"
                                               class="block text-sm font-medium leading-5 text-gray-700">Voornaam</label>
                                        @error('registration.name_first')
                                        <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-span-6 sm:col-span-3 col-start-1 sm:col-start-1 lg:col-start-auto lg:col-span-2 input-group">
                                        <input id="name_suffix" wire:model="registration.name_suffix"
                                               class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                        <label for="name_suffix"
                                               class="block text-sm font-medium leading-5 text-gray-700">Tussenvoegsel</label>
                                    </div>

                                    <div class="col-span-12 sm:col-span-6 col-start-1 sm:col-start-1 lg:col-start-auto lg:col-span-4 input-group">
                                        <input id="name" wire:model="registration.name"
                                               class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                        <label for="name"
                                               class="block text-sm font-medium leading-5 text-gray-700">Achternaam</label>
                                        @error('registration.name')
                                        <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-span-12 sm:col-span-6 col-start-1 sm:col-start-1 lg:col-start-auto lg:col-span-3 input-group">
                                        <input id="password" wire:model="password"
                                               class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                        <label for="password"
                                               class="block text-sm font-medium leading-5 text-gray-700">Creeër
                                            wachtwoord</label>
                                    </div>

                                    <div class="col-span-12 sm:col-span-6 col-start-1 sm:col-start-1 md:col-start-auto lg:col-span-3 input-group">
                                        <input id="password_confirm" wire:model="password_confirmation"
                                               class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                        <label for="password_confirm"
                                               class="block text-sm font-medium leading-5 text-gray-700">Herhaal
                                            wachtwoord</label>
                                        @error('password')
                                        <div class="mt-1 text-red-500 text-sm">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-span-12 lg:col-span-3 mid-grey">
                                        <div class="text-{{$this->minCharRule}}-700">@if($this->minCharRule === 'green')
                                                check @elseif($this->minCharRule === 'red') X @endif Min 8 chars
                                        </div>
                                        <div class="text-{{ $this->minDigitRule  }}-700">@if($this->minDigitRule === 'green')
                                                check @elseif($this->minDigitRule === 'red') X @endif Min 1 cijfer
                                        </div>
                                        <div class="text-{{ $this->specialCharRule  }}-700">@if($this->specialCharRule === 'green')
                                                check @elseif($this->specialCharRule === 'red') X @endif Min 1 speciaal
                                            teken (bijv $ of @)</span></div>
                                    </div>

                                </div>
                                <div class="sm:text-right mt-14">
                                    <button class="button button-md primary-button transition ease-in-out duration-150"
                                            disabled>
                                        Ga naar jouw schoolgegevens
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @elseif($this->step === 2)
                    <div class="grid grid-cols-12">
                        {{--content header--}}
                        <div class="col-span-3 sm:col-span-2 md:col-span-1">
                            <img class="card-header-img" src="/svg/stickers/school.svg" alt="">
                        </div>
                        <div class="col-span-9 sm:col-span-10 md:col-span-11 sm:mb-5">
                            <h1 class="mt-2.5 ml-2.5">Wat zijn jouw schoolgegevens?</h1>
                        </div>

                        <div class="col-span-12">
                            <form wire:submit.prevent="step2" action="#" method="POST">
                                <div class="grid grid-cols-12 gap-3 flex content-start">
                                    <div class="col-span-12 sm:col-span-6 lg:col-span-6">
                                        <label for="school_location"
                                               class="block text-sm font-medium leading-5 text-gray-700">Schoolnaam</label>
                                        <input id="school_location" wire:model="registration.school_location"
                                               class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                        @error('registration.school_location')
                                        <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-span-12 sm:col-span-6 lg:col-span-6">
                                        <label for="website_url"
                                               class="block text-sm font-medium leading-5 text-gray-700">Website</label>
                                        <input id="state" wire:model="registration.website_url"
                                               class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                        @error('registration.website_url')
                                        <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-span-12 sm:col-span-9  md:col-span-7">
                                        <label for="address"
                                               class="block text-sm font-medium leading-5 text-gray-700">Adres</label>
                                        <input id="address" wire:model="registration.address"
                                               class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                        @error('registration.address')
                                        <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-span-6 sm:col-span-3 md:col-span-2">
                                        <label for="number"
                                               class="block text-sm font-medium leading-5 text-gray-700">Huisnummer</label>
                                        <input id="number"
                                               class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">

                                    </div>
                                    <div class="col-span-3 col-start-1 md:col-start-1 md:col-span-2">
                                        <label for="postcode"
                                               class="block text-sm font-medium leading-5 text-gray-700">Postcode</label>
                                        <input id="postcode" wire:model="registration.postcode"
                                               class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                        @error('registration.postcode')
                                        <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-span-9 sm:col-span-9 md:col-span-7">
                                        <label for="city" wire:model="registration.city"
                                               class="block text-sm font-medium leading-5 text-gray-700">Plaatsnaam</label>
                                        <input id="postcode" wire:model="registration.city"
                                               class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                        @error('registration.city')
                                        <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    {{--                                            <div class="col-span-6 sm:col-span-3 lg:col-span-2">--}}
                                    {{--                                                <label for="state" wire:model="registration.city"--}}
                                    {{--                                                       class="block text-sm font-medium leading-5 text-gray-700">Plaats</label>--}}
                                    {{--                                                <input id="state"--}}
                                    {{--                                                       class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">--}}
                                    {{--                                                @error('registration.name_first')--}}
                                    {{--                                                <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>--}}
                                    {{--                                                @enderror--}}
                                    {{--                                            </div>--}}
                                    <div class="col-span-6">
                                        <button
                                                wire:click=backToStepOne"
                                                class="text-button transition ease-in-out duration-150">
                                            < Terug naar jouw docentprofiel
                                        </button>
                                    </div>
                                    <div class="col-span-12 md:col-span-6">

                                        <button
                                                class="button button-md primary-button md:float-right">
                                            Maak mijn Test-Correct account >
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @elseif($this->step === 1)
                    <div class="grid grid-cols-12">
                        {{--content header--}}
                        <div class="col-span-3 sm:col-span-2 md:col-span-1">
                            <img class="card-header-img" src="/svg/stickers/completed.svg" alt="">
                        </div>
                        <div class="col-span-9 sm:col-span-10 md:col-span-11 sm:mb-5">
                            <h1 class="mt-2.5 ml-2.5">Je bent nu klaar! Met Test-Correct kun je...</h1>
                        </div>
                        <div class="col-span-12">
                            <div class="grid grid-cols-12 gap-5 body1">
                                <div class="col-span-12 md:col-span-6">
                                    <img src="/svg/stickers/toetsen-maken-afnemen.svg" alt="" class="mr-5 float-left">
                                    <span class="klaar-text">Toetsen aanmaken en bestaande toetsen omzetten.</span>
                                </div>
                                <div class="col-span-12 md:col-span-6">
                                    <img src="/svg/stickers/toetsen-beoordelen-bespreken.svg" alt=""
                                         class="mr-5 float-left">
                                    <span>Toetsen beoordelen en samen de toets bespreken</span>
                                </div>
                                <div class="col-span-12 md:col-span-6">
                                    <img src="/svg/stickers/klassen.svg" alt="" class="mr-5 float-left">
                                    <span>Klassen maken en uitnodigen om een toets af te nemen</span>
                                </div>
                                <div class="col-span-12 md:col-span-6">
                                    <img src="/svg/stickers/toetsresultaten-analyse.svg" alt="" class="mr-5 float-left">
                                    <span>Toetsresultaten delen en analystische feedback inzien.</span>
                                </div>
                                <div class="col-span-12">
                                    <span class="block mb-3">Deel op social media dat je een test-Correct docent account hebt aangemaakt.</span>
                                    <button class="button button-sm secondary-button"><img class="w-20 h-5"
                                                                                           src="/svg/logos/Logo-LinkedIn.svg"
                                                                                           alt=""></button>
                                    <button class="button button-sm secondary-button"><img class="w-20 h-5"
                                                                                           src="/svg/logos/Logo-Twitter.svg"
                                                                                           alt=""></button>
                                    <button class="button button-sm secondary-button"><img class="w-20 h-5"
                                                                                           src="/svg/logos/Logo-Facebook.svg"
                                                                                           alt=""></button>
                                </div>
                                <div class="col-span-12">
                                    <div class="notification warning">
                                        <div class="title">Hallo titel</div>
                                    </div>
                                    <div class="notification error">
                                        <div class="title">Error title</div>
                                        <div class="body">Error body</div>
                                    </div>
                                    <div class="notification informational">
                                        <div class="title">Informational title</div>
                                        <div class="body">informational body</div>
                                    </div>
                                </div>
                                <div class="col-span-6">
                                    <button
                                            wire:click=backToStepTwo"
                                            class="text-button transition ease-in-out duration-150">
                                        < Terug naar jouw schoolgegevens
                                    </button>
                                </div>
                                <div class="col-span-12 md:col-span-6">
                                    <button
                                            class="button button-md cta-button md:float-right">
                                        Inloggen op Test-Correct ->
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                @endif
            </div>
            <div class="col-span-1 sm:text-right px-3">
                <span>Heb je al een account?</span>
                <button class="text-button">
                    <span class="bold">Log in -></span>
                </button>
            </div>
            <div class="col-span-1 px-3">
                <span>Ben je een student?</span>
                <button class="text-button">
                    <span class="bold">Kijk hier -></span>
                </button>
            </div>
        </div>
    </div>
</div>