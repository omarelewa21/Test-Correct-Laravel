<div class="flex justify-center items-center min-h-screen">
    <div class="w-full max-w-3xl space-y-4">
        <div class="flex justify-center">
            <x-button.text-button>
                <span>{{__('auth.login_as_teacher')}}</span>
                <x-icon.arrow/>
            </x-button.text-button>
        </div>

        <div class="content-section p-10 space-y-5 shadow-xl">
            <div class="flex items-center space-x-2.5">
                <div class="flex">
                    <x-stickers.login/>
                </div>
                <div>
                    <h1>{{ __('auth.login_as_student') }}</h1>
                </div>

            </div>

            <div class="flex-col" x-data="{openTab: 1}">
                <div class="flex w-full space-x-6 mb-5">
                    <x-button.text-button class="primary"
                                          @click="openTab = 1">{{ __('auth.log_in_verb') }}</x-button.text-button>
                    <x-button.text-button class="primary"
                                          @click="openTab = 2">{{ __('auth.log_in_as_guest') }}</x-button.text-button>
                </div>

                <div x-show="openTab === 1">
                    <div class="mb-3">
                        <h4>{{__('auth.log_in_with_student_account')}}</h4>
                    </div>
                    <form wire:submit.prevent="login" action="#" method="POST" class="flex-col">
                        <div class="flex space-x-4">
                            <x-input.group label="{{ __('auth.emailaddress')}}" class="flex-1">
                                <x-input.text wire:model="username"></x-input.text>
                            </x-input.group>
                            <x-input.group label="{{ __('auth.password')}}" class="flex-1">
                                <x-input.text wire:model.lazy="password" type="password"></x-input.text>
                            </x-input.group>
                        </div>
                        <div class="error-section md:mb-20">
                            @error('username')
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

                        <div class="flex mt-4">
                            <x-button.cta class="ml-auto" size="md">{{ __('auth.log_in_verb') }}</x-button.cta>
                        </div>
                    </form>
                </div>

                <div x-show="openTab === 2">
                    Hoi ik ben tab 2

                    <div class="bg-white max-w-xl mx-auto border border-gray-200" x-data="{selected:null}">
                        <ul class="shadow-box">

                            <li class="relative border-b border-gray-200">

                                <button type="button" class="w-full px-8 py-6 text-left"
                                        @click="selected !== 1 ? selected = 1 : selected = null">
                                    <div class="flex items-center justify-between">
					                    <span>Should I use reCAPTCHA v2 or v3?</span>
                                        <span class="ico-plus"></span>
                                    </div>
                                </button>

                                <div class="relative overflow-hidden transition-all max-h-0 duration-700"
                                     style="" x-ref="container1"
                                     x-bind:style="selected == 1 ? 'max-height: ' + $refs.container1.scrollHeight + 'px' : ''">
                                    <div class="p-6">
                                        <p>reCAPTCHA v2 is not going away! We will continue to fully support and
                                            improve security and usability for v2.</p>
                                        <p>reCAPTCHA v3 is intended for power users, site owners that want more
                                            data about their traffic, and for use cases in which it is not
                                            appropriate to show a challenge to the user.</p>
                                        <p>For example, a registration page might still use reCAPTCHA v2 for a
                                            higher-friction challenge, whereas more common actions like sign-in,
                                            searches, comments, or voting might use reCAPTCHA v3. To see more
                                            details, see the reCAPTCHA v3 developer guide.</p>
                                    </div>
                                </div>

                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

        <div class="flex justify-center items-center space-x-6">
            <span>{{__('auth.forgot_password_long')}}</span>
            <x-button.text-button>
                <span>{{__('auth.reset_password')}}</span>
                <x-icon.arrow/>
            </x-button.text-button>
        </div>
        <div class="flex justify-center items-center space-x-4">
            <x-button.primary>
                <x-icon.download/>
                <span>{{__('auth.download_app')}}</span>
            </x-button.primary>
            <h5 class="inline-flex">&amp;</h5>
            <x-button.text-button>
                <span>{{__('auth.request_account_from_teacher')}}</span>
                <x-icon.arrow/>
            </x-button.text-button>
        </div>


    </div>
</div>
