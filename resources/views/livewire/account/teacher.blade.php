<main class="account-page"
      x-data="accountSettings(@entangle('openTab').defer, @js($this->featureSettings['system_language']))"
      x-cloak
>
    <header class="sticky flex flex-col justify-center top-0 w-full z-10">
        <div class="flex w-full px-4 items-center justify-center relative text-white z-10 h-[var(--header-height)] bg-gradient-to-r from-[var(--teacher-primary)] to-[var(--teacher-primary-light)] main-shadow">
            <div class="relative flex justify-center items-center text-center">
                <h4 class="text-white text-center duration-100 transition-opacity"
                    x-on:language-loading-start.window="$el.classList.toggle('opacity-0')"
                    x-on:language-loading-end.window="setTimeout(() => $el.classList.toggle('opacity-0'), 150)"
                    wire:ignore.self
                >@lang('account.account') @lang('account.settings')</h4>

                <x-animations.loading-fade loadProperty="changing"
                                           class="min-w-[250px] transform -translate-x-1/2 -translate-y-1/2 left-1/2 top-1/2"
                                           color="white"
                />
            </div>

            <div class="absolute right-4">
                <div class="flex items-center justify-center min-w-[40px] w-10 h-10 rounded-full bg-white/20 hover:scale-105 transition-transform cursor-pointer"
                     wire:click="redirectBack()"
                >
                    <x-icon.close />
                </div>
            </div>
        </div>
        <x-menu.tab.container class="w-full"
                              :withTileEvents="true"
                              max-width-class="max-w-[1040px] px-5"
                              x-bind:class="{'pointer-events-none': changing}"
        >
            <x-menu.tab.item tab="account" menu="openTab" x-bind:wire:key="'open-account-'+openTab">
                    <span class="flex justify-center">
                        @lang('account.account')
                        <x-animations.loading-fade loadProperty="changing"
                                                   class="bg-lightGrey px-1"
                                                   color="base"
                        />
                    </span>
            </x-menu.tab.item>
            <x-menu.tab.item tab="tests" menu="openTab" x-bind:wire:key="'open-tests-'+openTab">
                    <span class="flex justify-center">
                        @lang('header.Toetsen')
                        <x-animations.loading-fade loadProperty="changing"
                                                   class="bg-lightGrey min-w-[80px] px-1"
                                                   color="base"
                        />
                    </span>
            </x-menu.tab.item>
        </x-menu.tab.container>
    </header>

    <div class="mx-auto max-w-[1040px] py-10 px-5 z-1 isolate">
        {{-- Tab 'Account'--}}
        <div class="flex flex-col gap-8" x-show="openTab === 'account'">
            <div class="flex flex-col w-full gap-4">
                <div class="relative pl-0.5">
                    <h2 class="flex">Test-Correct @lang('account.settings')</h2>
                    <x-animations.loading-fade loadProperty="changing" class="bg-lightGrey w-1/2" color="base" />
                </div>

                <div class="content-section relative p-10 grid grid-cols-1 lg:grid-cols-2 gap-6 w-full ">
                    <div class="system-lang | flex flex-col">
                        <div class="flex flex-col">
                            <x-input.group :label="__('account.Systeem taal')"
                                           x-on:change="startLanguageChange($event, 'featureSettings.system_language')"
                            >
                                <x-input.select wire:model.defer="featureSettings.system_language">
                                    @foreach($this->systemLanguages as $key => $language)
                                        <x-input.option :value="$key"
                                                        :label="$language"
                                        />
                                    @endforeach
                                </x-input.select>
                            </x-input.group>
                        </div>
                    </div>

                    <div class="auto-logout | self-end">
                        <div class="border-b border-t border-bluegrey flex w-full justify-between items-center h-[50px]">
                            <div class="flex items-center gap-2.5">
                                <x-input.toggle class="mr-2" wire:model="featureSettings.enable_auto_logout" />
                                <x-icon.locked />
                                <span class="bold">@lang('account.Automatisch uitloggen na')</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-input.text class="text-center w-[3.375rem]"
                                              :only-integer="true"
                                              wire:model.lazy="featureSettings.auto_logout_minutes"
                                              :disabled="!$this->featureSettings['enable_auto_logout']"
                                              :error="$this->getErrorBag()->has('auto_logout_minutes')"
                                />
                                <span class="bold">min.</span>
                                <x-tooltip>@lang('account.auto_logout_tooltip')</x-tooltip>
                            </div>
                        </div>
                    </div>

                    <x-animations.loading-fade loadProperty="changing"
                                               class=" bg-white content-section p-10 grid grid-cols-1 lg:grid-cols-2 gap-6"
                    >
                        <div class="h-[50px] flex items-center w-full">
                            <x-knightrider />
                        </div>
                        <div class="h-[50px] flex items-center w-full">
                            <x-knightrider />
                        </div>
                    </x-animations.loading-fade>
                </div>
            </div>
            <div class="flex flex-col w-full gap-4">
                <div class="relative pl-0.5">
                    <h2 class="flex">@lang('account.Jouw profiel') - @lang('account.docent account')</h2>
                    <x-animations.loading-fade loadProperty="changing" class="bg-lightGrey w-1/2" color="base" />
                </div>

                <div class="content-section relative p-10 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @unless($this->canEditProfile)
                        <div class="col-span-1 lg:col-span-2">
                            <div class="notification info stretched">
                                <div class="title">
                                    @if($this->editRestriction === 'lvs')
                                        <x-icon.entree />
                                    @endif
                                    <span>@lang('account.uneditable_title_'.$this->editRestriction)</span>
                                </div>
                                @if($this->editRestriction === 'lvs')
                                    <div class="body">@lang('account.uneditable_text_'.$this->editRestriction)</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-col gap-4 col-span-1">
                        <div @class(['gender | flex gap-4 flex-wrap', 'pointer-events-none' => !$this->canEditProfile])
                             x-data="{gender: @entangle('userData.gender')}">
                            <div class="flex space-x-2 items-center hover:text-primary transition cursor-pointer"
                                 @if($this->canEditProfile) x-on:click="gender = 'Male'" @endif
                                 :class="gender === 'Male' ? 'primary bold' : 'text-midgrey'"
                            >
                                <div class="flex">
                                    <x-icon.man class="text-inherit" />
                                </div>
                                <span class="flex">@lang('account.Dhr').</span>
                            </div>
                            <div class="flex space-x-2 items-center hover:text-primary transition cursor-pointer"
                                 @if($this->canEditProfile) x-on:click="gender = 'Female'" @endif
                                 :class="gender === 'Female' ? 'primary bold' : 'text-midgrey'"
                            >
                                <div class="flex">
                                    <x-icon.woman class="text-inherit" />
                                </div>
                                <span class="flex">@lang('account.Mevr').</span>
                            </div>
                            <div class="flex space-x-2 items-center hover:text-primary transition cursor-pointer"
                                 @if($this->canEditProfile) x-on:click="gender = 'Other'; $nextTick(() => $el.querySelector('input').focus())"
                                 @endif
                                 :class="gender === 'Other' ? 'primary bold' : 'text-midgrey'"
                            >
                                <div class="flex">
                                    <x-icon.other class="text-inherit" />
                                </div>
                                <label for="gender_different"
                                       class="flex"
                                >
                                    @lang('account.Anders'):
                                </label>
                                <input id="gender_different"
                                       wire:model.lazy="userData.gender_different"
                                       class="form-input flex"
                                       style="width: 118px;"
                                       x-bind:disabled="gender !== 'Other'"
                                       @disabled(!$this->canEditProfile)
                                       :class="gender !== 'Other' ? 'disabled' : ''"
                                       autocomplete="off"
                                       type="text"
                                >
                            </div>
                        </div>

                        <div class="naw | flex flex-col gap-4">
                            <div class="flex gap-4">
                                <x-input.group :label="__('onboarding.Voornaam')" class="flex-1">
                                    <x-input.text class="w-full" wire:model.debounce="userData.name_first"
                                                  :disabled="!$this->canEditProfile" />
                                </x-input.group>
                                <x-input.group :label="__('onboarding.Tussenv.')"
                                               title="{{ __('onboarding.Tussenvoegsel') }}">
                                    <x-input.text class="w-20" wire:model.debounce="userData.name_suffix"
                                                  :disabled="!$this->canEditProfile" />
                                </x-input.group>
                                <x-input.group :label="__('onboarding.Achternaam')" class="flex-1">
                                    <x-input.text class="w-full" wire:model.debounce="userData.name"
                                                  :disabled="!$this->canEditProfile" />
                                </x-input.group>
                            </div>
                            <x-input.group :label="__('onboarding.your_school_email')" class="flex-1">
                                <x-input.text class="w-full" disabled wire:model="userData.username" />
                            </x-input.group>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 col-span-1">
                        <div class="picture | flex gap-4 items-center">
                            <div class="flex w-[46px] h-[46px] rounded-full border-2 border-sysbase items-center justify-center overflow-hidden">
                                <x-icon.profile class="text-bluegrey scale-[2.5] transform relative top-1" />
                            </div>

                            <x-button.primary disabled size="sm" class="h-10">
                                <x-icon.upload />
                                <span>Profielfoto uploaden</span>
                            </x-button.primary>
                        </div>

                        <div class="password | flex flex-col gap-2"
                             x-data="{showPassword: false}"
                        >
                            <div>
                                <x-button.primary class="" wire:click="$emit('openModal', 'change-password')">
                                    <x-icon.edit />
                                    <span>@lang('header.Wachtwoord wijzigen')</span>
                                </x-button.primary>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 col-span-1 lg:col-span-2">
                        <div class="subjects | flex flex-col gap-2">
                            <span>{{ trans_choice('account.Jouw vakken', $this->subjects['count']) }}</span>
                            <span class="bold">{{ $this->subjects['string'] }}</span>
                        </div>
                        <div class="school-locations | flex flex-col gap-2">
                            <span>{{ trans_choice('account.Jouw scholen', $this->locations['count']) }}</span>
                            <span class="text-midgrey bold">{!! $this->locations['string']  !!}</span>
                        </div>
                        <div class="classes | flex flex-col gap-2">
                            <span>{{ trans_choice('account.Jouw klassen', $this->classes['count']) }}</span>
                            <span class="bold">{{ $this->classes['string'] }}</span>
                        </div>
                    </div>

                    {{-- Language change loading skeleton --}}
                    <x-animations.loading-fade loadProperty="changing"
                                               class=" bg-white content-section p-10 grid grid-cols-1 lg:grid-cols-2 gap-6"
                    >

                        @unless($this->canEditProfile)
                            <div class="col-span-1 lg:col-span-2">
                                <div @class([
                                        'notification info stretched  flex items-center w-full',
                                        'min-h-[58px]' => !$this->editRestriction === 'lvs',
                                        'min-h-[90px]' => $this->editRestriction === 'lvs',
                                        ])>
                                    <x-knightrider color="blue" />
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-col gap-4 col-span-1">
                            <div @class(['gender | flex gap-4 flex-wrap text-midgrey'])>
                                <div class="flex space-x-2 items-center">
                                    <div class="flex w-[46px] h-[46px] rounded-full bg-lightGrey items-center justify-center overflow-hidden"></div>
                                    <span class="min-w-[30px]">
                                            <x-knightrider />
                                        </span>
                                </div>
                                <div class="flex space-x-2 items-center">
                                    <div class="flex w-[46px] h-[46px] rounded-full bg-lightGrey items-center justify-center overflow-hidden"></div>
                                    <span class="min-w-[30px]">
                                            <x-knightrider />
                                        </span>
                                </div>
                                <div class="flex flex-1 space-x-2 items-center">
                                    <div class="flex w-[46px] min-w-[46px] h-[46px] rounded-full bg-lightGrey items-center justify-center overflow-hidden"></div>
                                    <span class="min-w-[30px] w-full flex">
                                            <x-knightrider />
                                        </span>
                                </div>
                            </div>

                            <div class="naw | flex flex-col gap-4">
                                <div class="flex gap-4 h-[64px] items-center">
                                    <div class="flex-1">
                                        <x-knightrider />
                                    </div>
                                    <div class="flex min-w-[80px]">
                                        <x-knightrider />
                                    </div>
                                    <div class="flex-1">
                                        <x-knightrider />
                                    </div>
                                </div>

                                <div class="flex w-full h-[64px] items-center">
                                    <div class="flex-1">
                                        <x-knightrider />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4 col-span-1 self-start">
                            <div class="picture | flex gap-4 items-center">
                                <div class="flex w-[46px] h-[46px] rounded-full bg-lightGrey items-center justify-center overflow-hidden"></div>
                                <div class="rounded-10 h-[40px] w-[250px] flex bg-lightGrey"></div>
                            </div>

                            <div class="password | flex flex-col gap-2">
                                <div class="rounded-10 h-[40px] w-[250px] flex bg-lightGrey"></div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4 col-span-1 ">
                            <div class="subjects | flex flex-col gap-2">
                                <span class="h-6 w-[100px]"><x-knightrider /></span>
                                <span class="h-6"><x-knightrider /></span>
                            </div>
                            <div class="school-locations | flex flex-col gap-2">
                                <span class="h-6 w-[100px]"><x-knightrider /></span>
                                <span class="h-6"><x-knightrider /></span>
                            </div>
                            <div class="classes | flex flex-col gap-2">
                                <span class="h-6 w-[100px]"><x-knightrider /></span>
                                <span class="h-6"><x-knightrider /></span>
                            </div>
                        </div>
                    </x-animations.loading-fade>
                </div>
            </div>
        </div>
        {{-- End tab 'Account'--}}

        {{-- Tab 'Tests'--}}
        <div class="flex flex-col gap-8" x-show="openTab === 'tests'">
            <div class="flex flex-col items-center w-full">
                <h3 class="semi-bold">@lang('account.test_header_info_text')</h3>
                {{--                <span class="text-sm">@lang('account.test_header_info_subtext') {{ $this->locationName }}</span>--}}
            </div>

            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Constructie')</h2>

                <div class="content-section relative p-10 grid grid-cols-1 lg:grid-cols-2 gap-x-6 w-full">
                    <div class="default-lang | flex flex-col gap-1 border-b border-bluegrey">
                        <div class="flex items-center justify-between">
                            <span>@lang('account.Standaard taal')</span>
                            <x-tooltip>@lang('account.Standaard taal tooltip')</x-tooltip>
                        </div>
                        <x-input.group class="mb-[7px]">
                            <x-input.select wire:model="featureSettings.wsc_default_language">
                                @foreach($this->wscLanguages as $key => $language)
                                    <x-input.option :value="$key" :label="$language" />
                                @endforeach
                            </x-input.select>
                        </x-input.group>
                    </div>

                    <div class="border-b lg:border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5 self-end">
                        <x-input.toggle wire:model="featureSettings.question_publicly_available" class="mr-2" />
                        <x-icon.preview />
                        <span class="bold">@lang('cms.Openbaar maken')</span>
                    </div>
                    <div class="border-b border-bluegrey flex w-full items-center h-[50px] gap-2.5 self-end">
                        <x-input.toggle wire:model="featureSettings.wsc_copy_subject_language" class="mr-2" />
                        <x-icon.text-align-left />
                        <span class="bold">@lang('account.Taal van taalvak overnemen')</span>
                    </div>
                    <div class="border-b border-bluegrey flex w-full items-center h-[50px] gap-2.5 self-end">
                        <x-input.toggle wire:model="featureSettings.question_auto_score_completion" class="mr-2" />
                        <x-icon.autocheck />
                        <span class="bold">@lang('account.Gatentektst vragen automatisch nakijken')</span>
                    </div>

                    <div class="flex justify-between items-center border-b border-bluegrey h-[50px]">
                        <div class="flex gap-2 items-center">
                            <x-icon.points />
                            <span class="bold">@lang('account.Aantal punten per vraag')</span>
                        </div>
                        <div class="flex gap-2 items-center">
                            <x-input.text wire:model="featureSettings.question_default_points"
                                          class="text-center w-[3.375rem]"
                                          :only-integer="true"
                            />
                        </div>
                    </div>

                    <div class="border-b border-bluegrey flex w-full items-center h-[50px] gap-2.5 self-end">
                        <x-input.toggle wire:model="featureSettings.question_half_points_possible" class="mr-2" />
                        <x-icon.half-points />
                        <span class="bold">@lang('cms.Halve puntenbeoordeling mogelijk')</span>
                    </div>
                </div>
            </div>


            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Schrijf op')</h2>           
                <div class="content-section relative p-10 grid grid-cols-1 lg:grid-cols-2 gap-x-6 w-full">
                    @if(settings()->canUseCmsWscWriteDownToggle())
                    <div class="border-b lg:border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5 self-end">
                        <x-input.toggle wire:model="featureSettings.spell_check_available_default" class="mr-2" />
                        <x-icon.spellcheck class="min-w-[1rem]" />
                    <span class="bold">@lang('cms.spell_check_available')</span>
                    </div>
                    
                    @endif
                    <div class="border-b lg:border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5 self-end">
                        <x-input.toggle wire:model="featureSettings.mathml_functions_default" class="mr-2" />
                        <x-icon.math-equation class="min-w-[1rem]" />
                        <span class="bold">@lang('cms.mathml_functions')</span>
                    </div>
                    <div class="flex justify-between lg:border-t items-center border-b border-bluegrey h-[50px]">
                        <div class="flex gap-2 items-center">
                            <x-input.toggle wire:model="featureSettings.restrict_word_amount_default" class="mr-2" />
                            <x-icon.text-align-left class="min-w-[1rem]" />
                            <span class="bold">@lang('cms.restrict_word_amount')</span>
                        </div>
                        <div class="flex gap-2 items-center">
                            <x-input.text wire:model="featureSettings.max_words_default"
                                            class="text-center w-[3.375rem]"
                                            :only-integer="true"
                            />
                        </div>
                    </div>
                    <div class="border-b lg:border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5 self-end">
                        <x-input.toggle wire:model="featureSettings.text_formatting_default" class="mr-2" />
                        <x-icon.font class="min-w-[1rem]" />
                        <span class="bold">@lang('cms.text_formatting')</span>
                    </div>
                </div>
            </div>
            

            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Afname')</h2>

                <div class="content-section relative p-10 grid grid-cols-1 lg:grid-cols-2 gap-x-6 w-full">
                    <div class="border-b border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle wire:model="featureSettings.test_take_browser_testing" class="mr-2" />
                        <x-icon.web />
                        <span class="bold">@lang('teacher.Browsertoetsen toestaan')</span>
                    </div>
                    <div class="border-b lg:border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle wire:model="featureSettings.test_take_notify_students" class="mr-2" />
                        <x-icon.send-mail />
                        <span class="bold">@lang('account.Studenten informeren via mail')</span>
                    </div>
                    <div class="border-b border-bluegrey flex w-full items-center h-[50px] gap-2.5 self-end">
                        <x-input.toggle wire:model="featureSettings.test_take_test_direct" class="mr-2" />
                        <x-icon.test-direct />
                        <span class="bold">@lang('teacher.Test-Direct toestaan')</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-bluegrey h-[50px]">
                        <div class="flex gap-2 items-center">
                            <x-icon.points />
                            <span class="bold">@lang('account.Weging van de toets')</span>
                        </div>
                        <div class="flex gap-2 items-center">
                            <x-input.text wire:model="featureSettings.test_take_default_weight"
                                          class="text-center w-[3.375rem]"
                                          :only-integer="true"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Nakijken')</h2>

                <div class="content-section relative p-10 grid grid-cols-1 lg:grid-cols-2 gap-x-6 w-full">
                    <div class="border-b border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle wire:model="featureSettings.assessment_skip_no_discrepancy_answer"
                                        class="mr-2 min-w-[var(--switch-width)]" />
                        <x-icon.co-learning class="min-w-[1rem]" />
                        <span class="bold inline-flex flex-shrink-1">@lang('account.Antwoorden met CO-Learning score overslaan')</span>
                        <div class="min-w-min">
                            <x-tooltip>@lang('assessment.discrepancies_toggle_tooltip')</x-tooltip>
                        </div>
                    </div>
                    <div class="border-b lg:border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle wire:model="featureSettings.assessment_show_student_names" class="mr-2" />
                        <x-icon.profile />
                        <span class="bold">@lang('assessment.Studentnamen tonen')</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Inzien')</h2>

                <div class="content-section relative p-10 grid grid-cols-1 lg:grid-cols-2 gap-x-6 w-full">
                    <div class="border-b border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle wire:model="featureSettings.review_show_grades" class="mr-2" />
                        <x-icon.grade />
                        <span class="bold">@lang('account.Cijfers tonen')</span>
                    </div>
                    <div class="border-b lg:border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle wire:model="featureSettings.review_show_correction_model" class="mr-2" />
                        <x-icon.discuss />
                        <span class="bold">@lang('account.Antwoordmodel tonen')</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Becijferen en normeren')</h2>

                <div class="content-section relative p-10 grid grid-cols-1 lg:grid-cols-2 gap-x-6 w-full">
                    <div class="default-lang | flex flex-col gap-1">
                        <div class="flex items-center justify-between">
                            <span>@lang('account.Standaard normering')</span>
                        </div>
                        <div class="flex items-center w-full gap-2">
                            <x-input.group class="flex-1">
                                <x-input.select wire:model="featureSettings.grade_default_standard">
                                    @foreach($this->gradingStandards as $key => $standard)
                                        <x-input.option :value="$key"
                                                        :label="$standard"
                                        />
                                    @endforeach
                                </x-input.select>
                            </x-input.group>
                            <x-input.group>
                                <x-input.text wire:model="featureSettings.grade_standard_value"
                                              class="text-center w-[3.375rem]"
                                />
                            </x-input.group>
                            <x-input.group>
                                <x-input.text wire:model="featureSettings.grade_cesuur_percentage"
                                              :disabled="$this->featureSettings['grade_default_standard'] !== 'cesuur'"
                                              class="text-center w-[3.375rem]"
                                />
                            </x-input.group>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- End tab 'Tests'--}}
    </div>
</main>