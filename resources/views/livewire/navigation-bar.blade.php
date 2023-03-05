<header id="navigation-bar"
     class="navigation-bar"
     x-data="navigationBar({{ $user }})"
     x-ref="nav_bar"
>
    <div id="logo" class="logo">
        <x-logos.test-correct-round class="logo-1" id="logo_1" wire:click="cakeRedirect('dashboard')"/>
        <x-logos.test-correct-text class="logo-2" id="logo_2" wire:click="cakeRedirect('dashboard')"/>
        <span class="student_version_tag" style="display: none"></span>
    </div>

    <span id="version-badge"></span>

    @yield('menu-top')

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
                 selid="{{$menuName}}-menu-btn"
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
                    <div class="tile-item {{ $tileName }}" selid="{{$tileName}}-menu-sub-btn" {{ $this->getMenuAction($tileData) }}>
                        {{ $tileData->title }}
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

</header>