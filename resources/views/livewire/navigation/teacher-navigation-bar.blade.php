@extends('livewire.navigation-bar')

@section('menu-top')
    <div id="menu-top" class="menu-top" x-ref="menu_top">
        <div class="action-icon-container">
            @if(session()->has('support.id'))
                <div class="action-icon menu-chat-icon" style="color: red" title="stop support" wire:click="laravelRedirect('{{route('support.return_as_support_user')}}')">
                    <x-icon.stop-support/>
                </div>
            @endif
            <div class="action-icon menu-chat-icon" x-ref="chat_button">
                <x-icon.chat/>
                <span>{{__('Chat')}}</span>
            </div>
            <div class="action-icon support-icon" x-ref="support_button">
                <x-icon.buoy/>
            </div>
            <div class="action-icon messages-icon">
                <x-icon.envelope/>
            </div>
        </div>
        <div class="user-button-container device-dependent-margin" x-ref="user_button">

            @if(Auth::user()->hasMultipleSchools())
                <span title="{{ Auth::user()->formal_name_with_current_school_location }}">{!!  Auth::user()->formal_name_with_current_school_location_short !!}</span>
            @else
                {{ Auth::user()->formal_name }}
            @endif

            <svg height="9" width="12">
                <polygon points="6,9 1,0 11,0" stroke="rgba(71, 129, 255, 1)" fill="rgba(71, 129, 255, 1)"/>
            </svg>
        </div>
        <div class="user-menu" x-cloak x-ref="user_menu" x-cloak="" x-show="userMenu" x-transition.origin.top @click.outside="userMenu = false">
            <a  id="user_account_settings" class="cursor-pointer" wire:click="laravelRedirect('{{ route('users.account', url()->referrer() , absolute: false) }}')">@lang('account.account') @lang('account.settings')</a>
            @if ($this->showSchoolSwitcher)
                <a  id="user_school_locations" class="cursor-pointer" wire:click="$emit('openModal', 'teacher.schoollocation-switcher-modal')">{{ __('general.Wissel van school') }}</a>
            @endif
            <a class="cursor-pointer" wire:click="cakeRedirect('update-password')">{{__('header.Wachtwoord wijzigen')}}</a>
            <a href="https://support.test-correct.nl/knowledge" target="_blank">{{__('header.Supportpagina')}}</a>
            <a class="cursor-pointer" wire:click="cakeRedirect('delay-auto-logout')">{{__('header.Automatisch uitloggen uitstellen')}}</a>
            @if(Auth::user()->isToetsenbakker())
                <a class="cursor-pointer" wire:click="laravelRedirect('{{route('teacher.file-management.testuploads')}}')">{{__('header.Te verwerken Toetsen')}}</a>
            @endif
            <a href="{{ route('auth.login') }}">{{__('header.Uitloggen')}}</a>
        </div>
        <div class="support-menu" x-ref="support_menu" x-cloak="" x-show="supportMenu" x-transition="" @click.outside="supportmenu = false">
            <a class="cursor-pointer" wire:click="cakeRedirect('knowledge_base')">{{__('header.Kennisbank')}}</a>
            <a class="cursor-pointer" wire:click="cakeRedirect('webinar')">{{__('header.Webinar')}}</a>
            <a href="mailto:{{ config('mail.from.address') }}">{{__('header.Email')}}</a>
            <a class="cursor-pointer" wire:click="cakeRedirect('support_updates')">{{__('header.Updates & onderhoud')}}</a>
        </div>
    </div>
@endsection