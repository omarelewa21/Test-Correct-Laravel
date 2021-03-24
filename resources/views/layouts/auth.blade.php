<x-layouts.base>
    <header class="header top-0 px-8 xl:px-28 flex flex-wrap content-center fixed w-full z-20">
        <a class="mr-4 flex" href="#">
            <img class="" src="/svg/logos/Logo-Test-Correct-2.svg"
                 alt="Test-Correct">
        </a>
        <div class="flex items-center">
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
        <div class="flex ml-auto items-center space-x-3">
            <x-button.cta size="sm">Maak account</x-button.cta>

            <x-dropdown label="Log in" button="primary-button">
                <x-dropdown.item @click="alert('Oplossingen')">
                    Docent
                </x-dropdown.item>
                <x-dropdown.item @click="alert('Oplossingen')">
                    Student
                </x-dropdown.item>
                <x-dropdown.item @click="alert('Oplossingen')">
                    Schoolbeheerder
                </x-dropdown.item>
            </x-dropdown>

            <a href="#" @click.prevent="" class="flex justify-center items-center rounded-full bg-primary text-white p-2 w-8 h-8" >
                <x-icon.questionmark/>
            </a>
        </div>
    </header>
    <main class="student-bg">
        {{ $slot }}
    </main>
</x-layouts.base>