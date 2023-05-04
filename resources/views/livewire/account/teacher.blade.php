<main class="account-page"
      x-data="{openTab: 'account', shimmer: false}"
      x-cloak
      x-on:updated-language.window="$nextTick(() => setTimeout(() => shimmer = false, 5000))"
>
    <header class="sticky flex flex-col justify-center top-0 w-full z-10">
        <div class="flex w-full px-4 items-center justify-center relative text-white z-10 h-[var(--header-height)] bg-gradient-to-r from-[var(--teacher-primary)] to-[var(--teacher-primary-light)] main-shadow">
            <h4 class="text-white">@lang('account.account') @lang('account.settings')</h4>

            <div class="absolute right-4 ">
                <div class="flex items-center justify-center min-w-[40px] w-10 h-10 rounded-full bg-white/20 hover:scale-105 transition-transform cursor-pointer"
                     wire:click="redirectBack()"
                >
                    <x-icon.close />
                </div>
            </div>
        </div>

        <x-menu.tab.container class="w-full" :withTileEvents="true" max-width-class="max-w-[1040px] px-5">
            <x-menu.tab.item tab="account" menu="openTab">@lang('account.account')</x-menu.tab.item>
            <x-menu.tab.item tab="tests" menu="openTab">@lang('header.Toetsen')</x-menu.tab.item>
        </x-menu.tab.container>
    </header>

    <div class="mx-auto max-w-[1040px] py-10 px-5 z-1">
        {{-- Tab 'Account'--}}
        <div class="flex flex-col gap-8" x-show="openTab === 'account'">
            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">Test-Correct @lang('account.settings')</h2>
                <div class="content-section p-10 grid grid-cols-1 lg:grid-cols-2 gap-6 w-full">
                    <div class="system-lang | flex flex-col">
                        <x-input.group :label="__('account.Systeem taal')">
                            <x-input.select wire:model="featureSettings.system_language"
                                            x-on:change="shimmer = true"
                            >
                                @foreach($this->systemLanguages as $key => $language)
                                    <option value="{{ $key }}"
                                            wire:key="system-language-option-{{ $key }}">{{ $language }}</option>
                                @endforeach
                            </x-input.select>
                        </x-input.group>
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
                </div>
            </div>
            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Jouw profiel') - @lang('account.docent account')</h2>

                <div class="content-section p-10 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @unless($this->canEditProfile)
                        <div class="col-span-1 lg:col-span-2">
                            <div class="notification info stretched">
                                <div class="title">
                                    @if($this->editRestriction === 'lvs')
                                        <x-icon.questionmark />
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
                                <x-input.group :label="__('onboarding.Tussenv.')">
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
                            <x-input.group label="{{ __('account.Jouw') }} {{  str(__('staff.Wachtwoord'))->lower() }}"
                                           class="flex-1 relative"
                            >
                                <x-input.text class="w-full pr-10 dotsfont"
                                              type="text"
                                              value="PASSWORD"
                                              disabled
                                />

                                <div class="absolute bottom-[9px] right-3 cursor-pointer">
                                    <x-icon.preview-off x-show="!showPassword" class="text-midgrey" />
                                </div>
                            </x-input.group>
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
                </div>
            </div>
        </div>
        {{-- End tab 'Account'--}}

        {{-- Tab 'Tests'--}}
        <div class="flex flex-col gap-8" x-show="openTab === 'tests'">
            <div class="flex flex-col items-center w-full">
                <h3 class="semi-bold">@lang('account.test_header_info_text')</h3>
                <span class="text-sm">@lang('account.test_header_info_subtext')</span>
            </div>

            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Constructie')</h2>

                <div class="content-section p-10 grid grid-cols-1 lg:grid-cols-2 gap-x-6 w-full">
                    <div class="default-lang | flex flex-col gap-1 border-b border-bluegrey">
                        <div class="flex items-center justify-between">
                            <span>@lang('account.Standaard taal')</span>
                            <x-tooltip>@lang('account.Standaard taal tooltip')</x-tooltip>
                        </div>
                        <x-input.group class="mb-[7px]">
                            <x-input.select wire:model="featureSettings.wsc_default_language">
                                @foreach($this->wscLanguages as $key => $language)
                                    <option value="{{ $key }}"
                                            wire:key="wsc-language-option-{{ $key }}">{{ $language }}</option>
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
                        <x-icon.questionmark />
                        <span class="bold">@lang('account.Neem taal over van taalvak')</span>
                    </div>
                    <div class="border-b border-bluegrey flex w-full items-center h-[50px] gap-2.5 self-end">
                        <x-input.toggle wire:model="featureSettings.question_auto_score_completion" class="mr-2" />
                        <x-icon.autocheck />
                        <span class="bold">@lang('account.Automatisch nakijken gatentektst vragen')</span>
                    </div>

                    <div class="flex justify-between items-center border-b border-bluegrey h-[50px]">
                        <div class="flex gap-2 items-center">
                            <x-icon.questionmark />
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
                <h2 class="flex">@lang('account.Afname')</h2>

                <div class="content-section p-10 grid grid-cols-1 lg:grid-cols-2 gap-x-6 w-full">
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
                            <x-icon.questionmark />
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

                <div class="content-section p-10 grid grid-cols-1 lg:grid-cols-2 gap-x-6 w-full">
                    <div class="border-b border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle wire:model="featureSettings.assessment_skip_no_discrepancy_answer"
                                        class="mr-2" />
                        <x-icon.co-learning />
                        <span class="bold">@lang('account.Sla antwoorden over die met CO-Learning zijn beoordeeld')</span>
                    </div>
                    <div class="border-b lg:border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle wire:model="featureSettings.assessment_show_student_names" class="mr-2" />
                        <x-icon.profile />
                        <span class="bold">@lang('assessment.Toon de naam van studenten')</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Inzien')</h2>

                <div class="content-section p-10 grid grid-cols-1 lg:grid-cols-2 gap-x-6 w-full">
                    <div class="border-b border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle wire:model="featureSettings.review_show_grades" class="mr-2" />
                        <x-icon.grade />
                        <span class="bold">@lang('account.Cijfers tonen')</span>
                    </div>
                    <div class="border-b lg:border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle wire:model="featureSettings.review_show_correction_model" class="mr-2" />
                        <x-icon.discuss />
                        <span class="bold">@lang('account.Toon antwoordmodel')</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Becijferen en normeren')</h2>

                <div class="content-section p-10 grid grid-cols-1 lg:grid-cols-2 gap-x-6 w-full">
                    <div class="default-lang | flex flex-col gap-1">
                        <div class="flex items-center justify-between">
                            <span>@lang('account.Standaard normering')</span>
                        </div>
                        <div class="flex items-center w-full gap-2">
                            <x-input.group class="flex-1">
                                <x-input.select wire:model="featureSettings.grade_default_standard">
                                    @foreach($this->gradingStandards as $key => $standard)
                                        <option value="{{ $key }}"
                                                wire:key="grading-standard-option-{{ $key }}">{{ $standard }}</option>
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