<div id="planned-body"
     x-data="{ activeTab: @entangle('tab') }"
     x-init="addRelativePaddingToBody('planned-body'); makeHeaderMenuActive('student-header-tests');"
     x-cloak
     class="w-full flex flex-col items-center"
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('planned-body')"
     wire:ignore.self
>
    <div class="flex w-full justify-center border-b border-system-secondary">
        <div class="flex w-full mx-4 lg:mx-8 xl:mx-12 max-w-7xl space-x-4">
            <div class="py-2"
                 :class="{'border-b-2 border-system-base border-primary-hover': activeTab === '{{ $this->plannedTab }}'}"
                 wire:click="changeActiveTab('{{ $this->plannedTab }}')"
            >
                <x-button.text-button>{{ __('student.planned') }}</x-button.text-button>
            </div>
            <div class="py-2"
                 :class="{'border-b-2 border-system-base border-primary-hover': activeTab === '{{ $this->discussTab }}'}"
                 wire:click="changeActiveTab('{{ $this->discussTab }}')">
                <x-button.text-button>{{ __('student.discuss') }}</x-button.text-button>
            </div>
            <div class="py-2"
                 :class="{'border-b-2 border-system-base border-primary-hover': activeTab === '{{ $this->reviewTab }}'}"
                 wire:click="changeActiveTab('{{ $this->reviewTab }}')">
                <x-button.text-button>{{ __('student.review') }}</x-button.text-button>
            </div>
            <div class="py-2"
                 :class="{'border-b-2 border-system-base border-primary-hover': activeTab === '{{ $this->gradedTab }}'}"
                 wire:click="changeActiveTab('{{ $this->gradedTab }}')">
                <x-button.text-button>{{ __('student.graded') }}</x-button.text-button>
            </div>
        </div>
    </div>
    <div class="flex flex-col my-10 w-full">
        <div class="w-full px-4 lg:px-8 xl:px-12">
            <div x-show="activeTab === '{{ $this->plannedTab }}'" class="flex flex-col space-y-4 mx-auto max-w-7xl">
                <livewire:student.planned/>
            </div>
            <div x-show="activeTab === '{{ $this->discussTab }}'" class="flex flex-col space-y-4 mx-auto max-w-7xl">
                <livewire:student.discuss/>
            </div>
            <div x-show="activeTab === '{{ $this->reviewTab }}'" class="flex flex-col space-y-4 mx-auto max-w-7xl">
                <livewire:student.review/>
            </div>
            <div x-show="activeTab === '{{ $this->gradedTab }}'" class="flex flex-col space-y-4 mx-auto max-w-7xl">
                <livewire:student.graded/>
            </div>
        </div>
    </div>
</div>