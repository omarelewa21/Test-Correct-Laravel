<header id="header" class="header fixed w-full content-center z-10">
    <div class="py-2.5 px-6 flex h-full items-center">
        <div class="relative">
            <a href="@if(Auth::user()->guest){{ route('auth.login') }}@else{{ route('student.dashboard') }}@endif">
                <img class="h-12" src="{{ asset('/svg/logos/Logo-Test-Correct-2.svg') }}"
                     alt="Test-Correct">
                @if(!session()->get('isInBrowser'))
                    <span class="text-xs absolute min-w-max bottom-0 left-[60px]
                    @if(session()->get('TLCVersioncheckResult') == 'OK') cta-primary @elseif(session()->get('TLCVersioncheckResult') == 'NEEDSUPDATE') orange @elseif(session()->get('TLCVersioncheckResult') == 'NOTALLOWED') all-red @else note @endif"
                    >
                        {{ __('student.version') }}: {{ session()->get('TLCVersion') }}
                    </span>
                @endif
            </a>
        </div>

        <div id="menu" class="menu hidden flex-wrap content-center lg:flex lg:ml-4">
            @if(!Auth::user()->guest)
            <div class="menu-item px-2 py-1">
                <x-button.text id="student-header-dashboard" type="link" href="{{ route('student.dashboard') }}">
                    {{ __('student.dashboard') }}</x-button.text>
            </div>
            <div class="menu-item px-2 py-1">
                <x-button.text id="student-header-tests" type="link" href="{{ route('student.test-takes') }}">{{ __('student.tests') }}</x-button.text>
            </div>
            <div class="menu-item px-2 py-1">
                <x-button.text id="student-header-analysis" wire:click="">{{ __('student.analysis') }}</x-button.text>
            </div>
            <div class="menu-item px-2 py-1">
                <x-button.text id="student-header-messages" wire:click="">{{ __('student.messages') }}</x-button.text>
            </div>
            <div class="menu-item px-2 py-1">
                <x-button.text id="student-header-knowledgebank" wire:click="">{{ __('student.knowledgebank') }}</x-button.text>
            </div>
            @endif
        </div>

        <div class="user flex flex-wrap items-center ml-auto space-x-2" x-data="">
            <x-dropdown label="{{ Auth::user()->getNameFullAttribute() }}">
                @if(!Auth::user()->guest)
                <div class="lg:hidden">
                    <x-dropdown.item type="link" href="{{ route('student.dashboard') }}">
                        {{ __('student.dashboard') }}
                    </x-dropdown.item>
                    <x-dropdown.item type="link" href="{{ route('student.test-takes') }}">
                        {{ __('student.tests') }}
                    </x-dropdown.item>
                    <x-dropdown.item >
                        {{ __('student.analysis') }}
                    </x-dropdown.item>
                    <x-dropdown.item >
                        {{ __('student.messages') }}
                    </x-dropdown.item>
                    <x-dropdown.item >
                        {{ __('student.knowledgebank') }}
                    </x-dropdown.item>
                </div>
                @endif
                <x-dropdown.item type="link" href="{{ route('student.dashboard.logout') }}">
                    {{ __('auth.logout') }}
                </x-dropdown.item>
            </x-dropdown>
        </div>
    </div>
</header>