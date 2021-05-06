<x-layouts.base>
    <header id="header"
            class="header top-0 fixed w-full h-auto p-2.5 z-20">
        <div class="flex flex-wrap content-center justify-center sm:justify-start mx-auto max-w-5xl">
            <a class="mb-4 sm:mb-0 sm:mr-4 flex" href="#">
                <img class="" src="/svg/logos/Logo-Test-Correct-2.svg"
                     alt="Test-Correct">
            </a>
            <div class="hidden flex items-center">
                <x-dropdown label="Oplossingen">
                    <x-dropdown.item @click="alert('Oplossingen')">
                        Oplossingen
                    </x-dropdown.item>
                </x-dropdown>

                <x-dropdown label="Diensten">
                    <x-dropdown.item @click="alert('Oplossingen')">
                        Oplossingen
                    </x-dropdown.item>
                </x-dropdown>

                <x-dropdown label="Support">
                    <x-dropdown.item @click="alert('Oplossingen')">
                        Oplossingen
                    </x-dropdown.item>
                </x-dropdown>

                <x-button.text-button class="ml-4">Over Ons</x-button.text-button>

            </div>
            <div class="flex sm:ml-auto items-center space-x-3">
                <x-button.cta size="sm">Maak account</x-button.cta>
                <x-button.primary size="sm" type="link" href="{{ route('auth.login') }}">Log in</x-button.primary>
            </div>
        </div>
    </header>
    <main class="">
        {{ $slot }}
    </main>
</x-layouts.base>