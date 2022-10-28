<x-layouts.base>
    <livewire:co-learning.header testName="{{ $testName ?? '' }}"/>

    <main class="flex flex-1 items-stretch mx-8 xl:mx-28 m-foot-head">
        {{ $slot  }}
    </main>



    <x-notification/>
    @livewire('livewire-ui-modal')

</x-layouts.base>