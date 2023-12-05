@php($activeFilterContainer = sprintf("words-%s-active-filters", $this->openTab))
<div x-data="overviewComponent(@entangle('openTab'), @js($activeFilterContainer))"
     x-cloak
     style="transition: visibility 100ms ease"
     class="w-full"
>
    <x-menu.tab.container class="bg-lightGrey">
        <x-menu.tab.allowed-content-sources :content-sources="$allowedTabs"
                                            menu="openTab"
        ></x-menu.tab.allowed-content-sources>
    </x-menu.tab.container>

    <div class="flex w-full max-w-screen-2xl mx-auto  px-8">
        <div class="w-full divide-y divide-secondary z-0">
            {{-- Filters--}}

            <x-partials.versionable-overview-filters :active-filter-container="$activeFilterContainer"
                                                     versionablePrefix="words"
                                                     searchProperty="filters.text"
            />
            {{-- Content --}}
            <x-partials.overview-content-section :$results :pagination="true">
                <x-slot name="resultMessage">
                    {{ trans_choice('general.number-of-results', $results->total(), ['count' => $results->total()]) }}
                </x-slot>

                <x-slot name="header">

                </x-slot>

                <x-slot name="cards">
                    @foreach($results as $word)
                        <x-grid.word-card :$word
                                          wire:loading.class="hidden"
                                          wire:target="filters,clearFilters,$set"
                                          :addable="$this->addable"
                                          :used="in_array($word->getKey(), $this->used)"
                        />
                    @endforeach
                </x-slot>

                <livewire:context-menu.word-card />
            </x-partials.overview-content-section>

        </div>
    </div>
</div>