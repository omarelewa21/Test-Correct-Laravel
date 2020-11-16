<div class="max-w-4xl mx-auto">
    <div class="px-4 py-5 sm:p-6">
        <div class="mt-10 sm:mt-0">
            <div class="md:grid md:grid-cols-3 md:gap-6">

                <div class="mt-5 md:mt-0 md:col-span-3">

                    <div class="shadow overflow-hidden sm:rounded-md">
                        @if($this->step === 1)
                            <form wire:submit.prevent="step1" action="#" method="POST">
                                <div class="px-4 py-5 bg-white sm:p-6">
                                    <div class="grid grid-cols-6 gap-6">
                                        <div class="col-span-6 sm:col-span-6 lg:col-span-2">
                                            <label for="gender_male"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Aanhef</label>
                                            @if($this->registration->gender === 'male')
                                                <button type="button"
                                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-500 focus:outline-none focus:shadow-outline-purple focus:border-purple-700 active:bg-indigo-700 transition duration-150 ease-in-out">
                                                    Man
                                                </button>
                                            @else
                                                <button wire:click="$set('registration.gender', 'male')" type="button"
                                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:text-gray-800 active:bg-gray-50 transition duration-150 ease-in-out">
                                                    Man
                                                </button>
                                            @endif
                                            @error('registration.gender')
                                            <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                            @enderror
                                        </div>


                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <label for="gender_female"
                                                   class="block text-sm font-medium leading-5 text-gray-700">&nbsp;</label>
                                            @if($this->registration->gender === 'female')
                                                <button type="button"
                                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-500 focus:outline-none focus:shadow-outline-purple focus:border-purple-700 active:bg-indigo-700 transition duration-150 ease-in-out">
                                                    Vrouw
                                                </button>
                                            @else
                                                <button wire:click="$set('registration.gender', 'female')" type="button"
                                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:text-gray-800 active:bg-gray-50 transition duration-150 ease-in-out">
                                                    Vrouw
                                                </button>
                                            @endif
                                        </div>

                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <label for="gender_different"
                                                   class="block text-sm font-medium leading-5 text-gray-700">&nbsp;</label>
                                            @if($this->registration->gender === 'different')
                                                <button type="button"
                                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-500 focus:outline-none focus:shadow-outline-purple focus:border-purple-700 active:bg-indigo-700 transition duration-150 ease-in-out">
                                                    Anders
                                                </button>
                                                <input id="gender_different" wire:model="registration.gender_different"
                                                       class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">

                                            @else
                                                <button wire:click="$set('registration.gender', 'different')"
                                                        type="button"
                                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:text-gray-800 active:bg-gray-50 transition duration-150 ease-in-out">
                                                    Anders
                                                </button>
                                                <input id="gender_different" wire:model="registration.gender_different"
                                                       class="mt-1 form-input bg-gray-200 block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5"
                                                       disabled>

                                            @endif
                                        </div>
                                        <div class="col-span-6 sm:col-span-6 lg:col-span-2">
                                            <label for="name_first"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Voornaam</label>
                                            <input id="name_first" wire:model="registration.name_first"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                            @error('registration.name_first')
                                            <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <label for="name_suffix"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Tussenvoegsel</label>
                                            <input id="name_suffix" wire:model="registration.name_suffix"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                        </div>

                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <label for="name"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Achternaam</label>
                                            <input id="name" wire:model="registration.name"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                            @error('registration.name')
                                            <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-span-6 sm:col-span-6 lg:col-span-2">
                                            <label for="password"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Wachtwoord</label>
                                            <input id="password" wire:model="password"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                        </div>

                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <label for="password_confirm"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Herhaal
                                                wachtwoord</label>
                                            <input id="password_confirm" wire:model="password_confirmation"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                            @error('password')
                                            <div class="mt-1 text-red-500 text-sm">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <div class="text-{{$this->minCharRule}}-700">Min 8 chars</div>
                                            <div class="text-{{ $this->minDigitRule  }}-700">Min 1 cijfer</div>
                                            <div class="text-{{ $this->specialCharRule  }}-700">
                                                Min 1 speciaal teken (bijv $ of @)
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                    <button
                                        class="py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 shadow-sm hover:bg-indigo-500 focus:outline-none focus:shadow-outline-blue active:bg-indigo-600 transition duration-150 ease-in-out">
                                        Ga naar jouw schoolgegevens
                                    </button>
                                </div>
                            </form>
                        @endif
                        @if($this->step === 2)
                            <form wire:submit.prevent="step1" action="#" method="POST">
                                <div class="px-4 py-5 bg-white sm:p-6">
                                    <div class="grid grid-cols-6 gap-6">
                                        <div class="col-span-6 sm:col-span-6 lg:col-span-2">
                                            <label for="school_location"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Schoolnaam</label>
                                            <input id="school_location" wire:model="registration.school_location"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                            @error('registration.school_location')
                                            <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <label for="website_url"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Website</label>
                                            <input id="state" wire:model="registration.website_url"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                            @error('registration.website_url')
                                            <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <label for="address"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Adres</label>
                                            <input id="address" wire:model="registration.address"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                            @error('registration.address')
                                            <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <label for="number"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Huisnummer</label>
                                            <input id="number"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">

                                        </div>
                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <label for="postcode"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Postcode</label>
                                            <input id="postcode" wire:model="registration.postcode"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                            @error('registration.postcode')
                                            <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <label for="city" wire:model="registration.city"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Plaatsnaam</label>
                                            <input id="postcode" wire:model="registration.city"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                            @error('registration.city')
                                            <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-span-6 sm:col-span-3 lg:col-span-2">
                                            <label for="state" wire:model="registration.city"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Plaats</label>
                                            <input id="state"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5">
                                            @error('registration.name_first')
                                            <div class="mt-1 text-red-500 text-sm">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div>{{ $this->step }}</div>
                                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                    <button
                                        wire:click=backToStepOne"
                                        class="py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-indigo-600 bg-white-600 shadow-sm hover:bg-indigo-500 hover:text-white focus:outline-none focus:shadow-outline-blue active:bg-indigo-600 transition duration-150 ease-in-out">
                                        Terug naar jouw docentprofiel
                                    </button>

                                    <button
                                        class="py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 shadow-sm hover:bg-indigo-500 focus:outline-none focus:shadow-outline-blue active:bg-indigo-600 transition duration-150 ease-in-out">
                                        Maak mijn Test-Correct account
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>

                </div>
            </div>
        </div>


    </div>
</div>

