<main class=""
      x-data="{openTab: 'tests'}"
      x-cloak
>
    <header class="sticky flex flex-col justify-center top-0 w-full z-10">
        <div class="flex w-full px-4 items-center justify-center relative text-white z-10 h-[var(--header-height)] bg-gradient-to-r from-[var(--teacher-primary)] to-[var(--teacher-primary-light)] main-shadow">
            <h4 class="text-white">@lang('account.account') @lang('account.settings')</h4>

            <div class="absolute right-4 ">
                <div class="flex items-center justify-center min-w-[40px] w-10 h-10 rounded-full bg-white/20 hover:scale-105 transition-transform"
                     wire:click="redirectBack()"
                >
                    <x-icon.close />
                </div>
            </div>
        </div>

        <x-menu.tab.container class="w-full" :withTileEvents="true" max-width-class="max-w-[1020px]">
            <x-menu.tab.item tab="account" menu="openTab">@lang('account.account')</x-menu.tab.item>
            <x-menu.tab.item tab="tests" menu="openTab">@lang('header.Toetsen')</x-menu.tab.item>
        </x-menu.tab.container>
    </header>

    <div class="mx-auto max-w-[1020px] py-10 z-1">
        <div class="flex flex-col gap-8" x-show="openTab === 'account'">
            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">Test-Correct @lang('account.settings')</h2>
                <div class="content-section p-10 grid grid-cols-2 gap-6 w-full">
                    <div class="system-lang | flex flex-col">

                        <x-input.group :label="__('account.Systeem taal')">
                            <x-input.select>
                                <option>Nederlands</option>
                                <option>Engels</option>
                            </x-input.select>
                        </x-input.group>
                    </div>

                    <div class="auto-logout | self-end">
                        <div class="border-b border-t border-bluegrey flex w-full justify-between items-center h-[50px]">
                            <div class="flex items-center gap-2.5">
                                <x-input.toggle class="mr-2" />
                                <x-icon.locked />
                                <span class="bold">@lang('account.Automatisch uitloggen na')</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-input.text class="text-center w-[3.375rem]"
                                              :only-integer="true"
                                              max="120"
                                              min="15"
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

                <div class="content-section p-10 grid grid-cols-2 gap-6 w-full">
                    <div class="col-span-2 notification info stretched">
                        <div class="title">
                            <x-icon.entreefederatie />
                            <span>Jouw profiel shit</span>
                        </div>
                        <div class="body">Irri text</div>
                    </div>

                    <div class="flex flex-col gap-4">
                        <div class="gender | flex gap-4 flex-wrap"
                             x-data="{gender: 'male'}">
                            <div class="flex space-x-2 items-center hover:text-primary transition cursor-pointer"
                                 @click="gender = 'male'"
                                 :class="gender === 'male' ? 'primary bold' : 'text-midgrey'"
                            >
                                <div class="flex">
                                    <x-icon.man class="text-inherit" />
                                </div>
                                <span class="flex">@lang('account.Dhr').</span>
                            </div>
                            <div class="flex space-x-2 items-center hover:text-primary transition cursor-pointer"
                                 @click="gender = 'female'"
                                 :class="gender === 'female' ? 'primary bold' : 'text-midgrey'"
                            >
                                <div class="flex">
                                    <x-icon.woman class="text-inherit" />
                                </div>
                                <span class="flex">@lang('account.Mevr').</span>
                            </div>
                            <div class="flex space-x-2 items-center hover:text-primary transition cursor-pointer"
                                 @click="gender = 'different'; $nextTick(() => $el.querySelector('input').focus())"
                                 :class="gender === 'different' ? 'primary bold' : 'text-midgrey'"
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
                                       wire:model.lazy="registration.gender_different"
                                       class="form-input flex"
                                       style="width: 128px;"
                                       :disabled="gender !== 'different'"
                                       :class="gender !== 'different' ? 'disabled' : ''"
                                >
                            </div>
                        </div>

                        <div class="naw | flex flex-col gap-4">
                            <div class="flex gap-4">
                                <x-input.group :label="__('onboarding.Voornaam')" class="flex-1">
                                    <x-input.text class="w-full" />
                                </x-input.group>
                                <x-input.group :label="__('onboarding.Tussenv.')">
                                    <x-input.text class="w-20" />
                                </x-input.group>
                                <x-input.group :label="__('onboarding.Achternaam')" class="flex-1">
                                    <x-input.text class="w-full" />
                                </x-input.group>
                            </div>
                            <x-input.group :label="__('onboarding.your_school_email')" class="flex-1">
                                <x-input.text class="w-full" disabled />
                            </x-input.group>
                        </div>

                        <div class="subjects | flex flex-col gap-2">
                            <span>@lang('onboarding.Jouw vak(ken)')</span>
                            <span class="bold">Aardrijkskunde, Wiskunde</span>
                        </div>
                        <div class="school-locations | flex flex-col gap-2">
                            <span>@lang('onboarding.Jouw schoollocaties')</span>
                            <span class="bold">Locatie A, locatie B</span>
                        </div>
                        <div class="classes | flex flex-col gap-2">
                            <span>@lang('account.Jouw') {{ str(__('teacher.Klassen'))->lower() }}</span>
                            <span class="bold">klas A, klas B</span>
                        </div>

                    </div>

                    <div class="flex flex-col gap-4">
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
                                <x-input.text class="w-full pr-10"
                                              type="password"
                                              value="PASSWORD"
                                              disabled
                                />

                                <div class="absolute bottom-[9px] right-3 cursor-pointer">
                                    <x-icon.preview-off x-show="!showPassword" class="text-midgrey" />
                                </div>
                            </x-input.group>
                            <div>
                                <x-button.primary class="">
                                    <x-icon.edit />
                                    <span>@lang('header.Wachtwoord wijzigen')</span>
                                </x-button.primary>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="flex flex-col gap-8" x-show="openTab === 'tests'">
            <div class="flex flex-col items-center w-full">
                <h3 class="font-semibold">@lang('account.test_header_info_text')</h3>
                <span class="text-sm">@lang('account.test_header_info_subtext')</span>
            </div>

            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Constructie')</h2>

                <div class="content-section p-10 grid grid-cols-2 gap-x-6 w-full">
                    <div class="default-lang | flex flex-col gap-1">
                        <div class="flex items-center justify-between">
                            <span>@lang('account.Standaard taal')</span>
                            <x-tooltip>@lang('account.Standaard taal tooltip')</x-tooltip>
                        </div>
                        <x-input.group class="mb-[7px]">
                            <x-input.select>
                                <option>Nederlands</option>
                                <option>Engels</option>
                            </x-input.select>
                        </x-input.group>
                    </div>

                    <div class="border-b border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5 self-end">
                        <x-input.toggle class="mr-2" />
                        <x-icon.preview />
                        <span class="bold">@lang('cms.Openbaar maken')</span>
                    </div>
                    <div class="border-b border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5 self-end">
                        <x-input.toggle class="mr-2" />
                        <x-icon.questionmark />
                        <span class="bold">@lang('account.Neem taal over van taalvak')</span>
                    </div>
                    <div class="border-b border-bluegrey flex w-full items-center h-[50px] gap-2.5 self-end">
                        <x-input.toggle class="mr-2" />
                        <x-icon.autocheck />
                        <span class="bold">@lang('account.Automatisch nakijken gatentektst vragen')</span>
                    </div>

                    <div class="flex justify-between items-center border-b border-bluegrey h-[50px]">
                        <div class="flex gap-2 items-center">
                            <x-icon.questionmark />
                            <span class="bold">@lang('account.Aantal punten per vraag')</span>
                        </div>
                        <div class="flex gap-2 items-center">
                            <x-input.text class="text-center w-[3.375rem]"
                                          :only-integer="true"
                            />
                        </div>
                    </div>

                    <div class="border-b border-bluegrey flex w-full items-center h-[50px] gap-2.5 self-end">
                        <x-input.toggle class="mr-2" />
                        <x-icon.half-points />
                        <span class="bold">@lang('cms.Halve puntenbeoordeling mogelijk')</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Afname')</h2>

                <div class="content-section p-10 grid grid-cols-2 gap-x-6 w-full">
                    <div class="border-b border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle class="mr-2" />
                        <x-icon.web />
                        <span class="bold">@lang('account.Browsertoetsen toestaan')</span>
                    </div>
                    <div class="border-b border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle class="mr-2" />
                        <x-icon.send-mail />
                        <span class="bold">@lang('account.Studenten informeren via mail')</span>
                    </div>
                    <div class="border-b border-bluegrey flex w-full items-center h-[50px] gap-2.5 self-end">
                        <x-input.toggle class="mr-2" />
                        <x-icon.test-direct />
                        <span class="bold">@lang('account.Test-Direct toestaan')</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-bluegrey h-[50px]">
                        <div class="flex gap-2 items-center">
                            <x-icon.questionmark />
                            <span class="bold">@lang('account.Weging van de toets')</span>
                        </div>
                        <div class="flex gap-2 items-center">
                            <x-input.text class="text-center w-[3.375rem]"
                                          :only-integer="true"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Nakijken')</h2>

                <div class="content-section p-10 grid grid-cols-2 gap-x-6 w-full">
                    <div class="border-b border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle class="mr-2" />
                        <x-icon.co-learning />
                        <span class="bold">@lang('Kaas')</span>
                    </div>
                    <div class="border-b border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle class="mr-2" />
                        <x-icon.profile />
                        <span class="bold">@lang('assessment.Toon de naam van studenten')</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Inzien')</h2>

                <div class="content-section p-10 grid grid-cols-2 gap-x-6 w-full">
                    <div class="border-b border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle class="mr-2" />
                        <x-icon.grade />
                        <span class="bold">@lang('account.Cijfers tonen')</span>
                    </div>
                    <div class="border-b border-t border-bluegrey flex w-full items-center h-[50px] gap-2.5">
                        <x-input.toggle class="mr-2" />
                        <x-icon.discuss />
                        <span class="bold">@lang('account.Toon antwoordmodel')</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col w-full gap-4">
                <h2 class="flex">@lang('account.Becijferen en normeren')</h2>

                <div class="content-section p-10 grid grid-cols-2 gap-x-6 w-full">
                    <div class="default-lang | flex flex-col gap-1">
                        <div class="flex items-center justify-between">
                            <span>@lang('account.Standaard taal')</span>
                            <x-tooltip>@lang('account.Standaard taal tooltip')</x-tooltip>
                        </div>
                        <div class="flex items-center w-full gap-2">
                            <x-input.group class="flex-1">
                                <x-input.select>
                                    <option>Nederlands</option>
                                    <option>Engels</option>
                                </x-input.select>
                            </x-input.group>
                            <x-input.group>
                                <x-input.text class="text-center w-[3.375rem]"/>
                            </x-input.group>
                            <x-input.group>
                                <x-input.text class="text-center w-[3.375rem]"/>
                            </x-input.group>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    be like water; {{ $this->userUuid }}
</main>