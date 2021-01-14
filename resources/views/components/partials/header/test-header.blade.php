<header class="header flex flex-wrap content-center">
    <a class="mr-4" href="#">
        <img class="" src="/svg/logos/Logo-Test-Correct-2.svg"
             alt="Test-Correct">
    </a>
    <div class="user flex flex-wrap items-center ml-auto">
        <x-dropdown label="{{ Auth::user()->getNameFullAttribute() }}">
            <x-dropdown.item>Inleveren</x-dropdown.item>
        </x-dropdown>
    </div>
</header>