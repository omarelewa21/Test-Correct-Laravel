<div id="login-body" class="flex justify-center items-center min-h-screen"
     x-data=""
     x-init="addRelativePaddingToBody('login-body', 10)"
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('login-body')"
     wire:ignore.self
>
    <div class="w-full max-w-3xl space-y-4 mx-4 py-4">
        @if($this->loginTab)
        <div class="content-section p-10 space-y-5 shadow-xl flex flex-col " style="min-height: 550px">
            <div class="flex items-center space-x-2.5">
                <div class="flex">
                    <x-stickers.login/>
                </div>
                <div>
                    <h1>{{ __('auth.login_as_student') }}</h1>
                </div>

            </div>

            <div class="flex flex-col flex-1" x-data="{openTab: 1, showPassword: false}">
                <div class="flex w-full space-x-6 mb-5 border-b border-light-grey">
                    <div :class="{'border-b-2 border-primary -mb-px' : openTab === 1}">
                        <x-button.text-button class="primary"
                                              @click="openTab = 1">{{ __('auth.log_in_verb') }}</x-button.text-button>
                    </div>
                    <div class="" :class="{'border-b-2 border-primary -mb-px' : openTab === 2}">
                        <x-button.text-button class="primary"
                                              @click="openTab = 2">{{ __('auth.log_in_with_temporary_student_login') }}</x-button.text-button>
                    </div>
                </div>

                <div class="flex flex-col flex-1" x-show="openTab === 1">
                    <form wire:submit.prevent="login" action="#" method="POST" class="flex-col flex flex-1">
                        <div class="flex flex-col md:flex-row space-y-4 md:space-x-4 md:space-y-0">
                            <x-input.group label="{{ __('auth.emailaddress')}}" class="flex-1">
                                <x-input.text wire:model="username" autofocus></x-input.text>
                            </x-input.group>
                            <x-input.group label="{{ __('auth.password')}}" class="flex-1 relative">
                                <x-input.text wire:model.lazy="password"
                                              x-bind:type="showPassword ? 'text' : 'password'"
                                              class="pr-12 overflow-ellipsis"
                                >
                                </x-input.text>
                                <x-icon.preview class="absolute bottom-3 right-3.5 primary-hover cursor-pointer"
                                                @click="showPassword = !showPassword"/>
                            </x-input.group>
                        </div>
                        <div class="error-section">
                            @error('username')
                            <div class="notification error stretched mt-4">
                                <span class="title">{{ $message }}</span>
                            </div>
                            @enderror
                            @error('password')
                            <div class="notification error stretched mt-4">
                                <span class="title">{{ $message }}</span>
                            </div>
                            @enderror
                            @error('invalid_user')
                            <div class="notification error stretched mt-4">
                                <div class="flex items-center space-x-3">
                                    <x-icon.exclamation/>
                                    <span class="title">{{ __('auth.incorrect_credentials') }}</span>
                                </div>
                                <span class="body">{{ __('auth.incorrect_credentials_long') }}</span>
                            </div>
                            @enderror
                            @if($requireCaptcha)
                                <div x-on:refresh-captcha.window="$refs.captcha.firstElementChild.setAttribute('src','/captcha/image?_=1333294957&_='+Math.random());">
                                    <div class="notification error stretched mt-4">
                                        <div class="flex items-center space-x-3">
                                            <x-icon.exclamation/>
                                            <span class="title">{{ __('auth.require_captcha') }}</span>
                                        </div>
                                        <span class="body">{{ __('auth.require_captcha_long') }}</span>
                                    </div>
                                    <div class="mt-2 inline-flex flex-col items-center space-y-1">
                                        <div x-ref="captcha" wire:ignore>
                                            @captcha
                                        </div>
                                        <input type="text" id="captcha"
                                               class="form-input @error('captcha') border-all-red @enderror"
                                               name="captcha"
                                               wire:model="captcha" autocomplete="off" style="width: 180px"/>
                                    </div>
                                </div>
                            @endif
                            @error('captcha')
                            <span class="text-sm all-red">{{ __('auth.incorrect_captcha') }}</span>
                            @enderror
                        </div>

                        <div class="">
                            <div class="mx-auto mt-4 flex flex-col items-center"
                                 x-data="{selected:null, tooltip: false}">
                                <div class="w-full flex items-center">
                                    <x-icon.arrow/>

                                    <span class="bold ml-2 mr-4">{{ __('auth.go_to_test_directly') }}</span>

                                    <div class="flex relative justify-center items-center mr-2 base bg-blue-grey rounded-full "
                                         style="width: 22px; height: 22px"
                                         x-on:mouseenter="tooltip = true"
                                         x-on:mouseleave="tooltip = false"
                                    >
                                        <x-icon.questionmark class="transform scale-75"/>
                                        <div class="absolute p-4 top-8 rounded-10 bg-off-white w-60 z-10 shadow-lg"
                                             x-cloak x-show.transition="tooltip">
                                            <p>Turducken shankle t-bone pancetta chicken. Pork belly ham hock leberkas
                                                frankfurter turducken hamburger</p>
                                        </div>
                                    </div>

                                    <label class="switch">
                                        <input type="checkbox" @click="selected !== 1 ? selected = 1 : selected = null">
                                        <span class="slider round"></span>
                                    </label>
                                </div>

                                <div class="w-full relative overflow-hidden transition-all max-h-0 duration-500"
                                     x-ref="container1"
                                     :style="selected == 1 ? 'max-height: ' + $refs.container1.scrollHeight + 'px' : ''"
                                >
                                    <x-partials.test-take-code/>
                                </div>
                            </div>

                        </div>
                        <div class="flex mt-auto pt-4">
                            <x-button.text-button wire:click="$set('loginTab', false)">
                                <span class="text-base">{{__('auth.forgot_password_long')}}</span>
                                <x-icon.arrow/>
                            </x-button.text-button>
                            <x-button.cta class="ml-auto" size="md">
                                <span>{{ __('auth.log_in_verb') }}</span>
                            </x-button.cta>
                        </div>
                    </form>
                </div>

                <div class="flex flex-col flex-1" x-show="openTab === 2" x-cloak>

                    <form wire:submit.prevent="login_guest" action="#" method="POST" class="flex-col flex flex-1">
                        <div class="flex flex-col md:flex-row space-y-4 md:space-x-4 md:space-y-0">
                            <x-input.group label="{{ __('auth.first_name')}}" class="w-56">
                                <x-input.text wire:model="firstName" autofocus></x-input.text>
                            </x-input.group>
                            <x-input.group label="{{ __('auth.suffix')}}" class="w-28">
                                <x-input.text wire:model="firstName" autofocus></x-input.text>
                            </x-input.group>
                            <x-input.group label="{{ __('auth.last_name')}}" class="flex-1">
                                <x-input.text wire:model="firstName" autofocus></x-input.text>
                            </x-input.group>
                        </div>
                        <div class="error-section">
                            @error('')

                            @enderror
                        </div>

                        <div class="">
                            <div class="mx-auto mt-4 flex flex-col">
                                <x-partials.test-take-code/>
                            </div>
                        </div>
                        <div class="flex mt-auto pt-4">
                            <x-button.cta class="ml-auto" size="md">
                                <span>{{ __('auth.log_in_verb') }}</span>
                            </x-button.cta>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @else
        <div class="content-section p-10 space-y-5 shadow-xl flex flex-col " style="min-height: 550px">
            <div class="flex items-center space-x-2.5">
                <div class="flex">
                    <x-stickers.login/>
                </div>
                <div>
                    <h1>{{ __('auth.forgot_password') }}</h1>
                </div>
            </div>
            <div class="flex flex-col flex-1 h-full">
                <p class="mb-6">We hebben we het e-mailadres nodig dat geassocieerd is met jouw account om een mail te kunnen sturen over het opnieuw instellen van het wachtwoord.</p>
                <x-input.group label="E-mailadres">
                    <x-input.text wire:model="forgotPasswordEmail"/>
                </x-input.group>
                <div class="mt-auto flex w-full">
                    <x-button.cta class="ml-auto" size="md" disabled>
                        <span>{{ __('auth.send_email') }}</span>
                    </x-button.cta>
                </div>
            </div>

        </div>
        @endif
        <div class="flex flex-col md:flex-row justify-center items-center md:space-x-4">
            <x-button.primary type="link" href="https://www.test-correct.nl/student/">
                <x-icon.download/>
                <span>{{__('auth.download_student_app')}}</span>
            </x-button.primary>
            <h5 class="hidden inline-flex mt-2 md:mt-0">&amp;</h5>
            <x-button.text-button class="hidden">
                <span>{{__('auth.request_account_from_teacher')}}</span>
                <x-icon.arrow/>
            </x-button.text-button>
        </div>
    </div>
</div>
