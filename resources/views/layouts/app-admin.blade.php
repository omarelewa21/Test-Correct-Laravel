<x-layouts.base>
    <header class="header top-0 px-8 xl:px-28 flex flex-wrap content-center fixed w-full main-shadow z-20 @if(\tcCore\Http\Helpers\GlobalStateHelper::getInstance()->hasActiveMaintenance()) maintenance-header-bg @endif @if(\tcCore\Http\Helpers\GlobalStateHelper::getInstance()->isOnDeploymentTesting()) deployment-testing-marker @endif">
        <a class="mr-4 flex" href="#">
            <img class="" src="{{ asset('/svg/logos/Logo-Test-Correct-2.svg') }}"
                 alt="Test-Correct">
        </a>

        <div class="flex space-x-6 items-center">
            @if(Auth::user()->isA('Account manager'))
                @include('components.partials.header.accountmanager')
            @endif
            @if(Auth::user()->isA('Administrator'))
                @include('components.partials.header.administrator')
            @endif
        </div>
        <div class="user flex flex-wrap items-center ml-auto space-x-6">
            @if(Auth::user()->isA('Teacher'))
                <span class="bold">{{ Auth::user()->getNameFullAttribute() }}</span>
            @elseif(Auth::user()->isA('Administrator') || Auth::user()->isA('account manager'))
                <x-dropdown label="{{ Auth::user()->getNameFullAttribute() ?: Auth::user()->name }}">
                    <x-dropdown.item onclick="Livewire.emit('logout')">
                        {{ __("auth.logout") }}
                    </x-dropdown.item>
                </x-dropdown>
            @endif
        </div>
    </header>

    <main class="flex flex-1 items-stretch mx-8 xl:mx-28 m-foot-head">
        {{ $slot }}
    </main>
    @isset($footerbuttons)
        <footer class="footer px-8 xl:px-28 flex content-center fixed w-full bottom-0 z-10">
            <div class="flex items-center ml-auto space-x-6">
                {{ $footerbuttons }}
            </div>
        </footer>
    @endisset
    @livewire('livewire-ui-modal')
</x-layouts.base>
