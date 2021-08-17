<x-layouts.base>
    <header class="header top-0 px-8 xl:px-28 flex flex-wrap content-center fixed w-full z-20"
            x-data="{}"
            x-on:set-red-header-border.window="$el.classList.add('red-header-border')"
            x-on:remove-red-header-border.window="$el.classList.remove('red-header-border')"
        >
        <a class="mr-4 flex" href="#">
            <img class="" src="/svg/logos/Logo-Test-Correct-2.svg"
                 alt="Test-Correct">
        </a>
{{--        <div class="flex items-center">--}}
{{--            <x-button.text-button type="link" href="{{ config('app.url_login') }}" class="rotate-svg-180">--}}
{{--                <x-icon.arrow/>--}}
{{--                <span>Terugknop (Naar portal, opnieuw inloggen?)</span>--}}
{{--            </x-button.text-button>--}}
{{--        </div>--}}
        <div class="user flex flex-wrap items-center ml-auto space-x-6">
            @if(Auth::user()->isA('Teacher'))
                <span class="bold">{{ Auth::user()->getNameFullAttribute() }}</span>
            @else
            <x-dropdown label="{{ Auth::user()->getNameFullAttribute() }}">
                <x-dropdown.item onclick="livewire.find(document.querySelector('[testtakemanager]').getAttribute('wire:id')).call('turnInModal')">
                    Inleveren
                </x-dropdown.item>
            </x-dropdown>
            @endif
        </div>
    </header>

    <main class="flex flex-1 items-stretch mx-8 xl:mx-28 m-foot-head">
        {{ $slot }}
    </main>
    <footer class="footer px-8 xl:px-28 flex content-center fixed w-full bottom-0 z-10">

        <div class="flex items-center">
            {{ $fraudDetection }}
        </div>

        <div class="flex items-center ml-auto space-x-6">
            {{ $footerbuttons }}
        </div>
    </footer>
    {{ $testTakeManager }}
</x-layouts.base>
