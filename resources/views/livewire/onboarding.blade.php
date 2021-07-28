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
                <div class="flex space-x-6">
                    @if($this->step === 1)
                        <div>
                            <div class="inline-block rounded-full header-number mr-2 active">1</div>
                            <span class="mt-1 active">Jouw docentprofiel</span>
                        </div>
                        <div>
                            <div class="inline-block rounded-full header-number mr-2">2</div>
                            <span class="mt-1">Jouw schoolgegevens</span>
                        </div>
                        <div>
                            <div class="inline-block rounded-full header-number mr-2">3</div>
                            <span class="mt-1">Klaar!</span>
                        </div>
                        <iframe id="frame-step1" src="https://www.test-correct.nl/bedankt-aanmelding-docent/" style="height: 1px; width:1px"></iframe>
                    @endif
                    @if($this->step === 2)
                        <div class="flex items-center">
                            <div class="bg-primary rounded-full header-check text-white flex items-center justify-center mr-3">
                                <x-icon.checkmark/>
                            </div>
                            <span class="active">Jouw docentprofiel</span>
                        </div>
                        <div class="flex items-center">
                            <div class="inline-block rounded-full header-number mr-2 active">2</div>
                            <span class="active">Jouw schoolgegevens</span>
                        </div>
                        <div class="flex items-center">
                            <div class="inline-block rounded-full header-number mr-2">3</div>
                            <span>Klaar!</span>
                        </div>
                            <iframe id="frame-step2" src="https://www.test-correct.nl/bedankt-aanmelding-docent/" style="height: 1px; width:1px"></iframe>
                    @endif
                    @if($this->step === 3)
                        <div class="flex items-center">
                            <div class="bg-primary rounded-full header-check text-white flex items-center justify-center mr-3">
                                <x-icon.checkmark/>
                            </div>
                            <span class="active ">Jouw docentprofiel</span>
                        </div>
                        <div class="flex items-center">
                            <div class="bg-primary rounded-full header-check text-white flex items-center justify-center mr-3">
                                <x-icon.checkmark/>
                            </div>
                            <span class="active">Jouw schoolgegevens</span>
                        </div>
                        <div class="flex items-center">
                            <div class="bg-primary rounded-full header-check text-white flex items-center justify-center mr-3">
                                <x-icon.checkmark/>
                            </div>
                            <span class="active">Klaar!</span>
                        </div>
                            <iframe id="frame-step3" src="https://www.test-correct.nl/bedankt-aanmelding-docent/" style="height: 1px; width:1px"></iframe>
                    @endif
                </div>
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
                                                    <x-icon.onboarding-gender-man></x-icon.onboarding-gender-man>
                                                    <x-icon.checkmark-circle></x-icon.checkmark-circle>
                                                    Meneer
                                                </button>
                                            @else
                                                <button wire:key="registration_male"
                                                        wire:click="$set('registration.gender', 'male')" type="button"
                                                        class="inline-flex w-full items-center p-4 select-button ">
                                                    <x-icon.onboarding-gender-man></x-icon.onboarding-gender-man>
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
                                                    <x-icon.onboarding-gender-woman></x-icon.onboarding-gender-woman>
                                                    <x-icon.checkmark-circle></x-icon.checkmark-circle>
                                                    Mevrouw
                                                </button>
                                            @else
                                                <button wire:click="$set('registration.gender', 'female')" type="button"
                                                        class="inline-flex w-full items-center select-button ">
                                                    <x-icon.onboarding-gender-woman></x-icon.onboarding-gender-woman>
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
                                                        <x-icon.onboarding-gender-other></x-icon.onboarding-gender-other>
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
                                                        <x-icon.onboarding-gender-other></x-icon.onboarding-gender-other>
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
                                        <div class="password items-start mb-4">

                                            <div class="input-group w-1/2 md:w-auto order-1 pr-2 mb-4 md:mb-0">
                                                <input id="password" wire:model="password" type="password"
                                                       class="form-input @error('password') border-red @enderror">
                                                <label for="password"
                                                       class="transition ease-in-out duration-150">Creeër
                                                    wachtwoord</label>
                                            </div>

                                            <div class="input-group w-1/2 md:w-auto order-3 md:order-2 pr-2 md:pl-2 mb-4 md:mb-0">
                                                <input id="password_confirm" wire:model="password_confirmation"
                                                       type="password"
                                                       class="form-input @error('password') border-red @enderror">
                                                <label for="password_confirm"
                                                       class="transition ease-in-out duration-150">
                                                    Herhaal wachtwoord</label>
                                            </div>

                                            <div class="mid-grey w-1/2 md:w-auto order-2 md:order-3 pl-2 h-16 overflow-visible md:h-auto md:overflow-auto requirement-font-size">
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
                                        <div x-data @subjects-update="console.log('subjects updated', $event.detail.subjects)" data-subjects='[]' class="subjects mb-4 ">
                                            <div x-data="subjectSelect()" x-init="init('parentEl')" @click.away="clearSearch()" @keydown.escape="clearSearch()" @keydown="navigate" class="mr-4 mb-4 sm:mb-0 ">
                                                <div>
                                                <label for="subjects"
                                                       class="transition ease-in-out duration-150">Jouw vak(ken)</label>
                                                </div>
                                                <template x-for="(subject, index) in subjects">
                                                    <div class="selected-subject align-top text-sm mt-2 mr-1 ">
                                                        <span class="ml-2 mr-1 leading-relaxed truncate max-w-xs" x-text="subject"></span>
                                                        <button @click.prevent="removeSubject(index)" class="w-6 h-8 inline-block align-middle text-gray-500 hover:text-gray-600 focus:outline-none">
                                                            <img class="icon-close-small" src="img/icons/icons-close-small.png" >
                                                        </button>
                                                    </div>
                                                </template>

                                                <div x-show="!showInput" class="add-button-div align-top text-sm mt-2 mr-1 " x-on:click="showSubjectInput()"></div>

                                                <div x-show="showInput" style="
                                                            width: 12em;
                                                            height: 40px;
                                                            border-radius: 8px;
                                                            overflow: hidden;
                                                            "
                                                            class="responsive subject_select_div" @keydown.enter.prevent="addSubject(textInput)"
                                                >

                                                    <input x-model="textInput" x-ref="textInput" @input="search($event.target.value)" x-on:keyup="filter()" x-on:focus="displaySubjects()" placeholder="Selecteer vak..."  class="input-text-select">
                                                    <img x-show="!show" src="img/icons/icons-chevron-down-small.png"
                                                         srcset="img/icons/icons-chevron-down-small@2x.png 2x,
                                                                    img/icons/icons-chevron-down-small@3x.png 3x"
                                                         class="icons-chevron float-right"
                                                         x-on:click="displaySubjects()"
                                                    >
                                                    <img x-show="show" src="img/icons/icons-chevron-up-small-blue.png"
                                                         srcset="img/icons/icons-chevron-up-small-blue@2x.png 2x,
                                                                    img/icons/icons-chevron-up-small-blue@3x.png 3x"
                                                         class="icons-chevron float-right"
                                                         x-on:click="hideSubjects()"
                                                    >
                                                    <hr x-show="show">
                                                    <div class="subject_select_div_padding">
                                                        <div class="subject_select_div_inner">
                                                            <template x-for="(subject_option, index) in available_subject_options">
                                                                <div x-show="show" :class="{subject_item_active: subject_option==active_subject_option}" x-on:click="addSubject(subject_option)" class="subject_item"><span x-text="subject_option"></span><hr class="subject_hr"></div>
                                                            </template>
                                                        </div>
                                                    </div>
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
                                                    class="flex items-center button button-md primary-button btn-disabled"
                                                    disabled>
                                                <span class="mr-2">Ga naar jouw schoolgegevens</span>
                                                <x-icon.chevron></x-icon.chevron>
                                            </button>
                                        @else
                                            <button wire:click="step1"
                                                    class="flex items-center button button-md primary-button">
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
                                    <div>
                                        <p class="text-note">
                                            Op de dienst Test-Correct zijn de <a class="underline primary-hover" href="https://www.test-correct.nl/algemene-voorwaarden/">algemene voorwaarden</a> van The Teach & Learn Company B.V./Test-Correct van toepassing. Door middel van doorklikken naar de volgende stap bevestigt u dat u de algemene voorwaarden heeft ontvangen, gelezen en begrepen.
                                        </p>
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
                                    <div class="md:flex md:justify-between mt-4 w-full sm:absolute bottom-0">
                                        <button wire:click.prevent="backToStepOne"
                                                class="button text-button flex items-center rotate-svg leading-50 space-x-2 -ml-5">
                                            <x-icon.chevron></x-icon.chevron>
                                            <span class="align-middle">Terug naar jouw docentprofiel</span>
                                        </button>
                                        @if ($btnDisabled)
                                            <button
                                                    class="flex items-center button button-md primary-button btn-disabled"
                                                    disabled>
                                                <span class="mr-2">Maak mijn Test-Correct account</span>
                                                <x-icon.chevron></x-icon.chevron>
                                            </button>
                                        @else
                                            <button
                                                    class="flex items-center button button-md primary-button md:float-right">
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
                                            <span class="klaar-text">Toetsen aanmaken en bestaande toetsen omzetten.</span>
                                        </div>
                                        <div class="w-full sm:w-1/2 sm:pl-2 mb-4 relative">
                                            <img src="/svg/stickers/toetsen-beoordelen-bespreken.svg" alt=""
                                                 class="mr-4 float-left">
                                            <span class="klaar-text">Toetsen beoordelen en samen de toets bespreken.</span>
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
                                        <div class="notification info mb-4">
                                            <span class="title">De verificatie e-mail is opnieuw naar je verzonden.</span>
                                        </div>
                                    @endif
                                    <div class="notification warning stretched mb-4 md:mb-16">
                                        <span class="title">Verifieer je e-mailadres</span>
                                        <span class="body">Open de verificatie mail en klik op 'Verifieer e-mailadres'. Het ontvangen van de e-mail kan enkele minuten duren. Heb je geen mail ontvangen?
                                            <a wire:click="resendEmailVerificationMail" class="bold cursor-pointer">Stuur de verificatiemail opnieuw <x-icon.arrow-small></x-icon.arrow-small></a> of
                                            <a href="https://support.test-correct.nl/knowledge" class="bold"
                                               target="_blank">zoek ondersteuning <x-icon.arrow-small></x-icon.arrow-small></a></span>
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
                        <a class="text-button" href="https://test-correct.nl/student">
                            <span class="bold">Kijk hier</span>
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

        .icons-chevron {
            width: 9px;
            height: 16px;
            object-fit: contain;
            margin-top: 12px;
            margin-right: 20px;
        }
        .input-text-select {
            padding: 8px;
            width: 140px;
        }
        .input-text-select:focus {
            outline: none;
        }
        .input-text-select::placeholder {
            font-family: Nunito Regular;
            font-size: 18px;
            font-weight: normal;
            font-stretch: normal;
            font-style: italic;
            line-height: normal;
            letter-spacing: normal;
            color: #041f74;
        }
        .selected-subject{
            background-color: #cedaf3;
            width: max-content;
            height: 40px;
            margin: 2px 4px 0 0;
            padding: 6px 16px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            font-stretch: normal;
            font-style: normal;
            line-height: 1.33;
            display: inline-flex;
        }
        .add-button-div{
            background-color: #cedaf3;
            height: 40px;
            width: 40px;
            border-radius: 10px;
            display: inline-flex;
            background-image: url('img/icons/icons-plus.png');
            background-repeat: no-repeat;
            background-position: center center;
            position: relative;
            top: -5px;
        }
        .add-button-img{
            width:16px;
            height:16px;
        }

        div.subject_select_div{
            border: 1px solid var(--blue-grey);
            display: inline-block;
        }

        div.show_subjects{
            border : 2px solid #004df5;
        }

        div.subject_select_div_padding{
            padding-right: 5px;
            padding-top: 5px;
            overflow: hidden;
        }

        div.subject_select_div_inner{
            overflow: hidden;
        }

        div.subject_select_div_inner::-webkit-scrollbar {
            width: 8px;               /* width of the entire scrollbar */
        }

        div.subject_select_div_inner::-webkit-scrollbar-track {
            background: transparent;        /* color of the tracking area */
        }

        div.subject_select_div_inner::-webkit-scrollbar-thumb {
            background: #cedaf3;    /* color of the scroll thumb */
            border-radius: 20px;       /* roundness of the scroll thumb */
            border: 4px solid transparent;  /* creates padding around scroll thumb */
        }

        div.subject_item{
            padding:16px;
            padding-top: 7px;
            padding-bottom: 0px;
            font-family: Nunito Regular;
            font-size: 16px;
            font-weight: normal;
            font-stretch: normal;
            font-style: normal;
            line-height: normal;
            letter-spacing: normal;
            color: #041f74;
        }

        div.subject_item:hover,div.subject_item_active{
            color: #004df5;
        }

        hr.subject_hr{
            margin-top: 7px;
        }

        img.icon-close-small{
            margin-top: -2px;
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
                subject_options: [  'Frans',
                    'Duits',
                    'Nederlands',
                    'Wiskunde',
                    'Biologie',
                    'Scheikunde',
                    'Economie'
                ],
                available_subject_options: [],
                active_subject_option: null,
                showInput: true,
                init() {
                    this.subjects = JSON.parse(this.$el.parentNode.getAttribute('data-subjects'));
                    this.available_subject_options = this.subject_options;
                    if(this.subjects.length>0){
                        this.showInput = false;
                    }
                },
                addSubject(subject) {
                    subject = subject.trim();
                    if(this.active_subject_option != null && !this.hasSubject(this.active_subject_option)){
                        this.subjects.push( this.active_subject_option );
                    }else if (subject != "" && !this.hasSubject(subject)) {
                        this.subjects.push( subject )
                    }
                    this.clearSearch();
                    this.$refs.textInput.focus();
                    this.fireSubjectsUpdateEvent();
                    if(this.subjects.length>0){
                        this.showInput = false;
                    }
                },
                displaySubjects() {
                    var div = this.$el.getElementsByClassName('subject_select_div')[0];
                    var inner_div = this.$el.getElementsByClassName('subject_select_div_inner')[0];
                    div.style.height = '190px';
                    inner_div.style.height = '130px';
                    inner_div.style.overflow = 'auto';
                    div.classList.add('show_subjects');
                    this.show = true;
                },
                hideSubjects() {
                    var div = this.$el.getElementsByClassName('subject_select_div')[0];
                    var inner_div = this.$el.getElementsByClassName('subject_select_div_inner')[0];
                    div.style.height = '40px';
                    inner_div.style.overflow = 'hidden';
                    div.classList.remove('show_subjects');
                    this.show = false;
                },
                fireSubjectsUpdateEvent() {
                    this.$el.dispatchEvent(new CustomEvent('subjects-update', {
                        detail: { subjects: this.subjects },
                        bubbles: true,
                    }));
                },
                hasSubject(subject) {
                    var subject = this.subjects.find(e => {
                        return e.toLowerCase() === subject.toLowerCase()
                    })
                    return subject != undefined
                },
                removeSubject(index) {
                    this.subjects.splice(index, 1)
                    this.fireSubjectsUpdateEvent()
                },
                search(q) {
                    if ( q.includes(",") ) {
                        q.split(",").forEach(function(val) {
                            this.addSubject(val)
                        }, this)
                    }
                    this.toggleSearch()
                },
                clearSearch() {
                    this.textInput = '';
                    this.available_subject_options = this.subject_options;
                    this.toggleSearch();
                },
                toggleSearch() {
                    this.open = this.textInput != ''
                },
                filter() {
                    if(this.textInput == ''){
                        this.available_subject_options = this.subject_options;
                        return;
                    }
                    var arr = this.subject_options.map((x) => x);
                    var i = 0;
                    while (i < arr.length) {
                        if(!arr[i].toLowerCase().includes(this.textInput.toLowerCase())){
                            arr.splice(i, 1);
                        } else {
                            ++i;
                        }
                    }
                    this.available_subject_options = arr;
                    if(!this.available_subject_options.includes(this.active_subject_option)){
                        this.active_subject_option = null;
                    }
                },
                navigate(e) {
                    if(e.keyCode!=40&&e.keyCode!=38){
                        return;
                    }
                    e = e || window.event;
                    if(this.active_subject_option==null){
                        this.active_subject_option = this.available_subject_options[0];
                        return this.scroll();
                    }
                    var temp = 0;
                    var active = this.active_subject_option;
                    this.available_subject_options.forEach((element,key) => {
                        if(element==active){
                            temp = key;
                        }
                    });
                    if(e.keyCode==40){
                        if(this.available_subject_options.length>temp){
                            var next_key = temp+1;
                            this.active_subject_option = this.available_subject_options[next_key];
                        }
                        return this.scroll();
                    }
                    if(temp==0){
                        this.active_subject_option = this.available_subject_options[0];
                        return this.scroll();
                    }
                    var previous_key = temp-1;
                    this.active_subject_option = this.available_subject_options[previous_key];
                    this.scroll();
                },
                scroll() {
                    var div = this.$el.getElementsByClassName('subject_item_active')[0];
                    if(div.length==0){
                        return;
                    }
                    div.scrollIntoView({behavior: "smooth", block: "center", inline: "nearest"});
                },
                showSubjectInput()
                {
                    this.showInput = true;
                }
            }

        }

    </script>

@endpush