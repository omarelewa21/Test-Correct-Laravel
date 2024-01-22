<div id="planned-body"
     x-data="{ activeTab: @entangle('tab') }"
     x-init="makeHeaderMenuActive('student-header-tests');"
     x-cloak
     class="w-full flex flex-col items-center"
     wire:ignore.self
>
    <div class="flex w-full justify-center border-b border-system-secondary px-4 lg:px-8 xl:px-24">
        <div class="flex w-full menu">
            <div wire:click="changeActiveTab('{{ $this->plannedTab }}')">
                <x-button.default @class([
                                    "px-2 border-0 hover:text-primary hover:bg-primary/5 active:bg-primary/10 focus:bg-primary/10 border-b-2 border-transparent",
                                    "text-primary border-primary" =>  $this->plannedTab === $tab
                                  ])
                                  size="md"
                >
                    <span>{{ __('student.planned') }}</span>
                </x-button.default>
            </div>
            <div wire:click="changeActiveTab('{{ $this->discussTab }}')">
                <x-button.default @class(["px-2 border-0 hover:text-primary hover:bg-primary/5 active:bg-primary/10 focus:bg-primary/10 border-b-2 border-transparent", "text-primary border-primary" =>  $this->discussTab === $tab ])
                                  size="md"
                >
                    <span>{{ __('student.discuss') }}</span>
                </x-button.default>
            </div>
            <div wire:click="changeActiveTab('{{ $this->reviewTab }}')">
                <x-button.default @class(["px-2 border-0 hover:text-primary hover:bg-primary/5 active:bg-primary/10 focus:bg-primary/10 border-b-2 border-transparent", "text-primary border-primary" =>  $this->reviewTab === $tab ])
                                  size="md"
                >
                    <span>{{ __('student.review') }}</span>
                </x-button.default>
            </div>
            <div wire:click="changeActiveTab('{{ $this->gradedTab }}')">
                <x-button.default @class(["px-2 border-0 hover:text-primary hover:bg-primary/5 active:bg-primary/10 focus:bg-primary/10 border-b-2 border-transparent", "text-primary border-primary" =>  $this->gradedTab === $tab ])
                                  size="md"
                >
                    <span>{{ __('student.results') }}</span>
                </x-button.default>
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
                <livewire:student.planned />
            </div>
            <div x-show="activeTab === '{{ $this->discussTab }}'"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
                 class="flex flex-col space-y-4">
                <livewire:student.discuss />
            </div>
            <div x-show="activeTab === '{{ $this->reviewTab }}'"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
                 class="flex flex-col space-y-4">
                <livewire:student.review />
            </div>
            <div x-show="activeTab === '{{ $this->gradedTab }}'"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
                 class="flex flex-col space-y-4">
                <livewire:student.graded />
            </div>
        </div>
    </div>
</div>