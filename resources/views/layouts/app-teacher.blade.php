<x-layouts.base>
    @livewire('navigation-bar', ['main'=> 'Test', 'sub' => 'TestBank'])

    <main class="flex flex-1 items-stretch mx-8  xl:mx-28 mt-[9.5rem] m-foot-head' }}">
        {{ $slot }}
    </main>
</x-layouts.base>
