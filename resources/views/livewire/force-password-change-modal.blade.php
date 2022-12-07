<x-modal.base-modal :closable="false">
    <x-slot name="title">
        <h2>{{__("password-reset.Maak een nieuw wachtwoord")}}</h2>
    </x-slot>

    <x-slot name="content">
        <div @password-changed-success.window="Notify.notify($event.detail, 'info')">
            <span>{{__('password-reset.Uitleg vervallen wachtwoord')}}</span>
            <div>
                @if($errors->isNotEmpty())
                    <div class="flex flex-col gap-2.5 w-full">
                        @foreach($errors->all() as $error)
                            <div class="notification error stretched w-full">
                                <span class="title">{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="flex flex-col">
                <x-input.group
                        x-data="{showPassword: false, hoverPassword: false, initialPreviewIconState: true}"
                        label="{{ __('auth.new_password')}}"
                        class="flex-1 relative pt-2">
                    <div @mouseenter="hoverPassword = true"
                         @mouseleave="hoverPassword = false"
                         @click="showPassword = !showPassword; hoverPassword = false; initialPreviewIconState = false"
                         wire:ignore>
                        <x-icon.preview-off
                                class="absolute bottom-3 right-3.5 primary-hover cursor-pointer"
                                x-bind:class="{'opacity-50' : initialPreviewIconState, 'hover:text-sysbase': (!showPassword && !hoverPassword)}"
                                x-show="(!showPassword && !hoverPassword) || (showPassword && hoverPassword)"/>
                        <div class="absolute bottom-3 right-3.5 flex items-center h-[16px]">
                            <x-icon.preview class="primary-hover cursor-pointer"
                                            x-bind:class="{'hover:text-sysbase': (showPassword && !hoverPassword)}"
                                            x-show="(showPassword && !hoverPassword) || (!showPassword && hoverPassword)"/>
                        </div>
                    </div>
                    <x-input.text wire:model.lazy="newPassword"
                                  selid="login-password"
                                  x-bind:type="showPassword ? 'text' : 'password'"
                                  class="pr-12 overflow-ellipsis"
                    >
                    </x-input.text>
                </x-input.group>
                <x-input.group
                        x-data="{showPassword: false, hoverPassword: false, initialPreviewIconState: true}"
                        label="{{ __('auth.new_password_repeat')}}"
                        class="flex-1 relative pt-2">
                    <div @mouseenter="hoverPassword = true"
                         @mouseleave="hoverPassword = false"
                         @click="showPassword = !showPassword; hoverPassword = false; initialPreviewIconState = false"
                         wire:ignore>
                        <x-icon.preview-off
                                class="absolute bottom-3 right-3.5 primary-hover cursor-pointer"
                                x-bind:class="{'opacity-50' : initialPreviewIconState, 'hover:text-sysbase': (!showPassword && !hoverPassword)}"
                                x-show="(!showPassword && !hoverPassword) || (showPassword && hoverPassword)"/>
                        <div class="absolute bottom-3 right-3.5 flex items-center h-[16px]">
                            <x-icon.preview class="primary-hover cursor-pointer"
                                            x-bind:class="{'hover:text-sysbase': (showPassword && !hoverPassword)}"
                                            x-show="(showPassword && !hoverPassword) || (!showPassword && hoverPassword)"/>
                        </div>
                    </div>
                    <x-input.text wire:model.lazy="newPasswordRepeat"
                                  selid="login-password"
                                  x-bind:type="showPassword ? 'text' : 'password'"
                                  class="pr-12 overflow-ellipsis"
                    >
                    </x-input.text>
                </x-input.group>
            </div>
        </div>
    </x-slot>

    <x-slot name="footer">
        <div class="inline self-end">
            <x-button.text-button class="mr-2"
                                  wire:click="$emit('closeModal')">
                {{__("auth.back_to_login")}}
            </x-button.text-button>
            <x-button.cta
                    wire:click="requestPasswordChange">
                {{__("password-reset.Wachtwoord resetten")}}
            </x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>
