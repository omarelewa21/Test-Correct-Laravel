<header id="header" class="header fixed w-full content-center">
    <div class="mx-4 md:mx-8 lg:mx-12 xl:mx-28 flex h-full items-center">
        <div class="relative">
            <a href="{{config('app.url_login')}}">
                <img class="h-12" src="{{ asset('/svg/logos/Logo-Test-Correct-2.svg') }}"
                     alt="Test-Correct">
                <span class="note text-xs absolute min-w-max -bottom-1 left-[60px]">Version 1.2</span>
            </a>
        </div>

        <div id="menu" class="menu hidden flex-wrap content-center lg:flex lg:ml-4">
            <div class="menu-item px-2 py-1">
                <x-button.text-button id="student-header-dashboard" type="link" href="{{ route('student.dashboard') }}">Dashboard</x-button.text-button>
            </div>
            <div class="menu-item px-2 py-1">
                <x-button.text-button id="student-header-planned" type="link" href="{{ route('student.planned') }}">Toetsing</x-button.text-button>
            </div>
            <div class="menu-item px-2 py-1">
                <x-button.text-button id="student-header-analysis" wire:click="">Analyses</x-button.text-button>
            </div>
            <div class="menu-item px-2 py-1">
                <x-button.text-button id="student-header-messages" wire:click="">Berichten</x-button.text-button>
            </div>
            <div class="menu-item px-2 py-1">
                <x-button.text-button id="student-header-knowledgebank" wire:click="">Kennisbank</x-button.text-button>
            </div>
        </div>

        <div class="user flex flex-wrap items-center ml-auto space-x-6">
            <x-dropdown label="{{ Auth::user()->getNameFullAttribute() }}">
                <div class="lg:hidden">
                    <x-dropdown.item wire:click="logout()">
                        Dashboard
                    </x-dropdown.item>
                    <x-dropdown.item wire:click="logout()">
                        Toetsing
                    </x-dropdown.item>
                    <x-dropdown.item wire:click="logout()">
                        Analyses
                    </x-dropdown.item>
                    <x-dropdown.item wire:click="logout()">
                        Berichten
                    </x-dropdown.item>
                    <x-dropdown.item wire:click="logout()">
                        Kennisbank
                    </x-dropdown.item>
                </div>
                <x-dropdown.item wire:click="logout()">
                    Uitloggen
                </x-dropdown.item>
            </x-dropdown>
        </div>
    </div>
</header>