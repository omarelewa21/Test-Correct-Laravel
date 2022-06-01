<div id="entree">
    <div class="flex w-full items-center justify-center pt-7">
        <a class="flex  w-36 md:w-auto" href="https://test-correct.nl">
            <img class="" src="{{ asset('svg/logos/Logo-Test-Correct-2.svg') }}" alt="Test-Correct">
        </a>
    </div>

    <div class="pt-12" x-data="{step: @entangle('step')}" x-cloak>
        <div class="">
            <div class="relative px-3 sm:px-10">
                <div class="absolute -top-10 left-1/2 -translate-x-1/2">
                    <x-stickers.aanmelden-met-entree/>
                </div>
                <div class="flex flex-col bg-white rounded-10  content-section max-w-xl mx-auto">
                    {{--content header--}}
                    <div class="flex flex-col justify-center pt-10">
                        <div class="flex justify-center relative px-5 mb-4">
                            <h3 class="bold text-xl md:text-[28px]"> {{ __('onboarding.Docent account maken met Entree') }}</h3>
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


                    @if($this->step === 1)
                        <div class="content-form p-5 sm:p-10" wire:key="step1">
                            {{--content form--}}
                            <div class="flex-grow">
                                <form class="h-full relative" wire:submit.prevent="step1" action="#" method="POST">
                                    <div class="email-section mb-4 w-full sm:w-1/2">
                                        <div class="mb-4">
                                            <div class="input-group">
                                                <input id="username" wire:model.lazy="registration.username" @if($this->hasFixedEmail) disabled @endif
                                                       class="form-input @if($this->hasFixedEmail) disabled @endif @error('registration.username') border-red @enderror"
                                                       autofocus>
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
                                             @click="gender = 'different'; $nextTick(() => $el.querySelector('input').focus())"
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
                                                       @if($this->hasValidTUser) disabled @endif
                                                       class="form-input @if($this->hasValidTUser) disabled @endif @error('registration.name_first') border-red @enderror">
                                                <label for="name_first"
                                                       class="transition ease-in-out duration-150">{{ __("onboarding.Voornaam") }}</label>
                                            </div>
                                            <div class="input-group flex mr-4">
                                                <input id="name_suffix" wire:model.lazy="registration.name_suffix"
                                                       @if($this->hasValidTUser) disabled @endif
                                                       class="form-input @if($this->hasValidTUser) disabled @endif @error('registration.name_suffix') border-red @enderror">
                                                <label for="name_suffix"
                                                       class="transition ease-in-out duration-150">{{ __("onboarding.Tussenvoegsel") }}</label>
                                            </div>
                                            <div class="input-group flex flex-1">
                                                <input id="name" wire:model.lazy="registration.name"
                                                       @if($this->hasValidTUser) disabled @endif
                                                       class="form-input md:w-full inline-block @if($this->hasValidTUser) disabled @endif @error('registration.name') border-red @enderror">
                                                <label for="name"
                                                       class="transition ease-in-out duration-150">{{ __("onboarding.Achternaam") }}</label>
                                            </div>
                                        </div>
                                        @if($this->needsPassword)
                                            <div class="password md:space-x-4">
                                                <div class="input-group relative md:flex-1 w-full mb-4 md:mb-0" x-data="{password: '', showPassword: false}">
                                                    <div class="flex items-center" :class="password.length >= 8 ? 'text-cta' : 'text-midgrey'">
                                                        <span class="mr-2" x-show="password.length >= 8" x-cloak><x-icon.checkmark-small/></span>
                                                        <span class="text-sm mt-1">Min. 8 {{ __("onboarding.tekens") }}</span>
                                                    </div>
                                                    <input id="password"
                                                           wire:model="password"
                                                           class="form-input @error('password') border-red @enderror"
                                                           :type="showPassword ? 'text' : 'password'"
                                                           x-model="password">
                                                    <label for="password"
                                                           class="transition ease-in-out duration-150">{{ __("onboarding.Creeër wachtwoord") }}</label>
                                                    <x-icon.preview class="absolute top-[37px] right-3.5 primary-hover cursor-pointer"
                                                                    @click="showPassword = !showPassword"/>
                                                </div>

                                                <div class="input-group relative md:flex-1 w-full mb-4 md:mb-0" x-data="{showPassword: false}">
                                                    <input id="password_confirm"
                                                           wire:model="password_confirmation"
                                                           :type="showPassword ? 'type' : 'password'"
                                                           class="form-input @error('password') border-red @enderror"
                                                    >
                                                    <label for="password_confirm"
                                                           class="transition ease-in-out duration-150">
                                                        {{ __("onboarding.Herhaal wachtwoord") }}</label>
                                                    <x-icon.preview class="absolute top-[37px] right-3.5 primary-hover cursor-pointer"
                                                                    @click="showPassword = !showPassword"/>
                                                </div>
                                            </div>
                                        @endif

                                        @if($this->showSubjects)
                                            <div x-data data-subjects='{!! $selectedSubjectsString !!}'
                                                 class="subjects mt-4 ">
                                                <div x-data="subjectSelect()" x-init="init('parentEl')"
                                                     @click.away="clearSearch()" @keydown.escape="clearSearch()"
                                                     @keydown="navigate" class="mr-4 mb-4 sm:mb-0 ">
                                                    <div>
                                                        <label for="subjects" id="subjects_label"
                                                               class="transition ease-in-out duration-150">{{__('onboarding.Jouw vak(ken)')}}</label>
                                                    </div>
                                                    <template x-for="(subject, index) in subjects">

                                                        <button class="secondary-button selected-subject align-top text-sm mt-2 mr-1 tooltip"
                                                                data-text="{{__('onboarding.Verwijder')}}"
                                                                @click.prevent="removeSubject(index)">
                                                            <span class="ml-2 mr-1 leading-relaxed truncate max-w-xs"
                                                                  x-text="subject"></span>
                                                            <span class=" inline-block align-middle"
                                                                  style="margin:auto">
                                                            <img class="icon-close-small"
                                                                 src="/img/icons/icons-close-small.svg">
                                                        </span>
                                                        </button>
                                                    </template>

                                                    <button x-show="!showInput"
                                                            class="secondary-button add-button-div align-top text-sm mt-2 mr-1 tooltip"
                                                            data-text="{{__('onboarding.Voeg toe')}}"
                                                            @click.prevent="showSubjectInput()">
                                                    <span class=" inline-block align-middle" style="margin:auto">
                                                        <img class="icon-close-small" src="/img/icons/icons-plus.svg">
                                                    </span>
                                                    </button>

                                                    <div x-show="showInput" style="
                                                            width: 12em;
                                                            height: 40px;
                                                            border-radius: 8px;
                                                            overflow: hidden;
                                                            "
                                                         class="responsive subject_select_div"
                                                            {{--@keydown.enter.prevent="addSubject(textInput)"--}}
                                                    >

                                                        <div class="select-search-header"
                                                             x-on:click="toggleSubjects()">{{ __('onboarding.Selecteer vak....') }}
                                                            <img x-show="!show"
                                                                 src="/img/icons/icons-chevron-down-small.svg"
                                                                 class="iconschevron-down-small icons-chevron float-right"
                                                                 x-on:click="displaySubjects()"
                                                            >
                                                            <img x-show="show"
                                                                 src="/img/icons/icons-chevron-up-small-blue.svg"
                                                                 class="iconschevron-down-small icons-chevron float-right"
                                                                 x-on:click="hideSubjects()"
                                                            >
                                                        </div>
                                                        <div class="search-wrapper">
                                                            <input id="input-text-select" x-show="show"
                                                                   x-model="textInput" x-ref="textInput"
                                                                   @input="search($event.target.value)"
                                                                   x-on:keyup="filter()" x-on:focus="focusSearch()"
                                                                   x-on:focusout="loseFocusSearch()"
                                                                   class="form-input input-text-select">
                                                            <img x-show="show"
                                                                 src="/img/icons/icons-search-blue.svg"
                                                                 class="icons-search-small icons-search-active float-right hide-search"
                                                            >
                                                            <img x-show="show"
                                                                 src="/img/icons/icons-search-blue-inactive.svg"
                                                                 class="icons-search-small icons-search-inactive float-right"
                                                            >
                                                        </div>
                                                        <hr x-show="show">
                                                        <div class="subject_select_div_padding">
                                                            <div class="subject_select_div_inner">
                                                                <div x-show="show_new_item"

                                                                     id="new_subject_item"
                                                                     class="subject_item new_subject_item">
                                                                    <span x-text="new_subject_item"></span>
                                                                    <hr class="subject_hr">
                                                                </div>
                                                                <template
                                                                        x-for="(subject_option, index) in available_subject_options">
                                                                    <div x-show="show"
                                                                         :class="{subject_item_active: subject_option==active_subject_option}"
                                                                         x-on:click="addSubject(subject_option)"
                                                                         class="subject_item existing_subject_item">
                                                                        <span x-text="subject_option"></span>
                                                                        <img class="icon-close-small-subjects "
                                                                             src="/img/icons/icons-plus-blue.svg">
                                                                        <hr class="subject_hr">
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif


                                    </div>
                                    <div class="error-section md:mb-20">
                                        @if($this->warningStepOne)
                                            <div class="notification warning mt-4">
                                                <span class="title">{{ __("onboarding.Zijn alle velden correct ingevuld") }}?</span>
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
                                    <div class="flex w-full mt-4">
                                        @if ($btnDisabled)
                                            <button
                                                    class="flex ml-auto items-center button button-md primary-button btn-disabled"
                                                    disabled>
                                                <span class="mr-2">{{ __("cms.Volgende") }}</span>
                                                <x-icon.chevron></x-icon.chevron>
                                            </button>
                                        @else
                                            <button wire:click="step1"
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
                        <div class="content-form relative" wire:key="step2" x-data="{showSchools: @if($this->hasValidTUser) false @else true @endif}">
                            {{--content header--}}
                            @if($this->hasFixedLocation)
                                <div class="flex flex-col p-5 md:p-10">
                                    @if($this->schoolLocation && !$this->school)
                                        <div class="input-section mb-4">
                                            <div class="school-info">
                                                <div class="input-group w-full">
                                                    <input id="school_location"
                                                           value="{{ $this->schoolLocation->name }}" disabled
                                                           class="form-input disabled @error('registration.school_location') border-red @enderror">
                                                    <label for="school_location"
                                                           class="">{{ __("onboarding.Schoolnaam") }}</label>
                                                </div>

                                                <div class="input-group w-full">
                                                    <input id="website_url" disabled
                                                           value="{{ $this->schoolLocation->internetaddress }}"
                                                           class="form-input disabled @error('registration.website_url') border-red @enderror">
                                                    <label for="website_url"
                                                           class="">{{ __("onboarding.Website") }}</label>
                                                </div>
                                                <div class="input-group flex-1 basis-full mr-4">
                                                    <input id="address" disabled
                                                           value="{{ $this->schoolLocation->visit_address }}"
                                                           class="form-input disabled @error('registration.address') border-red @enderror">
                                                    <label for="address"
                                                           class="">{{ __("onboarding.Bezoekadres") }}</label>
                                                </div>
                                                <div class="input-group w-28">
                                                    <input id="postcode" disabled
                                                           value="{{ $this->schoolLocation->visit_postal }}"
                                                           class="form-input disabled  @error('registration.postcode') border-red @enderror">
                                                    <label for="postcode"
                                                           class="">{{ __("onboarding.Postcode") }}</label>
                                                </div>
                                                <div class="input-group w-full">
                                                    <input id="city" disabled
                                                           value="{{ $this->schoolLocation->visit_city }}"
                                                           class="form-input disabled @error('registration.city') border-red @enderror">
                                                    <label for="city"
                                                           class="">{{ __("onboarding.Plaatsnaam") }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex border-b border-bluegrey mb-5">
                                            <div class="flex flex-col w-full" style="height:fit-content">
                                            @foreach($this->schoolLocations as $locationName)
                                                    <span class="flex mx-4 py-2 items-center justify-between
                                                                @if(!$loop->last) border-b border-bluegrey @endif w-full
                                                                text-primary bold
                                                            ">
                                                            <span class="flex flex-1">{{ $locationName }}</span>
                                                        </span>
                                            @endforeach
                                            </div>
                                        </div>
                                    @endif
                                        <div class="">
                                            @if($this->warningStepTwo && !$this->hasFixedLocation)
                                                <div class="notification warning mt-4">
                                                    <span class="title">{{ __("onboarding.Zijn alle velden correct ingevuld") }}?</span>
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
                                </div>
                            @endif
                            @if($this->school)
                                <div class="flex w-full absolute z-10 h-full rounded-b-10 transition-max-height overflow-hidden bg-white-70"
                                     :style="showSchools ? 'max-height: 100%' : 'max-height: 0'"
                                >
                                    <div class="flex flex-col w-full mx-5 mb-5 px-5 pb-5 md:px-10 md:pb-10 md:mx-10 pt-8 main-shadow rounded-b-10 bg-white"
                                         style="height:fit-content"
                                    >
                                        <div class="text-center pb-5 border-b border-bluegrey">
                                            <h6 class="">{{ __('onboarding.Kies locatie(s)') }}</h6>
                                            <p class="">{{ __('onboarding.We hebben meerdere locaties gevonden. Op welke locatie geef jij les?') }}</p>
                                        </div>
                                        <div class="flex border-b border-bluegrey mb-5">
                                            <div class="flex max-h-[210px] flex-col overflow-y-auto w-full">
                                            @foreach($this->school->schoolLocations as $location)

                                                <div wire:click="toggleSchoolLocation('{{ $location->uuid }}',@if($this->isSelectedSchoolLocation($location->uuid)) false @else true @endif )"
                                                     class="flex hover:bg-offwhite hover:text-primary transition cursor-pointer"
                                                >
                                                    <span class="flex mx-4 py-2 items-center justify-between
                                                            @if(!$loop->last) border-b border-bluegrey @endif w-full
                                                            @if($this->isSelectedSchoolLocation($location->uuid)) text-primary bold @endif
                                                            ">
                                                        <span class="flex flex-1">{{ $location->name }}</span>
                                                        @if($this->isSelectedSchoolLocation($location->uuid))
                                                            <x-icon.checkmark class="mx-2 w-4"/>
                                                        @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                            </div>
                                        </div>

                                        @empty($this->selectedLocationsString)
                                        <x-button.cta class="flex justify-center disabled" disabled @click="showSchools = false">
                                            <x-icon.checkmark/>
                                            <span>{{ __('onboarding.Bevestigen') }}</span>
                                        </x-button.cta>
                                        @else
                                            <x-button.cta class="flex justify-center" @click="showSchools = false">
                                                <x-icon.checkmark/>
                                                <span>{{ __('onboarding.Bevestigen') }}</span>
                                            </x-button.cta>
                                        @endempty

                                        <div class="">
                                            @if($this->warningStepTwo && !$this->hasFixedLocation)
                                                <div class="notification warning mt-4">
                                                    <span class="title">{{ __("onboarding.Zijn alle velden correct ingevuld") }}?</span>
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
                                    </div>
                                </div>
                            @endif
                            <div class="flex flex-col w-full flex-1 p-5 sm:px-10">
                                @if($this->school)
                                    @if(!$this->hasValidTUser)
                                        @foreach($this->selectedSchoolLocationList() as $location)
                                            <div class="flex space-x-4 w-full mb-4" wire:key="chosen-{{ $location->uuid }}">
                                                <div class="input-group flex-1">
                                                    <input id="name-{{ $location->uuid }}" disabled class="form-input disabled" value="{{ $location->name }}">
                                                    <label for="name-{{ $location->uuid }}" >{{ __('onboarding.Schoolnaam') }} {{ $loop->iteration }}</label>
                                                </div>

                                                <div class="input-group flex-1">
                                                    <input id="address-{{ $location->uuid }}" disabled class="form-input disabled" value="{{ $location->main_address }}">
                                                    <label for="address-{{ $location->uuid }}" >{{ __('teacher_registered.Adres') }} {{ $loop->iteration }}</label>
                                                </div>

                                            </div>
                                        @endforeach

                                        <x-button.text-button class="mx-auto" @click="showSchools = true">
                                            <x-icon.edit/>
                                            <span>{{ __('onboarding.Wijzig locaties') }}</span>
                                        </x-button.text-button>
                                    @endif
                                @endif
                                @if(!$this->hasValidTUser)
                                <p class="text-note mt-auto">
                                    {{ __('onboarding.general_terms_text_pt_1') }}
                                    <a class="underline primary-hover"
                                       href="https://www.test-correct.nl/algemene-voorwaarden"
                                       target="_blank">
                                        {{ __('onboarding.general_terms') }}
                                    </a> {{ __('onboarding.general_terms_text_pt_2') }}
                                </p>
                                @endif
                                <div class="mt-10 flex justify-between items-center">
                                    <x-button.text-button wire:click="backToStepOne">
                                        <x-icon.chevron class="z-0 rotate-180" />
                                        <span>{{ __('modal.Terug') }}</span>
                                    </x-button.text-button>
                                    @if ($btnDisabled)
                                        <x-button.cta size="md" class="btn-disabled" disabled>
                                            <span>{{ __('auth.Maak account') }}</span>
                                            <x-icon.chevron/>
                                        </x-button.cta>
                                    @else
                                        <x-button.cta size="md" wire:click="step2" onClick="this.setAttribute('disabled',true);">
                                            <span>{{ __('auth.Maak account') }}</span>
                                            <x-icon.chevron/>
                                        </x-button.cta>
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
                                <div class="">
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
                                    <a class="button button-sm primary-button flex-1"
                                       target="_blank" href="https://www.linkedin.com/company/9225774">
                                        LinkedIn
                                    </a>
                                    <a class="button button-sm primary-button flex-1"
                                       target="_blank" href="https://twitter.com/testcorrect">
                                        Twitter
                                    </a>
                                    <a class="button button-sm primary-button flex-1"
                                       target="_blank" href="https://www.facebook.com/TestCorrect/">
                                        Facebook
                                    </a>
                                </div>
                            </div>

                            @if(!$this->hasValidTUser && !$this->hasFixedEmail)
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
                            @endif
                            <div class="flex mt-auto w-full">
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
@push('page_styles')
    <style>
        input[list="languages"] {
            width: 12em;
        }

        select {
            width: 12em;
            margin: 0;
            margin-left: -12.75em;
        }

    </style>
@endpush

@push('page_scripts')
    <script>
        function subjectSelect() {
            return {
                open: false,
                show: false,
                textInput: '',
                subjects: [],
                subject_list_init: {!! $subjectOptions !!},
                available_subject_options: [],
                active_subject_option: null,
                showInput: true,
                show_new_item: false,
                new_subject_item: '',
                init() {
                    this.subjects = JSON.parse(this.$el.parentNode.getAttribute('data-subjects'));
                    this.available_subject_options = this.subject_list_init;
                    this.filterAvailableSubjectOptions();
                    if (this.subjects.length > 0) {
                        this.showInput = false;
                    }
                },
                addSubject(subject) {
                    subject = subject.trim();
                    subject = subject.replace(/'/g, "\x27");
                    subject = subject.replace(/"/g, "\x22");
                    if (this.active_subject_option != null && this.active_subject_option != "" && !this.hasSubject(this.active_subject_option)) {
                        this.subjects.push(this.active_subject_option);
                    } else if (subject != "" && !this.hasSubject(subject)) {
                        this.subjects.push(subject)
                    }
                    this.clearSearch();
                    this.$refs.textInput.focus();
                    if (this.subjects.length > 0) {
                        this.showInput = false;
                    }
                    this.syncSubjects();
                },
                syncSubjects() {
                    @this.
                    call('syncSelectedSubjects', this.subjects);
                },
                toggleSubjects() {
                    var div = this.$el.getElementsByClassName('subject_select_div')[0];
                    if (div.classList.contains('show_subjects')) {
                        this.hideSubjects();
                        return;
                    }
                    this.displaySubjects();
                },
                displaySubjects() {
                    this.show_new_item = false;
                    this.new_subject_item = '';
                    this.active_subject_option = '';
                    this.filterAvailableSubjectOptions();
                    var label = document.getElementById('subjects_label');
                    var div = this.$el.getElementsByClassName('subject_select_div')[0];
                    var inner_div = this.$el.getElementsByClassName('subject_select_div_inner')[0];
                    inner_div.classList.add('subject_select_div_inner_open');
                    div.style.height = '190px';
                    div.classList.add('show_subjects');
                    label.classList.add('label_bold');
                    this.show = true;
                    setTimeout(function () {
                        document.getElementById('input-text-select').focus();
                    }, 1000);
                },
                hideSubjects() {
                    var label = document.getElementById('subjects_label');
                    var div = this.$el.getElementsByClassName('subject_select_div')[0];
                    var inner_div = this.$el.getElementsByClassName('subject_select_div_inner')[0];
                    div.style.height = '40px';
                    inner_div.classList.remove('subject_select_div_inner_open');
                    div.classList.remove('show_subjects');
                    label.classList.remove('label_bold');
                    this.show = false;
                },
                hasSubject(subject) {
                    var subject = this.subjects.find(e => {
                        return e.toLowerCase() === subject.toLowerCase()
                    })
                    return subject != undefined
                },
                removeSubject(index) {
                    this.subjects.splice(index, 1);
                    this.syncSubjects();
                    this.filter();
                    if (this.subjects.length == 0) {
                        this.showInput = true;
                    }
                },
                search(q) {
                    // if ( q.includes(",") ) {
                    //     q.split(",").forEach(function(val) {
                    //         this.addSubject(val)
                    //     }, this)
                    // }
                    this.toggleSearch()
                },
                clearSearch() {
                    this.textInput = '';
                    this.available_subject_options = this.subject_list_init;
                    this.show_new_item = false;
                    this.new_subject_item = '';
                    this.hideSubjects();
                    this.toggleSearch();
                },
                toggleSearch() {
                    this.open = this.textInput != ''
                },
                filter() {
                    if (this.textInput == '') {
                        this.available_subject_options = this.subject_list_init;
                        this.filterAvailableSubjectOptions();
                        return;
                    }
                    this.new_subject_item = this.textInput;
                    var arr = this.subject_list_init.map((x) => x);
                    var i = 0;
                    while (i < arr.length) {
                        if (this.subjects.includes(arr[i])) {
                            arr.splice(i, 1);
                        } else if (!arr[i].toLowerCase().includes(this.textInput.toLowerCase())) {
                            arr.splice(i, 1);
                        } else {
                            ++i;
                        }
                    }
                    this.available_subject_options = arr;
                    if (!this.available_subject_options.includes(this.active_subject_option)) {
                        this.active_subject_option = null;
                    }
                    if (this.available_subject_options.length == 0) {
                        this.show_new_item = true;
                        return;
                    }
                    this.show_new_item = false;
                },
                navigate(e) {
                    this.filterAvailableSubjectOptions();
                    if (e.keyCode != 40 && e.keyCode != 38) {
                        return;
                    }
                    e = e || window.event;
                    document.getElementById('new_subject_item').classList.remove('subject_item_active');
                    if (this.available_subject_options.length == 0) {
                        this.active_subject_option = this.textInput;
                        document.getElementById('new_subject_item').classList.add('subject_item_active');
                        return;
                    }
                    if (this.active_subject_option == null) {
                        this.active_subject_option = this.available_subject_options[0];
                        return this.scroll();
                    }
                    var temp = 0;
                    var active = this.active_subject_option;
                    this.available_subject_options.forEach((element, key) => {
                        if (element == active) {
                            temp = key;
                        }
                    });
                    if (e.keyCode == 40) {
                        if (this.available_subject_options.length > temp) {
                            var next_key = temp + 1;
                            this.active_subject_option = this.available_subject_options[next_key];
                        }
                        return this.scroll();
                    }
                    if (temp == 0) {
                        this.active_subject_option = this.available_subject_options[0];
                        return this.scroll();
                    }
                    var previous_key = temp - 1;
                    this.active_subject_option = this.available_subject_options[previous_key];
                    this.scroll();
                },
                scroll() {
                    var div = this.$el.getElementsByClassName('subject_item_active')[0];
                    if (div == undefined) {
                        return;
                    }
                    div.scrollIntoView({behavior: "smooth", block: "center", inline: "nearest"});
                },
                showSubjectInput() {
                    this.showInput = true;
                },
                focusSearch() {
                    var icon = this.$el.getElementsByClassName('icons-search-active')[0];
                    icon.classList.remove('hide-search');
                    var icon = this.$el.getElementsByClassName('icons-search-inactive')[0];
                    icon.classList.add('hide-search');
                },
                loseFocusSearch() {
                    var icon = this.$el.getElementsByClassName('icons-search-inactive')[0];
                    icon.classList.remove('hide-search');
                    var icon = this.$el.getElementsByClassName('icons-search-active')[0];
                    icon.classList.add('hide-search');
                },
                filterAvailableSubjectOptions() {
                    var arr = this.subject_list_init.map((x) => x);
                    var i = 0;
                    while (i < arr.length) {
                        if (this.subjects.includes(arr[i])) {
                            arr.splice(i, 1);
                        } else if (!arr[i].toLowerCase().includes(this.textInput.toLowerCase())) {
                            arr.splice(i, 1);
                        } else {
                            ++i;
                        }
                    }
                    this.available_subject_options = arr;
                }
            }

        }

    </script>

@endpush