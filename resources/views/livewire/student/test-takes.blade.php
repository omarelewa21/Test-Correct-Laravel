<div id="planned-body"
     x-data="{ activeTab: @entangle('tab') }"
     x-init="addRelativePaddingToBody('planned-body'); makeHeaderMenuActive('student-header-tests');"
     x-cloak
     class="w-full flex flex-col items-center"
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('planned-body')"
     wire:ignore.self
>
    <div class="flex w-full justify-center border-b border-system-secondary px-4 lg:px-8 xl:px-24">
        <div class="flex w-full menu">
            <div wire:click="changeActiveTab('{{ $this->plannedTab }}')">
                <x-button.text-button class="px-2 border-0 {{ $this->plannedTab === $tab ? 'active' : '' }}" withHover="true">
                    <span>{{ __('student.planned') }}
                    </span>
                </x-button.text-button>
            </div>
            <div wire:click="changeActiveTab('{{ $this->discussTab }}')">
                <x-button.text-button class="px-2 border-0 {{ $this->discussTab === $tab ? 'active' : '' }}" withHover="true">
                    <span>{{ __('student.discuss') }}
                    </span>
                </x-button.text-button>
            </div>
            <div wire:click="changeActiveTab('{{ $this->reviewTab }}')">
                <x-button.text-button class="px-2 border-0 {{ $this->reviewTab === $tab ? 'active' : '' }}" withHover="true">
                    <span>{{ __('student.review') }}
                    </span>
                </x-button.text-button>
            </div>
            <div wire:click="changeActiveTab('{{ $this->gradedTab }}')">
                <x-button.text-button class="px-2 border-0 {{ $this->gradedTab === $tab ? 'active' : '' }}" withHover="true">
                    <span>{{ __('student.results') }}
                    </span>
                </x-button.text-button>
            </div>
        </div>
    </div>
    <div class="flex flex-col my-10 w-full px-4 lg:px-8 xl:px-24">
        <div class="w-full">
            <div x-show="activeTab === '{{ $this->plannedTab }}'"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
                 class="flex flex-col space-y-4">
                <livewire:student.planned/>
            </div>
            <div x-show="activeTab === '{{ $this->discussTab }}'"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
                 class="flex flex-col space-y-4">
                <livewire:student.discuss/>
            </div>
            <div x-show="activeTab === '{{ $this->reviewTab }}'"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
                 class="flex flex-col space-y-4">
                <livewire:student.review/>
            </div>
            <div x-show="activeTab === '{{ $this->gradedTab }}'"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
                 class="flex flex-col space-y-4">
                <livewire:student.graded/>
            </div>
        </div>
    </div>
</div>