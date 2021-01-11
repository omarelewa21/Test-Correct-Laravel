<x-layouts.base>
    <header class="header flex flex-wrap content-center fixed w-full z-10">
        <a class="mr-4" href="#">
            <img class="" src="/svg/logos/Logo-Test-Correct-2.svg"
                 alt="Test-Correct">
        </a>
        <div class="user flex flex-wrap items-center ml-auto space-x-6">
            <x-fraud-detected/>

            <x-dropdown label="{{ Auth::user()->getNameFullAttribute() }}">
                <x-dropdown.item>Inleveren</x-dropdown.item>
            </x-dropdown>
        </div>
    </header>
    <main class="flex-1 mx-28 m-foot-head">
        {{ $slot }}
    </main>
    <footer class="footer flex content-center fixed w-full bottom-0 z-10">
        <div class="flex items-center ml-auto space-x-6">
            {{ $footerbuttons }}

        </div>
    </footer>

    {{--    <x-notification></x-notification>--}}
</x-layouts.base>
