@extends('livewire.navigation-bar')

@section('menu-top')
    <div id="menu-top" class="menu-top" x-ref="menu_top">
        <div class="action-icon-container">
            <div class="action-icon support-icon" x-ref="support_button">
                <x-icon.buoy/>
            </div>
            <div class="action-icon messages-icon">
                <x-icon.envelope/>
            </div>
        </div>
        <div class="user-button-container device-dependent-margin" x-ref="user_button">
            {{ Auth::user()->formal_name }}
            <svg height="9" width="12">
                <polygon points="6,9 1,0 11,0" stroke="rgba(71, 129, 255, 1)" fill="rgba(71, 129, 255, 1)"/>
            </svg>
        </div>
        <div class="user-menu" x-cloak x-ref="user_menu" x-cloak="" x-show="userMenu" x-transition.origin.top
             @click.outside="userMenu = false">
            <a href="{{ route('auth.login') }}">{{__('header.Uitloggen')}}</a>
            <a class="cursor-pointer"
               wire:click="cakeRedirect('update-password')">{{__('header.Wachtwoord wijzigen')}}</a>
        </div>
        <div class="support-menu" x-ref="support_menu" x-cloak="" x-show="supportMenu" x-transition=""
             @click.outside="supportmenu = false">
            <a class="cursor-pointer" wire:click="cakeRedirect('knowledge_base')">{{__('header.Kennisbank')}}</a>
        </div>
    </div>
@endsection