<div class="flex justify-center items-center min-h-screen">
    <div class="w-full max-w-3xl space-y-4">
        <div class="flex justify-center">
            <x-button.text-button>
                <span>{{__('auth.login_as_teacher')}}</span>
                <x-icon.arrow/>
            </x-button.text-button>
        </div>

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
                    <div class="hidden" :class="{'border-b-2 border-primary -mb-px' : openTab === 2}">
                        <x-button.text-button class="primary"
                                              @click="openTab = 2">{{ __('auth.log_in_as_guest') }}</x-button.text-button>
                    </div>
                </div>

                <div class="flex flex-col flex-1" x-show="openTab === 1">
                    <div class="mb-3">
                        <h4>{{__('auth.log_in_with_student_account')}}</h4>
                    </div>
                    <form wire:submit.prevent="login" action="#" method="POST" class="flex-col flex flex-1">
                        <div class="flex space-x-4">
                            <x-input.group label="{{ __('auth.emailaddress')}}" class="flex-1">
                                <x-input.text wire:model="username"></x-input.text>
                            </x-input.group>
                            <x-input.group label="{{ __('auth.password')}}" class="flex-1 relative">
                                <x-input.text wire:model.lazy="password"
                                              x-bind:type="showPassword ? 'text' : 'password'"
                                              class="pr-12 overflow-ellipsis"
                                >
                                </x-input.text>
                                <x-icon.preview class="absolute bottom-3 right-3.5 primary-hover"
                                                @click="showPassword = !showPassword"/>
                            </x-input.group>
                        </div>
                        <div class="error-section">
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

                        <div class="hidden">
                            <div class="mx-auto mt-4 flex flex-col items-center"
                                 x-data="{selected:null}">
                                <div class="w-full flex justify-center border-b border-light-grey">
                                    <x-button.text-button type="link" class="rotate-svg-90 cursor-pointer"
                                                          @click.prevent="selected !== 1 ? selected = 1 : selected = null">
                                        <span>Meteen naar toets gaan?</span>
                                        <x-icon.chevron/>
                                    </x-button.text-button>
                                </div>

                                <div class="relative overflow-hidden transition-all max-h-0 duration-700"
                                     style="" x-ref="container1"
                                     x-bind:style="selected == 1 ? 'max-height: ' + $refs.container1.scrollHeight + 'px' : ''">
                                    {{-- Toetscode code --}}
                                    <div></div>
                                </div>
                            </div>

                        </div>

                        <div class="flex mt-auto">
                            <x-button.cta class="ml-auto" size="md">{{ __('auth.log_in_verb') }}</x-button.cta>
                        </div>
                    </form>
                </div>

                <div class="flex flex-col flex-1" x-show="openTab === 2">
                    <div>Gast login</div>
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
