<div id="login-body" class="flex justify-center items-center min-h-screen"
     x-data="{ openTab: 1, showPassword: false, showEntreePassword: false }"
     x-init="
            addRelativePaddingToBody('login-body', 10);
            setTimeout(() => {$wire.checkLoginFieldsForInput()}, 250);
            "
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('login-body')"
     wire:ignore.self
>
    <div class="w-full max-w-[800px] space-y-4 mx-4 py-4">
        @if($tab == 'login')
            <div class="content-section p-10 space-y-5 shadow-xl flex flex-col " style="min-height: 550px">
                <div class="flex items-center space-x-2.5">
                    <div class="flex">
                        <x-stickers.login/>
                    </div>
                    <div>
                        <h1>{{ __('auth.log_in_verb') }}</h1>
                    </div>

                </div>

                <div class="flex flex-col flex-1">
                    <div class="flex w-full space-x-6 mb-5 border-b border-light-grey">
                        <div :class="{'border-b-2 border-primary -mb-px' : openTab === 1}">
                            <x-button.text-button class="primary"
{{--                                                  @click="openTab = 1"--}}
                            >
                                {{ __('auth.log_in_verb') }}
                            </x-button.text-button>
                        </div>
{{--                        <div class="" :class="{'border-b-2 border-primary -mb-px' : openTab === 2}">--}}
                        <div>
                            <x-button.text-button class="disabled" disabled
{{--                                                  @click="openTab = 2;"--}}
                            >
                                {{ __('auth.log_in_with_temporary_student_login') }}
                            </x-button.text-button>
                        </div>
                    </div>

                    <div class="flex flex-col flex-1" x-show="openTab === 1">
                        <form wire:submit.prevent="login" action="#" method="POST" class="flex-col flex flex-1">
                            <div class="flex flex-col md:flex-row space-y-4 md:space-x-4 md:space-y-0">
                                <x-input.group label="{{ __('auth.emailaddress')}}" class="flex-1">
                                    <x-input.text wire:model.lazy="username" autofocus></x-input.text>
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

                            <div class="hidden">
                                <div class="mx-auto mt-4 flex flex-col items-center"
                                     x-data="{showCode: @entangle('showTestCode'), tooltip: false}">
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
                                                <p>Tooltip tekst voor meteen naar toets gaan</p>
                                            </div>
                                        </div>

                                        <label class="switch">
                                            <input type="checkbox"
                                                   @click="!showCode ? showCode = true : showCode = false"
                                                   @if($showTestCode) checked="checked" @endif
                                            >
                                            <span class="slider round"></span>
                                        </label>
                                    </div>

                                    <div class="w-full relative overflow-hidden transition-all max-h-0 duration-200"
                                         x-ref="container1"
                                         :style="showCode ? 'max-height: ' + $refs.container1.scrollHeight + 'px' : ''"
                                    >
                                        <x-partials.test-take-code/>
                                    </div>
                                </div>

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
                                @if($this->message_brin == 'brin_not_found')
                                    <div class="notification error stretched mt-4">
                                        <div class="flex items-center space-x-3">
                                            <x-icon.exclamation/>
                                            <span class="title">{{ __('auth.brin_not_found') }}</span>
                                        </div>
                                        <span class="body">{{ __('auth.brin_not_found_long') }}</span>
                                    </div>
                                @endif
                                @error('should_first_go_to_entree')
                                <div class="notification error stretched mt-4">
                                    <div class="flex items-center space-x-3">
                                        <x-icon.exclamation/>
                                        <span class="title">{{ $message }}</span>
                                    </div>

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
                                @error('invalid_test_code')
                                <div class="notification error stretched mt-4">
                                    <span class="title">{{ $message }}</span>
                                </div>
                                @enderror
                            </div>
                            {{-- With forgot_password button, ml_auto can be switched justify-between on the parent --}}
                            <div class="flex mt-auto pt-4 justify-between">
                                <div class="flex order-2 space-x-4">
                                    <x-button.primary type="link" class="bg-[#2e3192]" size="md" href="{{ route('saml2_login', 'entree') }}">
                                        <x-icon.entreefederatie/>
                                        <span>{{ __('auth.login_with_entree') }}</span>
                                    </x-button.primary>
                                    <x-button.cta class="" size="md">
                                        <span>{{ __('auth.log_in_verb') }}</span>
                                    </x-button.cta>
                                </div>
                                <x-button.text-button class="order-1" wire:click.prevent="$set('tab', 'forgotPassword')">
                                    <span class="text-base">{{__('auth.forgot_password_long')}}</span>
                                    <x-icon.arrow/>
                                </x-button.text-button>
                            </div>
                        </form>
                    </div>

                    <div class="hidden flex flex-col flex-1" x-show="openTab === 2" x-cloak>

                        <form wire:submit.prevent="guestLogin" action="#" method="POST" class="flex-col flex flex-1">
                            <div class="flex flex-col md:flex-row space-y-4 md:space-x-4 md:space-y-0">
                                <x-input.group label="{{ __('auth.first_name')}}" class="w-56">
                                    <x-input.text wire:model="firstName" autofocus></x-input.text>
                                </x-input.group>
                                <x-input.group label="{{ __('auth.suffix')}}" class="w-28">
                                    <x-input.text wire:model="suffix" autofocus></x-input.text>
                                </x-input.group>
                                <x-input.group label="{{ __('auth.last_name')}}" class="flex-1">
                                    <x-input.text wire:model="lastName" autofocus></x-input.text>
                                </x-input.group>
                            </div>

                            <div class="">
                                <div class="mx-auto mt-4 flex flex-col">
                                    <x-partials.test-take-code/>
                                </div>
                            </div>

                            <div class="error-section">
                                @error('firstName')
                                <div class="notification error stretched mt-4">
                                    <span class="title">{{ $message }}</span>
                                </div>
                                @enderror
                                @error('lastName')
                                <div class="notification error stretched mt-4">
                                    <span class="title">{{ $message }}</span>
                                </div>
                                @enderror
                                @error('invalid_test_code')
                                <div class="notification error stretched mt-4">
                                    <span class="title">{{ $message }}</span>
                                </div>
                                @enderror

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
        @elseif($tab == 'forgotPassword')
            <div class="content-section p-10 space-y-5 shadow-xl flex flex-col " style="min-height: 550px">
                <form wire:submit.prevent="sendForgotPasswordEmail" action="#" method="POST"
                      class="flex-col flex flex-1">
                    <div class="flex items-center space-x-2.5 mb-5">
                        <div class="flex">
                            <x-stickers.login/>
                        </div>
                        <div>
                            <h1>{{ __('auth.forgot_password') }}</h1>
                        </div>
                    </div>
                    <div class="flex flex-col flex-1 h-full">
                        <p class="mb-4 body1">{{ __('auth.forgot_password_explain_text') }}</p>
                        <x-input.group label="{{ __('auth.emailaddress') }}">
                            <x-input.text wire:model.debounce.300ms="forgotPasswordEmail"/>
                        </x-input.group>
                        @if($showSendForgotPasswordNotification)
                            <div class="flex flex-col notification info stretched mt-4 px-6">
                                <div class="title flex items-center space-x-2">
                                    <x-icon.arrow/>
                                    <span>{{ __('auth.forgot_password_email_send') }}</span>
                                </div>
                                <div class="body">
                                    <span>{{ __('auth.forgot_password_email_send_text') }}</span>
                                    <div class="flex space-x-4">
                                        <x-button.text-button class="text-sm primary space-x-1" wire:click.prevent="sendForgotPasswordEmail()">
                                            <span>{{ __('auth.send_mail_again') }}</span>
                                            <x-icon.arrow-small/>
                                        </x-button.text-button>
                                        <x-button.text-button class="text-sm primary space-x-1" type="link" href="https://test-correct.nl/support" target="_blank">
                                            <span>{{ __('auth.find_support') }}</span>
                                            <x-icon.arrow-small/>
                                        </x-button.text-button>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="mt-auto flex w-full">
                            @if($forgotPasswordButtonDisabled)
                                <x-button.cta class="order-2 ml-auto" size="md" disabled>
                                    <span>{{ __('auth.send_email') }}</span>
                                </x-button.cta>
                            @else
                                <x-button.cta class="order-2 ml-auto" size="md">
                                    <span>{{ __('auth.send_email') }}</span>
                                </x-button.cta>
                            @endif
                            <x-button.text-button class="order-1 rotate-svg-180" wire:click.prevent="$set('tab', 'login')">
                                <x-icon.arrow/>
                                <span class="text-base">{{ __('auth.back_to_login') }}</span>
                            </x-button.text-button>
                        </div>
                    </div>
                </form>
            </div>
        @elseif($tab == 'entree')
            <div class="content-section p-10 space-y-5 shadow-xl flex flex-col " style="min-height: 550px">
                <form wire:submit.prevent="entreeForm" action="#" method="POST"
                      class="flex-col flex flex-1" autocomplete="off">
                    <div class="flex items-center space-x-2.5 mb-5">
                        <div class="flex">
                            <x-stickers.entreefederatie/>
                        </div>
                        <div>
                            <h1>{{ __('auth.connect_entree') }}</h1>
                        </div>
                    </div>
                    <div class="flex flex-col flex-1 h-full">
                        <p class="mb-4 body1">{{ __('auth.connect_entree_text') }}</p>
                        <div class="flex w-full space-x-4">
                            <x-input.group label="{{ __('auth.emailaddress') }}" class="flex-1 relative">
                                <x-input.text wire:model.lazy="entreeEmail" autocomplete="new-password"/>
                            </x-input.group>
                            <x-input.group label="{{ __('auth.password')}}" class="flex-1 relative">
                                <x-input.text wire:model.debounce.300ms="entreePassword"
                                              class="w-full pr-12 overflow-ellipsis transition-none" autocomplete="off"
                                              x-bind:class="{'dotsfont' : !showEntreePassword}"
                                >
                                </x-input.text>
                                <x-icon.preview class="absolute bottom-3 right-3.5 primary-hover cursor-pointer"
                                                @click="showEntreePassword = !showEntreePassword"/>
                            </x-input.group>
                        </div>
                        <div class="flex">
                            <x-button.text-button wire:click.prevent="$set('tab', 'forgotPassword')">
                                <span class="text-base">{{__('auth.forgot_password_long')}}</span>
                                <x-icon.arrow/>
                            </x-button.text-button>
                        </div>

                        <div class="mt-auto flex w-full">
                            <x-button.text-button class="rotate-svg-180" wire:click.prevent="$set('tab', 'login')">
                                <x-icon.arrow/>
                                <span class="text-base">{{ __('auth.back_to_login') }}</span>
                            </x-button.text-button>
                            @if($connectEntreeButtonDisabled)
                                <x-button.primary class="ml-auto" size="md" disabled>
                                    <x-icon.entreefederatie/>
                                    <span>{{ __('auth.make_connection') }}</span>
                                </x-button.primary>
                            @else
                                <x-button.primary class="ml-auto bg-[#2e3192]" size="md">
                                    <x-icon.entreefederatie/>
                                    <span>{{ __('auth.make_connection') }}</span>
                                </x-button.primary>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        @endif
        <div class="flex flex-col md:flex-row justify-center items-center md:space-x-4">
            <x-button.primary type="link" href="{{ $this->studentDownloadUrl }}">
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

    <x-modal.auth-create-account maxWidth="lg" wire:model="showAuthModal"/>
</div>
