<header id="header" class="header sticky top-0 w-full content-center z-10 main-shadow @if(\tcCore\Http\Helpers\GlobalStateHelper::getInstance()->hasActiveMaintenance()) maintenance-header-bg @endif @if(\tcCore\Http\Helpers\GlobalStateHelper::getInstance()->isOnDeploymentTesting()) deployment-testing-marker @endif"
        x-data="{activeIcon: null, showKnowledgebankModal: @entangle('showKnowledgebankModal')}"
>
    <div class="py-2.5 px-6 flex h-full items-center device-dependent-margin">
        <div class="relative">
            <a href="{{ $logoUrl }}">
                <img class="h-12" src="{{ asset('/svg/logos/Logo-Test-Correct-2.svg') }}"
                     alt="Test-Correct">
                <x-version-badge :version="$appVersion" :status="$appStatus"/>
            </a>
        </div>

        <div id="menu" class="menu hidden flex-wrap content-center lg:flex lg:ml-4">
            @if(!Auth::user()->guest)
                <div class="menu-item">
                    <x-button.text class="px-2" id="student-header-dashboard" wire:click="dashboard()"
                                          withHover="true">
                        <span class="">{{ __('student.dashboard') }}</span>
                    </x-button.text>
                </div>
                <div class="menu-item">
                    <x-button.text class="px-2" id="student-header-tests" wire:click="tests()" withHover="true">
                        <span class="">{{ __('student.tests') }}</span>
                    </x-button.text>
                </div>
                <div class="menu-item">
                    <x-button.text class="px-2" id="student-header-analysis" wire:click="analyses()"
                                          withHover="true">
                        <span class="">{{ __('student.analysis') }}</span>
                    </x-button.text>
                </div>
            @endif
        </div>

        <div class="flex ml-auto action-icons mr-4 relative">
            @if(session()->has('support.id'))
                <div class="action-icon menu-chat-icon" style="color: red" title="stop support" wire:click="laravelRedirect('{{route('support.return_as_support_user')}}')">
                    <x-icon.stop-support/>
                </div>
            @endif
            <div class="flex space-x-1">
                <button class="flex items-center justify-center order-1 p-1.5 rounded-full action-icon-button relative"
                        :class="{'active' : activeIcon === 'support'}"
                        x-ref="support_icon"
                        @click="activeIcon = 'support'"
                >
                    <x-icon.support/>
                </button>
                <button class="flex items-center justify-center order-2 p-1.5 rounded-full action-icon-button relative"
                        :class="{'active' : activeIcon === 'messages'}"
                        @click="activeIcon = 'messages'"
                        wire:click="messages()"
                >
                    <x-icon.messages/>
                    @if($this->unreadMessageCount > 0)
                        <span class="flex absolute text-xs bold -right-1 top-0 bg-cta-primary rounded-[20px] px-1.5 py-0.5 text-white ">{{ $this->unreadMessageCount }}</span>
                    @endif
                </button>
                <button class="hidden flex items-center justify-center order-3 p-1.5 rounded-full action-icon-button relative"
                        :class="{'active' : activeIcon === 'notifications'}"
                        @click="activeIcon = 'notifications'"
                >
                    <x-icon.notification/>
                    <span></span>
                </button>
            </div>

            <div x-cloak=""
                 x-show="activeIcon === 'support'"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute z-40 top-10"
                 :style="{right : ($refs.support_icon.parentElement.offsetWidth - $refs.support_icon.offsetWidth) + 'px'}"
                 @keydown.window.escape="activeIcon = false" @click.outside="activeIcon = false"
            >
                <div class="flex flex-col bg-white main-shadow w-56 py-2.5 rounded-10">
                    <x-button.text size="sm" class="px-2.5 w-full" @click="showKnowledgebankModal = true"
                                          withHover="true">
                        <span>{{ __('header.Kennisbank') }}</span>
                    </x-button.text>
                </div>
            </div>
        </div>

        <div class="user flex flex-wrap items-center space-x-2" selid="header-dropdown">
            <x-dropdown label="{{ $user_name }}" labelstyle="pr-0.5">
                @if(!Auth::user()->guest)
                    <div class="lg:hidden">
                        <x-dropdown.item wire:click="dashboard()">
                            {{ __('student.dashboard') }}
                        </x-dropdown.item>
                        <x-dropdown.item wire:click="tests()">
                            {{ __('student.tests') }}
                        </x-dropdown.item>
                        <x-dropdown.item wire:click="analyses()">
                            {{ __('student.analysis') }}
                        </x-dropdown.item>
                    </div>
                @endif
                <x-dropdown.item type="link" href="{{ route('student.dashboard.logout') }}" selid="logout-btn">
                    {{ __('auth.logout') }}
                </x-dropdown.item>
                @if(!Auth::user()->guest)
                    <x-dropdown.item wire:click="$emit('openModal', 'change-password')">
                        {{ __('header.change_password') }}
                    </x-dropdown.item>
                @endif
            </x-dropdown>
        </div>
    </div>

    @if($this->showKnowledgebankModal)
        <x-modal.iframe wire:model="showKnowledgebankModal" maxWidth="7xl"/>
    @endif
</header>