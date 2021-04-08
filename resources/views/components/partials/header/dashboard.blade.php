<header class="header fixed w-full content-center"
        x-data="menuItemStates();"
        x-on:click.away="menuItemStates()"
>
    <div class="mx-4 lg:mx-28 flex h-full items-center">
        <div>
            <a class="relative" href="{{config('app.url_login')}}">
                <img class="h-8 lg:h-12" src="{{ asset('/svg/logos/Logo-Test-Correct-2.svg') }}"
                     alt="Test-Correct">
                <span class="note text-sm ">Version 1.2</span>
            </a>
        </div>

        <div id="menu" class="menu hidden flex-wrap content-center md:flex md:ml-4">
            <div class="menu-item px-2 py-1">
                <button @click="dashboard = !dashboard" @click.away="dashboard = false" class="text-button"
                        :class="{ 'active': dashboard}">Dashboard</button>
            </div>

            <x-menu.item label="Toetsing" name="toetsing"/>
            <x-menu.item label="Analyses" name="analyses"/>
            <x-menu.item label="Berichten" name="berichten"/>
            <x-menu.item label="Kennisbank" name="kennisbank"/>

        </div>

        <div class="user flex flex-wrap items-center ml-auto space-x-6">
            <x-dropdown label="{{ Auth::user()->getNameFullAttribute() }}">
                <x-dropdown.item wire:click="logout()">
                    Uitloggen
                </x-dropdown.item>
            </x-dropdown>
        </div>
    </div>
    <div>
        <div class="z-0 relative">
            <x-menu.dropdown name="toetsing">
                <x-button.text-button>Geplande toetsen</x-button.text-button>
                <x-button.text-button>Te bespreken</x-button.text-button>
                <x-button.text-button>Inzien</x-button.text-button>
                <x-button.text-button>Becijferd</x-button.text-button>
            </x-menu.dropdown>

            <x-menu.dropdown name="analyses">
                <x-button.text-button>Jouw analyses</x-button.text-button>
            </x-menu.dropdown>

            <x-menu.dropdown name="berichten">
                <x-button.text-button>Berichten</x-button.text-button>
            </x-menu.dropdown>

            <x-menu.dropdown name="kennisbank">
                <x-button.text-button>Bezoek de kennisbank</x-button.text-button>
            </x-menu.dropdown>
        </div>
    </div>
</header>