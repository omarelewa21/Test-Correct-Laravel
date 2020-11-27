<div>
    <div class="py-5 bg-white onboarding-header">
        <div class="max-w-2xl mx-auto grid grid-cols-3 gap-4 mid-grey">
            <div class="col-span-3">
                <img class="mx-auto tc-logo" src="/svg/logos/Logo-Test-Correct recolored.svg" alt="Test-Correct">
            </div>
            @if($this->step === 1)
                <div class="text-center md:text-right col-span-3 sm:col-span-1 active">
                    <div class="inline-block rounded-full header-number mr-2">1</div>
                    <span class="inline-block align-middle">Jouw docentprofiel</span>
                </div>
                <div class="text-center col-span-3 sm:col-span-1">
                    <div class="inline-block rounded-full header-number mr-2">2</div>
                    <span class="inline-block align-middle">Jouw schoolgegevens</span>
                </div>
                <div class="text-center md:text-left col-span-3 sm:col-span-1">
                    <div class="inline-block rounded-full header-number mr-2">3</div>
                    <span>Klaar!</span>
                </div>
            @endif
            @if($this->step === 2)
                <div class="text-center md:text-right col-span-3 sm:col-span-1 active">
                    <img class="inline-block header-check" src="/svg/icons/checkmark-circle.svg" alt="">
                    <span class="inline-block align-middle">Jouw docentprofiel</span>
                </div>
                <div class="text-center col-span-3 sm:col-span-1 active">
                    <div class="inline-block rounded-full header-number mr-2">2</div>
                    <span class="inline-block align-middle">Jouw schoolgegevens</span>
                </div>
                <div class="text-center md:text-left col-span-3 sm:col-span-1">
                    <div class="inline-block rounded-full header-number mr-2">3</div>
                    <span>Klaar!</span>
                </div>
            @endif
            @if($this->step === 3)
                <div class="text-center md:text-right col-span-3 sm:col-span-1 active">
                    <img class="inline-block header-check" src="/svg/icons/checkmark-circle.svg" alt="">
                    <span class="inline-block align-middle">Jouw docentprofiel</span>
                </div>
                <div class="text-center col-span-3 sm:col-span-1 active">
                    <img class="inline-block header-check" src="/svg/icons/checkmark-circle.svg" alt="">
                    <span class="inline-block align-middle">Jouw schoolgegevens</span>
                </div>
                <div class="text-center md:text-left col-span-3 sm:col-span-1 active">
                    <img class="inline-block header-check" src="/svg/icons/checkmark-circle.svg" alt="">
                    <span>Klaar!</span>
                </div>
            @endif
        </div>
    </div>
    <div class="onboarding-body">
        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-2 base px-4 py-5 sm:p-6">
                <div class="pb-5 col-span-2">
                    <div class="text-center">
                        <h2>Maak een Test-Correct docent account</h2>
                        <h3>Digitaal toetsen dat wél werkt!</h3>
                    </div>
                </div>
                <div class="col-span-2 bg-white rounded-10 p-4 sm:p-10 content-section">
                    @if($this->step === 1)
                        <div class="grid grid-cols-12" wire:key="step1">
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
                                    <div
                                            class="grid sm:grid-cols-12 lg:grid-cols-9 md:grid-cols-12 gap-3 gap-y-5 flex content-start">
                                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                                            <label for="gender_male"
                                                   class="block text-sm font-medium leading-5">Aanhef</label>
                                            @if($this->registration->gender === 'male')
                                                <button type="button"
                                                        class="relative inline-flex w-full items-center p-4 select-button btn-active">
                                                    <svg class="mr-3" width="46" height="46"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <g fill="none" fill-rule="evenodd">
                                                            <path
                                                                    d="M32.21 42.91C29.06 38.242 22.09 35 14 35c-2.717 0-5.307.366-7.669 1.028"
                                                                    fill="#FFF"
                                                                    stroke-linejoin="round"/>
                                                            <path
                                                                    d="M25.935 25.919v5.9a2.143 2.143 0 00-.004 3.518 5.517 5.517 0 01-11.028-.225l.002.081a4.596 4.596 0 00-.002-8.131v-1.143h11.032z"
                                                                    fill="#FFF" stroke-linejoin="round"/>
                                                            <g transform="translate(10 6)">
                                                                <circle class="hair" stroke-linejoin="round" cx="5.516"
                                                                        cy="11.645" r="5.516"/>
                                                                <circle class="hair" stroke-linejoin="round" cx="13.177"
                                                                        cy="7.048" r="7.048"/>
                                                                <path
                                                                        d="M19.919 7.967c3.983 0 4.903 9.086 4.903 12.564a9.193 9.193 0 01-18.387 0c0-1.168-1.271-2.318-.874-3.396a6.72 6.72 0 01.422-.919 5.21 5.21 0 118.877-5.457c.258-.022.515-.034.768-.034 1.6 0 2.299-2.758 4.29-2.758z"
                                                                        fill="#FFF" stroke-linejoin="round"/>
                                                                <circle class="hair" stroke-linejoin="round" cx="19.306"
                                                                        cy="7.661" r="5.516"/>
                                                                <path class="hair"
                                                                      d="M15.628 29.725c-4.756 0-8.669-3.613-9.144-8.244l-.049-.03c1.532.92 4.444 3.677 5.823 1.838-.154 3.065 2.451 3.678 5.056 3.371 2.605-.306 4.75-1.992 4.443-4.29 1.839.766 3.065-.153 3.065-1.839a9.193 9.193 0 01-9.194 9.194z"
                                                                      stroke-linejoin="round"/>
                                                                <path d="M7.006 20.753a2.145 2.145 0 11.264-3.022"
                                                                      fill="#FFF" stroke-linecap="round"
                                                                      stroke-linejoin="round"/>
                                                                <circle class="eye" cx="20.838" cy="16.643" r="1"/>
                                                                <circle class="eye" cx="13.177" cy="17.256" r="1"/>
                                                                <circle stroke-linecap="round" stroke-linejoin="round"
                                                                        cx="12.717" cy="17.314" r="2.758"/>
                                                                <path class="hair"
                                                                      d="M21.757 22.677c0-1.524-2.145-2.299-3.774-1.336-1.015.407-2.192.11-3.112.11-.919 0-2.758.766-2.604 2.758"
                                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                                <circle stroke-linecap="round" stroke-linejoin="round"
                                                                        cx="20.685" cy="16.701" r="2.758"/>
                                                                <path
                                                                        d="M17.865 16.503a1.831 1.831 0 00-1.164-.415c-.505 0-.962.204-1.295.533M24.209 15.016l-.92.612"
                                                                        stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path
                                                                        d="M13.807 23.777c.637-1.043 1.079-1.002 1.65-.91.469.076 1.062.179 1.68.092.663-.093 1.183-.29 1.568-.435.358-.135.632-.22.82-.221.873.758.765 1.124.552 1.516a2.895 2.895 0 01-.673.804 3.772 3.772 0 01-1.915.843c-.894.126-1.732.023-2.365-.234-.769-.312-1.231-.845-1.317-1.455z"
                                                                        fill="#FFF" stroke-linecap="round"
                                                                        stroke-linejoin="round"/>
                                                            </g>
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M19.806 22.854l-3.677-.613"/>
                                                            <circle stroke-width="2" cx="23" cy="23" r="22"/>
                                                        </g>
                                                    </svg>
                                                    <svg class="absolute top-2 right-2 overflow-visible" width="22"
                                                         height="22"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <g fill="none" fill-rule="evenodd">
                                                            <circle fill="#004DF5" cx="11" cy="11" r="11"/>
                                                            <path d="M6 10.5l4 4 6-8" stroke="#FFF"
                                                                  stroke-linecap="round" stroke-width="3"/>
                                                        </g>
                                                    </svg>
                                                    Meneer
                                                </button>
                                            @else
                                                <button wire:click="$set('registration.gender', 'male')" type="button"
                                                        class="inline-flex w-full items-center p-4 select-button ">
                                                    <svg class="mr-3" width="46" height="46"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <g fill="none" fill-rule="evenodd">
                                                            <path
                                                                    d="M32.21 42.91C29.06 38.242 22.09 35 14 35c-2.717 0-5.307.366-7.669 1.028"
                                                                    fill="#FFF"
                                                                    stroke-linejoin="round"/>
                                                            <path
                                                                    d="M25.935 25.919v5.9a2.143 2.143 0 00-.004 3.518 5.517 5.517 0 01-11.028-.225l.002.081a4.596 4.596 0 00-.002-8.131v-1.143h11.032z"
                                                                    fill="#FFF" stroke-linejoin="round"/>
                                                            <g transform="translate(10 6)">
                                                                <circle class="hair" stroke-linejoin="round" cx="5.516"
                                                                        cy="11.645" r="5.516"/>
                                                                <circle class="hair" stroke-linejoin="round" cx="13.177"
                                                                        cy="7.048" r="7.048"/>
                                                                <path
                                                                        d="M19.919 7.967c3.983 0 4.903 9.086 4.903 12.564a9.193 9.193 0 01-18.387 0c0-1.168-1.271-2.318-.874-3.396a6.72 6.72 0 01.422-.919 5.21 5.21 0 118.877-5.457c.258-.022.515-.034.768-.034 1.6 0 2.299-2.758 4.29-2.758z"
                                                                        fill="#FFF" stroke-linejoin="round"/>
                                                                <circle class="hair" stroke-linejoin="round" cx="19.306"
                                                                        cy="7.661" r="5.516"/>
                                                                <path class="hair"
                                                                      d="M15.628 29.725c-4.756 0-8.669-3.613-9.144-8.244l-.049-.03c1.532.92 4.444 3.677 5.823 1.838-.154 3.065 2.451 3.678 5.056 3.371 2.605-.306 4.75-1.992 4.443-4.29 1.839.766 3.065-.153 3.065-1.839a9.193 9.193 0 01-9.194 9.194z"
                                                                      stroke-linejoin="round"/>
                                                                <path d="M7.006 20.753a2.145 2.145 0 11.264-3.022"
                                                                      fill="#FFF" stroke-linecap="round"
                                                                      stroke-linejoin="round"/>
                                                                <circle class="eye" cx="20.838" cy="16.643" r="1"/>
                                                                <circle class="eye" cx="13.177" cy="17.256" r="1"/>
                                                                <circle stroke-linecap="round" stroke-linejoin="round"
                                                                        cx="12.717" cy="17.314" r="2.758"/>
                                                                <path class="hair"
                                                                      d="M21.757 22.677c0-1.524-2.145-2.299-3.774-1.336-1.015.407-2.192.11-3.112.11-.919 0-2.758.766-2.604 2.758"
                                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                                <circle stroke-linecap="round" stroke-linejoin="round"
                                                                        cx="20.685" cy="16.701" r="2.758"/>
                                                                <path
                                                                        d="M17.865 16.503a1.831 1.831 0 00-1.164-.415c-.505 0-.962.204-1.295.533M24.209 15.016l-.92.612"
                                                                        stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path
                                                                        d="M13.807 23.777c.637-1.043 1.079-1.002 1.65-.91.469.076 1.062.179 1.68.092.663-.093 1.183-.29 1.568-.435.358-.135.632-.22.82-.221.873.758.765 1.124.552 1.516a2.895 2.895 0 01-.673.804 3.772 3.772 0 01-1.915.843c-.894.126-1.732.023-2.365-.234-.769-.312-1.231-.845-1.317-1.455z"
                                                                        fill="#FFF" stroke-linecap="round"
                                                                        stroke-linejoin="round"/>
                                                            </g>
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M19.806 22.854l-3.677-.613"/>
                                                            <circle stroke-width="2" cx="23" cy="23" r="22"/>
                                                        </g>
                                                    </svg>

                                                    Meneer
                                                </button>
                                            @endif
                                        </div>


                                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                                            <label for="gender_female"
                                                   class="hidden md:block text-sm font-medium leading-5 text-gray-700">&nbsp;</label>
                                            @if($this->registration->gender === 'female')
                                                <button type="button"
                                                        class="relative inline-flex w-full items-center select-button  btn-active">
                                                    <svg class="mr-3" width="46" height="46"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <g fill="none" fill-rule="evenodd">
                                                            <g transform="matrix(-1 0 0 1 39.5 7)">
                                                                <path class="hair"
                                                                      d="M16.429.257c7.18 0 13.183 5.046 14.654 11.785h.043l3.747 16.063c-.26.393-.531.777-.815 1.152H0l2.533-8.488c-.712-1.658-1.104-3.51-1.104-5.512 0-8.284 6.715-15 15-15z"
                                                                      stroke-linejoin="round"/>
                                                                <path
                                                                        d="M29.945 32.931C26.53 31.096 22.205 30 17.5 30c-5.402 0-10.304 1.446-13.903 3.795"
                                                                        fill="#FFF" stroke-linejoin="round"/>
                                                                <path
                                                                        d="M21.559 23.342l-.146.141a6.025 6.025 0 00-1.77 4.274c0 1.358.447 2.61 1.203 3.62-1.018 3.417-7.774 2.82-8.216.387a4.533 4.533 0 00-3.889-7.32c.157-.258.409-.514.778-.767 2.089-1.426 6.102-1.538 12.04-.335z"
                                                                        fill="#FFF" stroke-linejoin="round"/>
                                                                <path
                                                                        d="M14.505 27.153a9.066 9.066 0 009.066-9.066c0-.887-1.42-1.505-1.813-2.569-.495-1.34-.068-3.122-1.05-4.044a9.034 9.034 0 00-6.203-2.453c-2.72 0-5.16-1.825-6.82.072-1.398 1.595-2.245 6.706-2.245 8.994a9.066 9.066 0 009.065 9.066z"
                                                                        fill="#FFF" stroke-linejoin="round"/>
                                                                <path d="M22.514 20.01a2.115 2.115 0 10-.26-2.98"
                                                                      fill="#FFF" stroke-linecap="round"
                                                                      stroke-linejoin="round"/>
                                                                <circle class="eye" transform="rotate(70 16.07 17.142)"
                                                                        cx="16.069" cy="17.142" r="1"/>
                                                                <path
                                                                        d="M15.261 14.611c.302-.453 2.418-1.057 3.626 0M10.124 13.856c-1.008-.604-1.965-.705-2.871-.302M11.198 18.255c.012.148-.695.522-.777.777-.13.4.13.762 1.295 1.036M10.88 22.015c1.41.705 2.517.705 3.323 0"
                                                                        stroke-linecap="round" stroke-linejoin="round"/>
                                                                <circle class="eye" transform="rotate(-50 8.886 16.377)"
                                                                        cx="8.886" cy="16.377" r="1"/>
                                                                <path class="hair"
                                                                      d="M6.195 7.056c.756 2.57 3.606 6.196 8.613 6.196s6.346 2.115 6.346 4.835c0 1.3 2.716-2.646 1.85-7.12-.946-4.888-5.582-7.386-8.196-7.386"
                                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                            </g>
                                                            <circle stroke-width="2" cx="23" cy="23" r="22"/>
                                                        </g>
                                                    </svg>
                                                    <svg class="absolute top-2 right-2 overflow-visible" width="22"
                                                         height="22"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <g fill="none" fill-rule="evenodd">
                                                            <circle fill="#004DF5" cx="11" cy="11" r="11"/>
                                                            <path d="M6 10.5l4 4 6-8" stroke="#FFF"
                                                                  stroke-linecap="round" stroke-width="3"/>
                                                        </g>
                                                    </svg>
                                                    Mevrouw
                                                </button>
                                            @else
                                                <button wire:click="$set('registration.gender', 'female')" type="button"
                                                        class="inline-flex w-full items-center select-button ">
                                                    <svg class="mr-3" width="46" height="46"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <g fill="none" fill-rule="evenodd">
                                                            <g transform="matrix(-1 0 0 1 39.5 7)">
                                                                <path class="hair"
                                                                      d="M16.429.257c7.18 0 13.183 5.046 14.654 11.785h.043l3.747 16.063c-.26.393-.531.777-.815 1.152H0l2.533-8.488c-.712-1.658-1.104-3.51-1.104-5.512 0-8.284 6.715-15 15-15z"
                                                                      stroke-linejoin="round"/>
                                                                <path
                                                                        d="M29.945 32.931C26.53 31.096 22.205 30 17.5 30c-5.402 0-10.304 1.446-13.903 3.795"
                                                                        fill="#FFF" stroke-linejoin="round"/>
                                                                <path
                                                                        d="M21.559 23.342l-.146.141a6.025 6.025 0 00-1.77 4.274c0 1.358.447 2.61 1.203 3.62-1.018 3.417-7.774 2.82-8.216.387a4.533 4.533 0 00-3.889-7.32c.157-.258.409-.514.778-.767 2.089-1.426 6.102-1.538 12.04-.335z"
                                                                        fill="#FFF" stroke-linejoin="round"/>
                                                                <path
                                                                        d="M14.505 27.153a9.066 9.066 0 009.066-9.066c0-.887-1.42-1.505-1.813-2.569-.495-1.34-.068-3.122-1.05-4.044a9.034 9.034 0 00-6.203-2.453c-2.72 0-5.16-1.825-6.82.072-1.398 1.595-2.245 6.706-2.245 8.994a9.066 9.066 0 009.065 9.066z"
                                                                        fill="#FFF" stroke-linejoin="round"/>
                                                                <path d="M22.514 20.01a2.115 2.115 0 10-.26-2.98"
                                                                      fill="#FFF" stroke-linecap="round"
                                                                      stroke-linejoin="round"/>
                                                                <circle class="eye" transform="rotate(70 16.07 17.142)"
                                                                        cx="16.069" cy="17.142" r="1"/>
                                                                <path
                                                                        d="M15.261 14.611c.302-.453 2.418-1.057 3.626 0M10.124 13.856c-1.008-.604-1.965-.705-2.871-.302M11.198 18.255c.012.148-.695.522-.777.777-.13.4.13.762 1.295 1.036M10.88 22.015c1.41.705 2.517.705 3.323 0"
                                                                        stroke-linecap="round" stroke-linejoin="round"/>
                                                                <circle class="eye" transform="rotate(-50 8.886 16.377)"
                                                                        cx="8.886" cy="16.377" r="1"/>
                                                                <path class="hair"
                                                                      d="M6.195 7.056c.756 2.57 3.606 6.196 8.613 6.196s6.346 2.115 6.346 4.835c0 1.3 2.716-2.646 1.85-7.12-.946-4.888-5.582-7.386-8.196-7.386"
                                                                      stroke-linecap="round" stroke-linejoin="round"/>
                                                            </g>
                                                            <circle stroke-width="2" cx="23" cy="23" r="22"/>
                                                        </g>
                                                    </svg>
                                                    Mevrouw
                                                </button>
                                            @endif
                                        </div>

                                        <div class="col-span-12 sm:col-span-12 lg:col-span-5">
                                            <label for="gender_different"
                                                   class="text-sm font-medium leading-5 text-gray-700 hidden lg:block">&nbsp;</label>
                                            @if($this->registration->gender === 'different')
                                                <button type="button"
                                                        class="relative inline-flex items-center p-4 w-full select-button  btn-active">
                                                    <x-icon.gender-other></x-icon.gender-other>
                                                    <svg class="absolute top-2 right-2 overflow-visible" width="22"
                                                         height="22"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <g fill="none" fill-rule="evenodd">
                                                            <circle fill="#004DF5" cx="11" cy="11" r="11"/>
                                                            <path d="M6 10.5l4 4 6-8" stroke="#FFF"
                                                                  stroke-linecap="round" stroke-width="3"/>
                                                        </g>
                                                    </svg>
                                                    <div class="">
                                                        <span>Anders: </span>
                                                        <input id="gender_different"
                                                               wire:model="registration.gender_different"
                                                               class="form-input sm:ml-2 mr-0 other-input">
                                                    </div>
                                                </button>
                                            @else
                                                <button wire:click="$set('registration.gender', 'different')"
                                                        type="button"
                                                        class="inline-flex items-center p-4 w-full select-button ">
                                                    <svg class="mr-3" width="46" height="46"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <g fill="none" fill-rule="evenodd">
                                                            <path
                                                                    d="M36.94 39.82C33.338 37.456 28.42 36 23 36c-5.373 0-10.251 1.43-13.845 3.757"
                                                                    fill="#FFF" stroke-linejoin="round"/>
                                                            <path
                                                                    d="M23.325 30.874c1.5 0 2.837.698 3.704 1.787a2.367 2.367 0 00.847 4.24 4.734 4.734 0 01-9.186-.335 1.992 1.992 0 00.546-3.346 4.732 4.732 0 014.089-2.346z"
                                                                    fill="#FFF" stroke-linejoin="round"/>
                                                            <path
                                                                    d="M22.895 34.004a8.32 8.32 0 01-8.32-8.32c0-.43-.445-2.236-1.421-3.36-.977-1.125-1.115-1.296-1.035-2.604.139-2.25 1.747-3.85 3.975-5.376 2.227-1.526 4.6.245 6.801.245 2.496 0 4.736-1.674 6.26.066 1.284 1.464 2.061 8.93 2.061 11.028a8.32 8.32 0 01-8.32 8.321z"
                                                                    fill="#FFF" stroke-linejoin="round"/>
                                                            <path class="hair"
                                                                  d="M16.084 10.771a6.086 6.086 0 015.997 5.051 5.24 5.24 0 00-7.616 4.671c0 .98.056 2.146.168 3.5C11.6 23.633 10 20.043 10 16.857a6.084 6.084 0 016.084-6.085z"
                                                            />
                                                            <path d="M15.11 26.713a1.996 1.996 0 11.246-2.813"
                                                                  fill="#FFF" stroke-linecap="round"
                                                                  stroke-linejoin="round"/>
                                                            <path class="hair"
                                                                  d="M19.397 7h6.232a7 7 0 017 7v3.371h0a6.438 6.438 0 00-4.553-1.885H21.89a5.418 5.418 0 01-5.289-4.243l-.132-.592A3 3 0 0119.397 7z"
                                                                  stroke-linecap="round" stroke-linejoin="round"/>
                                                            <g transform="translate(18.494 18.5)">
                                                                <path
                                                                        d="M6.583 5.017c.126.251.358.434.642.585.476.255.868.567.868 1.064 0 .618-.563 1.119-1.258 1.119-.506 0-.942-.266-1.142-.648"
                                                                        stroke-linecap="round" stroke-linejoin="round"/>
                                                                <circle class="eye" cx="9.697" cy="3.738" r="1"/>
                                                                <circle class="eye" cx="2.567" cy="4.309" r="1"/>
                                                                <path
                                                                        d="M3.355 1.748c-.665-.38-1.33-.475-1.996-.285C.81 1.62.355 2.005 0 2.618M11.326 1.846a2.415 2.415 0 00-1.449-.76c-.567-.081-1.138.087-1.712.502"
                                                                        stroke-linecap="round" stroke-linejoin="round"/>
                                                                <path
                                                                        d="M8.575 8.008c.142.562.129 1.16.043 1.586a3.725 3.725 0 01-.773 1.602c-.35.408-.807.712-1.357.791-2.243.32-3.23-1.701-3.23-1.701s-.197-.405-.269-.992a5.5 5.5 0 00.84.287c1.532.383 3.342.04 3.987-.476.257-.206.528-.599.76-1.097z"
                                                                        fill="#FFF" stroke-linecap="round"
                                                                        stroke-linejoin="round"/>
                                                            </g>
                                                            <circle stroke-width="2" cx="23" cy="23" r="22"/>
                                                        </g>
                                                    </svg>
                                                    <div class="">
                                                        <span>Anders: </span>
                                                        <input id="gender_different"
                                                               wire:model="registration.gender_different"
                                                               disabled
                                                               class="form-input sm:ml-2 mr-0 other-input">
                                                    </div>
                                                </button>
                                            @endif
                                        </div>

                                        <div class="col-span-12 sm:col-span-6 lg:col-span-9 input-group">
                                            <input id="email" wire:model.lazy="registration.email"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 border-solid border-2 @error('registration.email') border-red @enderror">
                                            <label for="email"
                                                   class="block text-sm font-medium leading-5 text-gray-700">E-mail</label>
                                        </div>

                                        <div class="col-span-12 sm:col-span-6 lg:col-span-3 input-group">
                                            <input id="name_first" wire:model.lazy="registration.name_first"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 border-solid border-2 @error('registration.name_first') border-red @enderror">
                                            <label for="name_first"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Voornaam</label>
                                        </div>

                                        <div
                                                class="col-span-6 sm:col-span-3 col-start-1 sm:col-start-1 lg:col-start-auto lg:col-span-2 input-group">
                                            <input id="name_suffix" wire:model.lazy="registration.name_suffix"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('registration.name_suffix') border-red @enderror
                                                           ">
                                            <label for="name_suffix"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Tussenvoegsel</label>
                                        </div>

                                        <div
                                                class="col-span-12 sm:col-span-6 col-start-1 sm:col-start-1 lg:col-start-auto lg:col-span-4 input-group">
                                            <input id="name" wire:model.lazy="registration.name"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('registration.name') border-red @enderror">
                                            <label for="name"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Achternaam</label>
                                        </div>
                                        <div
                                                class="col-span-12 sm:col-span-6 col-start-1 sm:col-start-1 lg:col-start-auto lg:col-span-3 input-group">
                                            <input id="password" wire:model.lazy="password" type="password"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('password') border-red @enderror">
                                            <label for="password"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Creeër
                                                wachtwoord</label>
                                        </div>

                                        <div
                                                class="col-span-12 sm:col-span-6 col-start-1 sm:col-start-1 md:col-start-auto lg:col-span-3 input-group">
                                            <input id="password_confirm" wire:model="password_confirmation"
                                                   type="password"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('password') border-red @enderror">
                                            <label for="password_confirm"
                                                   class="block text-sm font-medium leading-5 text-gray-700">
                                                Herhaal wachtwoord</label>
                                        </div>

                                        <div class="col-span-12 lg:col-span-3 mid-grey">
                                            <div
                                                    class="text-{{$this->minCharRule}}-700">@if($this->minCharRule === 'green')
                                                    check @elseif($this->minCharRule === 'red') X @endif Min. 8 tekens
                                            </div>
                                            <div
                                                    class="text-{{ $this->minDigitRule  }}-700">@if($this->minDigitRule === 'green')
                                                    check @elseif($this->minDigitRule === 'red') X @endif Min. 1 cijfer
                                            </div>
                                            <div
                                                    class="text-{{ $this->specialCharRule  }}-700">@if($this->specialCharRule === 'green')
                                                    check @elseif($this->specialCharRule === 'red') X @endif Min. 1
                                                speciaal
                                                teken (bijv. $ of @)
                                            </div>
                                        </div>

                                    </div>
                                    <div class="w-7/12">
                                        @error('registration.gender')
                                        <div class="notification error mt-5">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
                                        @error('registration.email')
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

                                        @if ($btnDisabled)
                                            <button
                                                    class="button button-md primary-button transition ease-in-out duration-150 btn-disabled"
                                            >
                                                Ga naar jouw schoolgegevens
                                            </button>
                                        @else
                                            <button
                                                    class="button button-md primary-button transition ease-in-out duration-150">
                                                Ga naar jouw schoolgegevens
                                            </button>
                                        @endif
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
                                        <div class="col-span-12 sm:col-span-6 lg:col-span-6 input-group">
                                            <input id="school_location" wire:model.lazy="registration.school_location"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('registration.school_location') border-red @enderror">
                                            <label for="school_location"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Schoolnaam</label>
                                        </div>

                                        <div class="col-span-12 sm:col-span-6 lg:col-span-6 input-group">
                                            <input id="state" wire:model.lazy="registration.website_url"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('registration.website_url') border-red @enderror">
                                            <label for="website_url"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Website</label>
                                        </div>

                                        <div class="col-span-12 sm:col-span-9  md:col-span-7 input-group">
                                            <input id="address" wire:model.lazy="registration.address"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('registration.address') border-red @enderror">
                                            <label for="address"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Adres</label>
                                        </div>
                                        <div class="col-span-6 sm:col-span-3 md:col-span-2 input-group">
                                            <input id="house_number" wire:model.lazy="registration.house_number"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('registration.house_number') border-red @enderror">
                                            <label for="house_number"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Huisnummer</label>
                                        </div>
                                        <div class="col-span-3 col-start-1 md:col-start-1 md:col-span-2 input-group">
                                            <input id="postcode" wire:model.lazy="registration.postcode"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('registration.postcode') border-red @enderror">
                                            <label for="postcode"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Postcode</label>
                                        </div>
                                        <div class="col-span-9 sm:col-span-9 md:col-span-7 input-group">
                                            <input id="city" wire:model.lazy="registration.city"
                                                   class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 @error('registration.city') border-red @enderror">
                                            <label for="city"
                                                   class="block text-sm font-medium leading-5 text-gray-700">Plaatsnaam</label>
                                        </div>
                                        <div class="col-span-12">
                                            @foreach($this->getErrorBag()->toArray() as $key => $value)
                                                <div class="notification error mt-5">
                                                    <span class="title">k {{ json_encode($key) }} v {{ json_encode($value) }}</span>
                                                </div>
                                            @endforeach

                                            @error('registration.school_location')
                                            <div class="notification error mt-5">
                                                <span class="title">{{ $message }}</span>
                                            </div>
                                            @enderror
                                            @error('registration.website_url')
                                            <div class="notification error mt-5">
                                                <span class="title">{{ $message }}</span>
                                            </div>
                                            @enderror
                                            @error('registration.address')
                                            <div class="notification error mt-5">
                                                <span class="title">{{ $message }}</span>
                                            </div>
                                            @enderror
                                            @error('registration.house_number')
                                            <div class="notification error mt-5">
                                                <span class="title">{{ $message }}</span>
                                            </div>
                                            @enderror
                                            @error('registration.postcode')
                                            <div class="notification error mt-5">
                                                <span class="title">{{ $message }}</span>
                                            </div>
                                            @enderror
                                            @error('registration.city')
                                            <div class="notification error mt-5">
                                                <span class="title">{{ $message }}</span>
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="col-span-6">
                                            <button
                                                    wire:click="backToStepOne"
                                                    class="text-button transition ease-in-out duration-150">
                                                < Terug naar jouw docentprofiel
                                            </button>
                                        </div>
                                        <div class="col-span-12 md:col-span-6">
                                            @if($btnDisabled)
                                                <button
                                                        wire:click="step2"
                                                        class="button button-md primary-button md:float-right transition duration-150 ease-in-out btn-disabled">
                                                    Maak mijn Test-Correct account >
                                                </button>
                                            @else
                                                <button
                                                        wire:click="step2"
                                                        class="button button-md primary-button md:float-right transition duration-150 ease-in-out">
                                                    Maak mijn Test-Correct account >
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @elseif($this->step === 3)
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
                                        <img src="/svg/stickers/toetsen-maken-afnemen.svg" alt=""
                                             class="mr-5 float-left">
                                        <span class="klaar-text">Toetsen aanmaken en bestaande toetsen omzetten.</span>
                                    </div>
                                    <div class="col-span-12 md:col-span-6">
                                        <img src="/svg/stickers/toetsen-beoordelen-bespreken.svg" alt=""
                                             class="mr-5 float-left">
                                        <span>Toetsen beoordelen en samen de toets bespreken.</span>
                                    </div>
                                    <div class="col-span-12 md:col-span-6">
                                        <img src="/svg/stickers/klassen.svg" alt="" class="mr-5 float-left">
                                        <span>Klassen maken en uitnodigen om een toets af te nemen.</span>
                                    </div>
                                    <div class="col-span-12 md:col-span-6">
                                        <img src="/svg/stickers/toetsresultaten-analyse.svg" alt=""
                                             class="mr-5 float-left">
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
</div>
