<x-layouts.base>
    <header id="header"
            class="header top-0 fixed w-full h-auto py-2.5 px-6 z-20 main-shadow @if(\tcCore\Http\Helpers\GlobalStateHelper::getInstance()->hasActiveMaintenance()) maintenance-header-bg @endif @if(\tcCore\Http\Helpers\GlobalStateHelper::getInstance()->isOnDeploymentTesting()) deployment-testing-marker @endif">
        <div class="flex flex-wrap content-center justify-center sm:justify-start mx-auto ">
            <a class="mb-4 sm:mb-0 sm:mr-4 flex" href="#" x-data="{}" @click="location.reload()">
                <img class="" src="/svg/logos/Logo-Test-Correct-2.svg"
                     alt="Test-Correct">
            </a>
            <div class="flex sm:ml-auto items-center space-x-3" x-data="{}">
            @guest
                <x-button.cta size="sm" type="link" href="https://www.test-correct.nl/welcome" browser>{{ __('auth.Maak account') }}</x-button.cta>
                <x-button.primary size="sm" type="link" href="{{ route('auth.login') }}" browser>{{  __('auth.login') }}</x-button.primary>
            @endguest
                <x-button.primary @click="Core.closeElectronApp()" size="sm" electron>
                    <span class="capitalize">{{__('general.close')}}</span>
                </x-button.primary>
                <x-button.primary @click="Core.closeChromebookApp('{{ \tcCore\Http\Helpers\BaseHelper::getLoginUrl() }}')" size="sm" chromebook>
                    <span class="capitalize">{{__('general.close')}}</span>
                </x-button.primary>
            </div>
        </div>
    </header>
    <main class="">
        {{ $slot }}
    </main>
</x-layouts.base>
