<div id="login-body" class="flex justify-center items-center min-h-screen"
     x-data="{ openTab: @entangle('login_tab'), showPassword: false, hoverPassword: false, initialPreviewIconState: true, showEntreePassword: false, device: @entangle('device')}"
     x-init="
            addRelativePaddingToBody('login-body', 10);
            setTimeout(() => {$wire.checkLoginFieldsForInput()}, 250);
            "
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('login-body')"
     wire:ignore.self
>
    <div class="w-full max-w-[800px] mx-4 py-4">


        @if($tab == 'login')
            @if($showGuestSuccess)
                <div class="flex cta-gradient w-full p-10 -mb-4 rounded-t-10 relative top-2.5 space-x-2.5">
                    <div class="flex" x-data="">
                        <x-stickers.congratulations/>
                    </div>
                    <div class="flex flex-col text-white pt-4 space-y-2.5">
                        <h1 class="flex text-white">{{ __('auth.'.$guest_message) }}</h1>
                        <div class="flex space-x-2.5 items-center">
                            <x-icon.checkmark/>
                            <h5 class="text-white">{{ __('auth.'.$guest_message.'_sub') }}</h5>
                        </div>
                    </div>
                </div>
            @endif
            <div class="content-section p-10 mb-4 space-y-5 shadow-xl flex flex-col " style="min-height: 550px">
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
                                                  @click="openTab = 1"
                            >
                                {{ __('auth.log_in_verb') }}
                            </x-button.text-button>
                        </div>
                        <div class="" :class="{'border-b-2 border-primary -mb-px' : openTab === 2}">
                            <x-button.text-button class="primary"
                                                  @click="openTab = 2;"
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
                                    <div class="group"
                                         @mouseenter="hoverPassword = true"
                                         @mouseleave="hoverPassword = false"
                                         @click="showPassword = !showPassword; hoverPassword = false; initialPreviewIconState = false">
                                        <x-icon.preview-off class="absolute bottom-3 right-3.5 primary-hover cursor-pointer"
                                                            x-bind:class="{'opacity-50' : initialPreviewIconState, 'hover:text-sysbase': (!showPassword && !hoverPassword)}"
                                                            x-show="(!showPassword && !hoverPassword) || (showPassword && hoverPassword)"/>
                                        <div class="absolute bottom-3 right-3.5 flex items-center h-[16px]">
                                            <x-icon.preview class="primary-hover cursor-pointer"
                                                            x-bind:class="{'hover:text-sysbase': (showPassword && !hoverPassword)}"
                                                            x-show="(showPassword && !hoverPassword) || (!showPassword && hoverPassword)"/>
                                        </div>
                                    </div>
                                    <x-input.text wire:model.lazy="password"
                                                  x-bind:type="showPassword ? 'text' : 'password'"
                                                  class="pr-12 overflow-ellipsis"
                                    >
                                    </x-input.text>
                                </x-input.group>
                            </div>

                            <div class="hidden">
                                <div class="mx-auto mt-4 flex flex-col items-center"
                                     x-data="{showCode: @entangle('showTestCode'), tooltip: false}">
                                    <div class="w-full flex items-center">
                                        <x-icon.arrow/>

                                        <span class="bold ml-2 mr-4">{{ __('auth.go_to_test_directly') }}</span>

                                        <div
                                                class="flex relative justify-center items-center mr-2 base bg-blue-grey rounded-full "
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
                                @if($this->entree_error_message)
                                    <div class="notification error stretched mt-4">
                                        <div class="flex items-center space-x-3">
                                            <x-icon.exclamation/>
                                            <span class="title">{{ __('auth.entree_error') }}</span>
                                        </div>
                                        <span class="body">{{ __($this->entree_error_message) }}</span>
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
                                    <div
                                            x-on:refresh-captcha.window="$refs.captcha.firstElementChild.setAttribute('src','/captcha/image?_=1333294957&_='+Math.random());">
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
                                    <x-button.primary type="link" class="bg-[#2e3192]" size="md"
                                                      href="{{ route('saml2_login', 'entree') }}">
                                        <x-icon.entreefederatie/>
                                        <span>{{ __('auth.login_with_entree') }}</span>
                                    </x-button.primary>
                                    <x-button.cta class="" size="md">
                                        <span>{{ __('auth.log_in_verb') }}</span>
                                    </x-button.cta>
                                </div>
                                <x-button.text-button class="order-1"
                                                      wire:click.prevent="$set('tab', 'forgotPassword')">
                                    <span class="text-base">{{__('auth.forgot_password_long')}}</span>
                                    <x-icon.arrow/>
                                </x-button.text-button>
                            </div>
                        </form>
                    </div>

                    <div class="flex flex-col flex-1" x-show="openTab === 2" x-cloak>

                        <form wire:submit.prevent="guestLogin" action="#" method="POST" class="flex-col flex flex-1">
                            <div class="flex flex-col md:flex-row space-y-4 md:space-x-4 md:space-y-0">
                                <x-input.group label="{{ __('auth.first_name')}}" class="w-56">
                                    <x-input.text wire:model.lazy="firstName" autofocus></x-input.text>
                                </x-input.group>
                                <x-input.group label="{{ __('auth.suffix')}}" class="w-28">
                                    <x-input.text wire:model.lazy="suffix" autofocus></x-input.text>
                                </x-input.group>
                                <x-input.group label="{{ __('auth.last_name')}}" class="flex-1">
                                    <x-input.text wire:model.lazy="lastName" autofocus></x-input.text>
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
                                @error('no_test_found_with_code')
                                <div class="notification error stretched mt-4">
                                    <span class="title">{{ $message }}</span>
                                </div>
                                @enderror
                                @error('empty_guest_first_name')
                                <div class="notification error stretched mt-4">
                                    <span class="title">{{ $message }}</span>
                                </div>
                                @enderror
                                @error('empty_guest_last_name')
                                <div class="notification error stretched mt-4">
                                    <span class="title">{{ $message }}</span>
                                </div>
                                @enderror
                                @error('error_on_handling_guest_login')
                                <div class="notification error stretched mt-4">
                                    <span class="title">{{ $message }}</span>
                                </div>
                                @enderror
                                @error('name_already_in_use')
                                <div class="notification warning stretched mt-4">
                                    <span class="title">{{ __('auth.choose_a_different_name') }}</span>
                                    <span class="body">{{ __('auth.name_already_in_use') }}</span>
                                </div>
                                @enderror
                                @error('rating_visible_expired')
                                <div class="notification warning stretched mt-4">
                                    <span class="title">{{ __('auth.test_code_expired') }}</span>
                                    <span class="body">{{ __('auth.can_no_longer_log_in_to_this_test') }}</span>
                                </div>
                                @enderror
                                @error('test_take_not_in_valid_stage')
                                <div class="notification warning stretched mt-4">
                                    <span class="title">{{ __('auth.something_went_wrong') }}</span>
                                    <span class="body">{{ __('auth.test_for_this_code_is_not_valid_anymore_contact_teacher') }}</span>
                                </div>
                                @enderror
                                @error('user_not_found_for_test_code')
                                <div class="notification warning stretched mt-4">
                                    <span class="title">{{ __('auth.user_not_found_for_test_code') }}</span>
                                    <span class="body">{{ __('auth.contact_teacher_for_more_information') }}</span>
                                </div>
                                @enderror
                                @if($showGuestError)
                                    @if($guest_message == 'removed_by_teacher')
                                    <div class="notification warning stretched mt-4">
                                        <span class="title">{{ __('auth.log_in_again') }}</span>
                                        <span class="body">{{ __('auth.removed_by_teacher') }}</span>
                                    </div>
                                    @endif
                                    @if($guest_message == 'no_browser_testing')
                                        <div class="notification warning stretched mt-4">
                                            <span class="title">{{ __('auth.cannot_log_in_to_test_with_browser') }}</span>
                                            <span class="body">{{ __('auth.usage_of_app_is_required_for_this_test') }}</span>
                                            <a href="{{ $studentDownloadUrl }}" class="bold text-sm"
                                               target="_blank">{{ __("auth.download_and_install_the_app") }} <x-icon.arrow-small></x-icon.arrow-small></a>
                                        </div>
                                    @endif
                                @endif
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
                                        <x-button.text-button class="text-sm primary space-x-1"
                                                              wire:click.prevent="sendForgotPasswordEmail()">
                                            <span>{{ __('auth.send_mail_again') }}</span>
                                            <x-icon.arrow-small/>
                                        </x-button.text-button>
                                        <x-button.text-button class="text-sm primary space-x-1" type="link"
                                                              href="https://test-correct.nl/support" target="_blank">
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
                            <x-button.text-button class="order-1 rotate-svg-180"
                                                  wire:click.prevent="$set('tab', 'login')">
                                <x-icon.arrow/>
                                <span class="text-base">{{ __('auth.back_to_login') }}</span>
                            </x-button.text-button>
                        </div>
                    </div>
                </form>
            </div>
        @elseif($tab == 'no_mail_present')
            <div class="content-section p-10 shadow-xl flex flex-col " style="min-height: 550px"
                 x-data="{hoverAccount: false, hoverNoAccount: false, hasAccount: @entangle('doIHaveATcAccountChoice'), showPasswordNoEmail: false}"
            >

                <div class="flex items-center space-x-2.5 mb-5">
                    <div class="flex">
                        <x-stickers.entreefederatie/>
                    </div>
                    <div>
                        @if($this->doIHaveATcAccount === 1)
                            <h4>{{ __('auth.no_email_found_in_entree') }}</h4>
                        @else
                            <h4>{{ __('auth.link_email_to_entree') }}</h4>
                        @endif
                    </div>
                </div>
                @if(!$this->samlMessageValid())
                    <div class="flex flex-col flex-1 h-full">
                        {{ __('auth.no_saml_message_found') }}
                    </div>
                @else

                    @if($this->doIHaveATcAccount === 1)
                        <div class="flex flex-col flex-1 h-full"
                        >
                            <span class="text-lg">{{ __('auth.no_email_found_in_entree_long') }}</span>
                            <div class="mt-4 flex space-x-3 ">
                                <div wire:click="$set('doIHaveATcAccountChoice', 2)"
                                     @mouseenter="hoverAccount = true"
                                     @mouseleave="hoverAccount = false"
                                     class="flex p-4 flex-1 border-2 h-full rounded-10 cursor-pointer transition-all relative space-x-4 hover:shadow-md"
                                     :class="hoverAccount || hasAccount === 2 ? 'primary border-primary bg-off-white': 'mid-grey border-blue-grey'"
                                >
                                    <div class="flex">
                                        <x-icon.account/>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="body2"
                                              :class="hoverAccount || hasAccount === 2 ? 'primary bold': 'base'">
                                            {{ __('auth.i_have_a_tc_account') }}
                                        </span>
                                        <span class="mid-grey text-sm">{{ __('auth.choose_this_if_you_already_have_an_account') }}</span>

                                    </div>
                                    <template x-if="hasAccount === 2">
                                        <x-icon.checkmark class="absolute top-2 right-2 overflow-visible"/>
                                    </template>
                                </div>

                                <div wire:click="$set('doIHaveATcAccountChoice', 3)"
                                     @mouseenter="hoverNoAccount = true"
                                     @mouseleave="hoverNoAccount = false"
                                     class="flex p-4 flex-1 border-2 h-full rounded-10 cursor-pointer transition-all relative space-x-4 hover:shadow-md"
                                     :class="hoverNoAccount || hasAccount === 3 ? 'primary border-primary bg-off-white ': 'mid-grey border-blue-grey '"
                                >
                                    <div class="flex">
                                        <x-icon.no-account/>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="body2"
                                              :class="hoverNoAccount || hasAccount === 3 ? 'primary bold': 'base'">
                                            {{ __('auth.i_have_no_tc_account') }}
                                        </span>
                                        <span class="mid-grey text-sm">{{ __('auth.choose_this_if_you_have_no_account') }}</span>

                                    </div>
                                    <template x-if="hasAccount === 3">
                                        <x-icon.checkmark class="absolute top-2 right-2 overflow-visible"/>
                                    </template>
                                </div>
                            </div>
                            <div class="flex w-full justify-between mt-auto">
                                <x-button.text-button type="link" href="{{ route('auth.login') }}"
                                                      class="rotate-svg-180">
                                    <x-icon.arrow/>
                                    <span>{{ __('auth.back_to_login') }}</span>
                                </x-button.text-button>
                                <x-button.primary wire:click="noEntreeEmailNextStep"
                                                  x-bind:disabled="hasAccount == null" size="md">
                                    <span>{{ __('auth.next_step') }}</span>
                                    <x-icon.chevron/>
                                </x-button.primary>
                            </div>
                        </div>
                    @endif

                    @if($this->doIHaveATcAccount === 2)
                        <div class="flex flex-col flex-1 h-full">
                            <span class="text-lg">{{ __('auth.log_in_with_existing_tc_account') }}</span>
                            <div class="flex flex-col flex-1 mt-4">
                                <form wire:submit.prevent="loginForNoMailPresent" action="#" method="POST"
                                      class="flex-col flex flex-1">
                                    <div class="flex flex-col md:flex-row space-y-4 md:space-x-4 md:space-y-0">
                                        <x-input.group label="{{ __('auth.emailaddress')}}" class="flex-1">
                                            <x-input.text wire:model.lazy="username" autofocus></x-input.text>
                                        </x-input.group>
                                        <x-input.group label="{{ __('auth.password')}}" class="flex-1 relative">
                                            <x-input.text wire:model.lazy="password"
                                                          x-bind:type="showPasswordNoEmail ? 'text' : 'password'"
                                                          class="pr-12 overflow-ellipsis"
                                            >
                                            </x-input.text>
                                            <x-icon.preview
                                                    class="absolute bottom-3 right-3.5 primary-hover cursor-pointer"
                                                    @click="showPasswordNoEmail = !showPasswordNoEmail"/>
                                        </x-input.group>
                                    </div>
                                    <div class="error-section">
                                        @if($this->entree_error_message)
                                            <div class="notification error stretched mt-4">
                                                <div class="flex items-center space-x-3">
                                                    <x-icon.exclamation/>
                                                    <span class="title">{{ __('auth.entree_error') }}</span>
                                                </div>
                                                <span class="body">{{ __($this->entree_error_message) }}</span>
                                            </div>
                                        @endif
                                        @error('entree_error')
                                        <div class="notification error stretched mt-4">
                                            <span class="title">{{ $message }}</span>
                                        </div>
                                        @enderror
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
                                            <div
                                                    x-on:refresh-captcha.window="$refs.captcha.firstElementChild.setAttribute('src','/captcha/image?_=1333294957&_='+Math.random());">
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
                                                           wire:model="captcha" autocomplete="off"
                                                           style="width: 180px"/>
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
                                            <x-button.cta class="" size="md">
                                                <x-icon.entreefederatie/>
                                                <span>{{ __('auth.make_link') }}</span>
                                            </x-button.cta>
                                        </div>
                                        <x-button.text-button class="order-1 rotate-svg-180"
                                                              wire:click.prevent="backToNoEmailChoice">
                                            <x-icon.arrow/>
                                            <span class="">{{__('auth.back_to_make_choice')}}</span>
                                        </x-button.text-button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($this->doIHaveATcAccount === 3)
                        <div class="flex flex-col flex-1 h-full">
                            <span class="text-lg">{{ __('auth.submit_mail_to_link_entree_to_tc') }}</span>
                            <div class="flex-col flex flex-1 mt-4">
                                <form wire:submit.prevent="emailEnteredForNoMailPresent" action="#" method="POST"
                                      class="flex-col flex flex-1">

                                    <div class="flex flex-col md:flex-row space-y-4 md:space-x-4 md:space-y-0">
                                        <x-input.group label="{{ __('auth.emailaddress')}}" class="w-full md:w-1/2">
                                            <x-input.text wire:model.lazy="username" autofocus></x-input.text>
                                        </x-input.group>
                                        {{ $schoolLocation }}

                                    </div>
                                    <div class="error-section">
                                        @error('username')
                                        <div class="notification error stretched mt-4">
                                                <span class="title flex items-center">
                                                    <x-icon.exclamation class="mr-2"/>
                                                    <span>{{ $message }}</span>
                                                </span>
                                            <span class="body">{{ __('auth.are_you_sure_you_have_no_account') }}</span>
                                        </div>
                                        @enderror
                                    </div>
                                    <div class="flex mt-auto pt-4 justify-between">
                                        <div class="flex order-2 space-x-4">
                                            <x-button.cta class="" size="md">
                                                <x-icon.entreefederatie/>
                                                <span>{{ __('auth.make_link') }}</span>
                                            </x-button.cta>
                                        </div>
                                        <x-button.text-button class="order-1 rotate-svg-180"
                                                              wire:click.prevent="backToNoEmailChoice">
                                            <x-icon.arrow/>
                                            <span class="">{{__('auth.back_to_make_choice')}}</span>
                                        </x-button.text-button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @endif
            </div>

        @elseif($tab == 'fatalError')
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
                        <p class="mb-4 body1">{{ __('auth.connect_entree_error') }}</p>

                        @if($fatal_error_message)
                            <div class="flex">
                                <div class="notification error stretched mt-4">
                                    <span class="title">{!!  __($fatal_error_message) !!}</span>
                                </div>
                            </div>
                        @endif
                        <div class="mt-auto flex w-full">

                            <x-button.text-button class="rotate-svg-180" type="link"
                                                  href="{{ route('saml2_login', 'entree') }}">
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
                        <div class="flex">
                            @if(!Ramsey\Uuid\Uuid::isValid($this->uuid))
                                <div class="notification error stretched mt-4">
                                    <span class="title">{{ __('auth.no_saml_message_found') }}</span>
                                </div>
                            @endif

                            @error('entree_error')
                            <div class="notification error stretched mt-4">
                                <span class="title">{{ $message }}</span>
                            </div>
                            @enderror
                        </div>

                        <div class="mt-auto flex w-full">
                            <x-button.text-button class="rotate-svg-180" wire:click.prevent="returnToLogin">
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
        <div class="flex flex-col md:flex-row justify-center items-center md:space-x-4" browser wire:ignore>
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
