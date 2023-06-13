<x-layouts.base>
    @livewire('navigation.teacher-navigation-bar', ['main'=> 'Test', 'sub' => 'TestBank'])

    <main class="">
        {{ $slot }}
    </main>
</x-layouts.base>
