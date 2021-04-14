<header id="header" class="header fixed w-full content-center">
    <div class="mx-4 lg:mx-28 flex h-full items-center">
        <div class="relative">
            <a href="{{config('app.url_login')}}">
                <img class="h-8 lg:h-12" src="{{ asset('/svg/logos/Logo-Test-Correct-2.svg') }}"
                     alt="Test-Correct">
                <span class="note text-xs absolute min-w-max -bottom-1 left-[60px]">Version 1.2</span>
            </a>
        </div>

        <div id="menu" class="menu hidden flex-wrap content-center md:flex md:ml-4">
            <div class="menu-item px-2 py-1">
                <x-button.text-button class="active">Dashboard</x-button.text-button>
            </div>
            <div class="menu-item px-2 py-1">
                <x-button.text-button wire:click="">Toetsing</x-button.text-button>
            </div>
            <div class="menu-item px-2 py-1">
                <x-button.text-button wire:click="">Analyses</x-button.text-button>
            </div>
            <div class="menu-item px-2 py-1">
                <x-button.text-button wire:click="">Berichten</x-button.text-button>
            </div>
            <div class="menu-item px-2 py-1">
                <x-button.text-button wire:click="">Kennisbank</x-button.text-button>
            </div>
        </div>

        <div class="user flex flex-wrap items-center ml-auto space-x-6">
            <x-dropdown label="{{ Auth::user()->getNameFullAttribute() }}">
                <x-dropdown.item wire:click="logout()">
                    Uitloggen
                </x-dropdown.item>
            </x-dropdown>
        </div>
    </div>
</header>