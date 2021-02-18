<x-layouts.base>

    <header class="header fixed w-full content-center">
        <div class="mx-4 lg:mx-28 flex h-full items-center">
            <div>
                <a class="" href="{{config('app.url_login')}}">
                    <img class="h-8 lg:h-12" src="/svg/logos/Logo-Test-Correct-2.svg"
                         alt="Test-Correct">
                </a>
            </div>

            <div id="menu" class="menu hidden flex-wrap content-center md:flex md:ml-4">
                <div class="menu-item px-2"><a href="#" class="text-button">Dashboard</a></div>
                <div class="menu-item px-2"><a href="#" class="text-button active">Toetsing</a></div>
                <div class="menu-item px-2"><a href="#" class="text-button">Analyses</a></div>
                <div class="menu-item px-2"><a href="#" class="text-button">Berichten</a></div>
                <div class="menu-item px-2"><a href="#" class="text-button">Kennisbank</a></div>
            </div>

            <div class="user flex flex-wrap items-center ml-auto space-x-6">
                <x-dropdown label="{{ Auth::user()->getNameFullAttribute() }}">
                    <x-dropdown.item>
                        Uitloggen
                    </x-dropdown.item>
                </x-dropdown>
            </div>
        </div>
    </header>

    <main>
        <div class="m-4 lg:m-28 space-y-6">
            <div>
                <h1>Geplande toetsen</h1>
            </div>
            <div class="bg-white p-8 rounded-10">
                <x-table>
                    <x-slot name="head">
                        <x-table.heading sortable="true">Toets</x-table.heading>
                        <x-table.heading>Vragen</x-table.heading>
                        <x-table.heading>Surveillanten</x-table.heading>
                        <x-table.heading>Inplanner</x-table.heading>
                        <x-table.heading sortable="true">Vak</x-table.heading>
                        <x-table.heading sortable="true">Afname</x-table.heading>
                        <x-table.heading sortable="true">Weging</x-table.heading>
                        <x-table.heading sortable="true">Type</x-table.heading>
                        <x-table.heading sortable="true"></x-table.heading>
                    </x-slot>
                    <x-slot name="body">
                        <x-table.row>
                            <x-table.cell>hans</x-table.cell>
                            <x-table.cell>timmer</x-table.cell>
                            <x-table.cell>mans</x-table.cell>
                            <x-table.cell>hans</x-table.cell>
                            <x-table.cell>timmer</x-table.cell>
                            <x-table.cell>mans</x-table.cell>
                            <x-table.cell>hans</x-table.cell>
                            <x-table.cell>timmer</x-table.cell>
                            <x-table.cell>mans</x-table.cell>
                        </x-table.row>
                        <x-table.row>
                            <x-table.cell>hans</x-table.cell>
                            <x-table.cell>timmer</x-table.cell>
                            <x-table.cell>mans</x-table.cell>
                            <x-table.cell>hans</x-table.cell>
                            <x-table.cell>timmer</x-table.cell>
                            <x-table.cell>mans</x-table.cell>
                            <x-table.cell>hans</x-table.cell>
                            <x-table.cell>timmer</x-table.cell>
                            <x-table.cell>mans</x-table.cell>
                        </x-table.row>
                        <x-table.row>
                            <x-table.cell>hans</x-table.cell>
                            <x-table.cell>timmer</x-table.cell>
                            <x-table.cell>mans</x-table.cell>
                            <x-table.cell>hans</x-table.cell>
                            <x-table.cell>timmer</x-table.cell>
                            <x-table.cell>mans</x-table.cell>
                            <x-table.cell>hans</x-table.cell>
                            <x-table.cell>timmer</x-table.cell>
                            <x-table.cell>mans</x-table.cell>
                        </x-table.row>
                    </x-slot>
                </x-table>
            </div>

        </div>
    </main>


</x-layouts.base>