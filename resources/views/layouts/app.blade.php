<x-layouts.base>
    <header class="header px-8 lg:px-28 flex flex-wrap content-center fixed w-full z-10">
        <a class="mr-4" href="#">
            <img class="" src="/svg/logos/Logo-Test-Correct-2.svg"
                 alt="Test-Correct">
        </a>
        <div class="user flex flex-wrap items-center ml-auto space-x-6">


            <x-dropdown label="{{ Auth::user()->getNameFullAttribute() }}">
                <x-dropdown.item>Inleveren</x-dropdown.item>
                <x-dropdown.item>Uitloggen</x-dropdown.item>
            </x-dropdown>
        </div>
    </header>
    <main class="flex flex-1 items-stretch mx-8 lg:mx-28 m-foot-head">
        {{ $slot }}
    </main>
    <footer class="footer px-8 lg:px-28 flex content-center fixed w-full bottom-0 z-10">
        <div class="flex items-center">
            <x-fraud-detected/>
        </div>

        <div class="flex items-center ml-auto space-x-6">
            {{ $footerbuttons }}

        </div>
    </footer>

    {{--    <x-notification></x-notification>--}}
</x-layouts.base>
