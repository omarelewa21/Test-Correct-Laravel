<div id="navigation-bar"
     class="navigation-bar"
     x-data="navigationBar"
     x-ref="nav_bar"
>
    <div id="logo" class="logo">
        <x-logos.test-correct-round class="logo-1" id="logo_1" wire:click="cakeRedirect('dashboard')"/>
        <x-logos.test-correct-text class="logo-2" id="logo_2" wire:click="cakeRedirect('dashboard')"/>
        <span class="student_version_tag" style="display: none"></span>
    </div>

    <span id="version-badge"></span>

    <div id="menu-top" class="menu-top" x-ref="menu_top">
        <div class="action-icon-container">
            <div class="action-icon menu-chat-icon" wire:click="cakeRedirect('chat')">
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
        <div class="user-button-container" x-ref="user_button">

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
            @if ($this->showSchoolSwitcher)
                <a  id="user_school_locations" class="cursor-pointer" wire:click="$emit('openModal', 'teacher.schoollocation-switcher-modal')">{{ __('general.Wissel van school') }}</a>
            @endif
            <a href="{{ route('auth.login') }}">{{__('header.Uitloggen')}}</a>
            <a class="cursor-pointer" wire:click="cakeRedirect('update-password')">{{__('header.Wachtwoord wijzigen')}}</a>
            <a href="https://support.test-correct.nl/knowledge" target="_blank">{{__('header.Supportpagina')}}</a>
            <a class="cursor-pointer" wire:click="cakeRedirect('delay-auto-logout')">{{__('header.Automatisch uitloggen uitstellen')}}</a>
            <a class="cursor-pointer" wire:click="cakeRedirect('tests.my_uploads_with_popup')">{{__('header.Uploaden toets')}}</a>
        </div>
        <div class="support-menu" x-ref="support_menu" x-cloak="" x-show="supportMenu" x-transition="" @click.outside="supportmenu = false">
            <a class="cursor-pointer" wire:click="cakeRedirect('knowledge_base')">{{__('header.Kennisbank')}}</a>
            <a class="cursor-pointer" wire:click="cakeRedirect('webinar')">{{__('header.Webinar')}}</a>
            <a href="mailto:{{ config('mail.from.address') }}">{{__('header.Email')}}</a>
            <a class="cursor-pointer" wire:click="cakeRedirect('support_updates')">{{__('header.Updates & onderhoud')}}</a>
        </div>
    </div>

    <div id="menu-bottom" class="menu-bottom" x-ref="menu_bottom">
        <div class="menu-scroll menu-scroll-left" x-ref="menu_scroll_left">
            <svg style="color:var(--white);transform:rotate(180deg);" width="9" height="13"
                 xmlns="http://www.w3.org/2000/svg">
                <path stroke="currentColor" stroke-width="3" d="M1.5 1.5l5 5-5 5" fill="none" fill-rule="evenodd"
                      stroke-linecap="round"></path>
            </svg>
        </div>
        @foreach($menus as $menuName => $menuData)
            <div class="menu-item {{ $menuData->hasItems ? 'has-items' : '' }}"
                 data-menu="{{ $menuName }}"
                 {{ $this->getMenuAction($menuData) }}
            >
                {{ $menuData->title }}
            </div>
        @endforeach
        <div class="menu-scroll menu-scroll-right" x-ref="menu_scroll_right">
            <svg style="color:var(--white);" width="9" height="13" xmlns="http://www.w3.org/2000/svg">
                <path stroke="currentColor" stroke-width="3" d="M1.5 1.5l5 5-5 5" fill="none" fill-rule="evenodd"
                      stroke-linecap="round"></path>
            </svg>
        </div>
    </div>
    <div id="tiles" class="tiles" x-ref="tiles">
        @foreach($tileGroups as $groupName => $group)
            <div class="tile-group {{ $groupName }}">
                @foreach($group as $tileName => $tileData)
                    <div class="tile-item {{ $tileName }}" {{ $this->getMenuAction($tileData) }}>
                        {{ $tileData->title }}
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

</div>