<div class="w-full">
    <x-sticky-page-title @class([
        'top-[150px]' => $this->viewingAsTeacher(),
        'top-20' => !$this->viewingAsTeacher(),
    ])>
        @yield('analyses.header.title')
    </x-sticky-page-title>
    <div id="dashboard-body"
         class="px-10 relative w-full pb-10"
         x-data="{}"
         x-init="addRelativePaddingToBody('dashboard-body'); makeHeaderMenuActive('student-header-analysis');"
         x-cloak
         x-on:resize.window.debounce.200ms="addRelativePaddingToBody('dashboard-body')"
         wire:ignore.self
    >
        @yield('analyses.page.title')

        {{-- Filters--}}
        <div class="flex flex-col pt-4 pb-2 mb-4 border-b border-secondary">
            <div class="flex w-full items-center">
                <div class="flex flex-wrap  space-x-2 items-center" x-cloak>

                    <x-input.choices-select :multiple="true"
                                            :options="$this->educationLevelYears"
                                            :withSearch="true"
                                            placeholderText="{{ __('general.Leerjaar')}}"
                                            wire:model="filters.educationLevelYears"
                                            wire:key="filter_eduction_level_years"
                                            filterContainer="analyses-active-filters"
                    />
                    <x-input.choices-select :multiple="true"
                                            :options="$this->periods"
                                            :withSearch="true"
                                            placeholderText="{{ __('teacher.Periode')}}"
                                            wire:model="filters.periods"
                                            wire:key="filter_periods"
                                            filterContainer="analyses-active-filters"
                    />
                    <x-input.choices-select :multiple="true"
                                            :options="$this->teachers"
                                            :withSearch="true"
                                            placeholderText="{{ __('general.Docent')}}"
                                            wire:model="filters.teachers"
                                            wire:key="filter_teacher"
                                            filterContainer="analyses-active-filters"
                    />
                </div>

                @if($this->hasActiveFilters())
                    <x-button.text-button class="ml-auto text-base"
                                          size="sm"
                                          @click="document.getElementById('analyses-active-filters').innerHTML = '';$wire.clearFilters()"
                    >
                        <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
                        <x-icon.close-small/>
                    </x-button.text-button>
                @else
                    <x-button.text-button class="ml-auto text-base disabled"
                                          size="sm"
                                          disabled
                    >
                        <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
                        <x-icon.close-small/>
                    </x-button.text-button>
                @endif
            </div>

            <div id="analyses-active-filters"
                 x-data
                 wire:key="filters-container"
                 wire:ignore
                 class="flex flex-wrap gap-2 mt-2 relative"
            >
            </div>

        </div>

        <div>
            @yield('analyses.attainment.description')
            @yield('analyses.general-data')
            @yield('analyses.p-values-graph')
            @yield('analyses.p-values-time-series-graph')
            <BR/>
            @if ($this->displayRankingPanel)
                <x-content-section class="w-full">
                    <x-slot name="title">
                        @yield('analyses.top-items.title')
                    </x-slot>
                    @forelse($this->topItems as $parentKey => $item)
                        @if($loop->first)
                            <div class="flex flex-row">
                                @endif
                                <div
                                        x-data="{
                                    active:false,
                                    async init() {
                                        let ready = await $wire.getFirstActiveForRankingTaxonomy({{ $item->id }});
                                        if (ready !== false) {
                                            // this is a feature of javascript no explicite way to concat integers as a string;
                                            let keyForContainerId = '' + {{ $parentKey+1 }} + (ready+1);
                                            $dispatch('top-item-active-changed{{ $item->id }}', { id: keyForContainerId })
                                        }
                                    }
                                }"
                                        class="md:w-1/3 mr-5"
                                >
                                    <div
                                            class="-ml-2 flex space-x-2 pb-2 border-b-3 border-transparent active  items-center question-indicator">
                                        <section
                                                class="question-number rounded-full text-center cursor-pointer flex items-center justify-center active"
                                        >
                                            <span class="align-middle px-1.5">{{ $loop->iteration }}</span>
                                        </section>
                                        <div
                                                class="flex text-lg bold flex-grow border-b-3  border-sysbase ">{!! $item->title !!} </div>

                                    </div>

                                    @foreach($this->taxonomies as $key=> $taxonomy)
                                        <div
                                                x-data="expandableGraphForGeneral({{ ($parentKey+1).$loop->iteration }}, '{{ $item->id }}', '{{ $taxonomy['name'] }}', 'expandableGraph')"
                                                x-on:top-item-active-changed{{  $item->id }}.window="if (id == $event.detail.id)  { expanded = true}"
                                                x-on:click="expanded = !expanded"
                                                x-on:filters-updated.window="if (expanded) {updateGraph(true)}"
                                                class="cursor-pointer ml-10"
                                        >
                                    <span :class="{ 'rotate-svg-90' : expanded }">
                                        <x-icon.chevron/>
                                    </span>
                                            <span>{{ __('student.Taxonomy') }} {{ $taxonomy['name'] }} {{__('student.Methode') }}</span>
                                            <div x-show="expanded">
                                                <div
                                                        wire:loading
                                                        wire:target="getData({{ $item->id }}, '{{ $taxonomy['name'] }}')"
                                                >
                                                    loading
                                                </div>
                                                <div wire:ignore :id="containerId"
                                                     style="height: {{ $taxonomy['height'] }}"
                                                >
                                                    <div x-show="showEmptyState" class="relative empty-state">
                                                        <x-empty-taxonomy-graph></x-empty-taxonomy-graph>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if($loop->last)
                            </div>
                        @endif
                    @empty
                        <div class="min-h-[300px] relative">
                            <x-empty-graph show="true"></x-empty-graph>
                        </div>
                    @endforelse

                </x-content-section>
            @endif
        </div>
    </div>
</div>
