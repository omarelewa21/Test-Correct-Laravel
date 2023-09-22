<x-modal.base-modal>
    <x-slot:title><h2>@lang('header.change_password')</h2></x-slot:title>
    <x-slot:content>
        <div class="flex flex-col flex-1"
             x-data="{}"
        >
            <div class="flex flex-col max-w-sm space-y-2.5 text-left">
                <x-input.group label="{{ __('auth.current_password')}}" class="flex-1">
                    @error('currentPassword')
                    <div class="validate-red text-sm">
                        {{ $message }}
                    </div>
                    @enderror
                    <x-input.text wire:model.lazy="currentPassword" type="password" autofocus />
                </x-input.group>
                <div class="divider border mt-2"></div>
                <x-input.group label="{{ __('auth.new_password')}}" type="password" class="flex-1">
                    @error('newPassword')
                    <div class="validate-red text-sm">
                        {{ $message }}
                    </div>
                    @enderror
                    <x-input.text wire:model.lazy="newPassword" type="password" />
                </x-input.group>
                <x-input.group label="{{ __('auth.new_password_repeat')}}" class="flex-1 pb-2">
                    @error('newPassword_confirmation')
                    <div class="validate-red text-sm">
                        {{ $message }}
                    </div>
                    @enderror
                    <x-input.text wire:model.lazy="newPassword_confirmation" type="password" autofocus />
                </x-input.group>
                <div class="mid-grey w-1/2 md:w-auto order-2 md:order-3 pl-2 h-16 overflow-visible md:h-auto md:overflow-auto requirement-font-size">
                    <div class="validate-{{$this->minCharRule}}">
                        @if($this->minCharRule === 'green')
                            <x-icon.checkmark-small></x-icon.checkmark-small>
                        @elseif($this->minCharRule === 'red')
                            <x-icon.close-small></x-icon.close-small>
                        @else
                            <x-icon.dot></x-icon.dot>
                        @endif
                        <span>{{__("password-reset.Min. 8 tekens")}}</span>
                    </div>
                </div>
                @error('passwords-dont-match')
                <div class="notification error">
                    <span class="body">{{ $message }}</span>
                </div>
                @enderror
            </div>
        </div>
    </x-slot:content>
    <x-slot:footer>
        <div class="flex flex-1 mt-4 items-center">
            <x-button.primary class="ml-auto" wire:click="requestPasswordChange">
                <span>{{ __('auth.send') }}</span>
            </x-button.primary>
        </div>
    </x-slot:footer>

</x-modal.base-modal>