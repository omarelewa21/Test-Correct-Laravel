<x-layouts.base>
    @livewire('navigation.teacher-navigation-bar', ['main'=> 'Test', 'sub' => 'TestBank'])

    <main class="">
        {{ $slot }}
    </main>
    @livewire('livewire-ui-modal')
</x-layouts.base>
