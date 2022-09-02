<div id="dashboard-body"
     class="px-4 lg:px-8 xl:px-24 relative w-full pb-10"
     x-data="{}"
     x-init="addRelativePaddingToBody('dashboard-body'); makeHeaderMenuActive('student-header-dashboard');"
     x-cloak
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('dashboard-body')"
     wire:ignore.self
>
    <div class="flex my-10">
        <h1>@yield('analyses.header.title')</h1>
    </div>
    {{-- Filters--}}
    <div class="flex flex-col pt-4 pb-2">
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
        <div class="hidden">{{ $this->data }}</div>
    </div>
    <div>
        <x-content-section>
            <x-slot name="title">
                @yield('analyses.p-values-per-item.title')
            </x-slot>

            <div id="pValueChart" style="width: 500px; height: 400px;"></div>


            <div x-data="{
            data:@entangle('dataValues'),
            title: @entangle('title'),

            renderGraph : function () {
                 this.headers = this.data.map( pValue => pValue.serie);
                 this.values = this.data.map( pValue => pValue.score);

                var colors = [
                    '#30BC51',
                    '#5043F6',
                    '#ECEE7D',
                    '#6820CE',
                    '#CB110E',
                    '#F79D25',
                    '#1B6112',
                    '#43ACF5',
                    '#E12576',
                    '#24D2C5',
                    '#30BC51',
                    '#5043F6',
                    '#E2DD10',
                    '#6820CE',
                    '#CB110E',
                    '#1B6112',
                    '#F79D25',
                    '#43ACF5',
                    '#E12374',
                    '#24D2C5',
                    '#30BC51',
                    '#5043F6',
                ];

                var data = anychart.data.set([
                    this.values
                ]);


                // create cartesian chart
                var chart = anychart.cartesian();

                // set chart title
                chart.title(this.title);

                // create first series with mapped data and set it's name
                this.headers.forEach((value,key) => {
                    chart.column(
                            data.mapAs({
                                value: key
                            })
                        ).name(value);
                });

                var colorIndex = 0;

                for(var i=0; i < chart.getSeriesCount(); i++) {
                    // reset the color index when color array out of bounds;
                    if (colors[colorIndex] === undefined) {
                        colorIndex = 0;
                    }

                    chart.getSeriesAt(i).fill(colors[i]).stroke(colors[colorIndex]);
                    colorIndex ++;
                }

                // enable categorizedBySeries mode
                chart.categorizedBySeries(true);
                // enable chart legend
                chart.legend(true);
                // rotate xAxis labels;
                var xAxisLabels = chart.xAxis().labels();
                xAxisLabels.rotation(-60)
                // set container id for the chart
                chart.container('pValueChart');
                // initiate chart drawing
                chart.draw();
            },

            init: function() {
             this.renderGraph()
             }
             }"
                 x-on:filters-updated.window="renderGraph"
            >
            </div>
        </x-content-section>

        <BR/>

        <x-content-section>
            <x-slot name="title">
                @yield('analyses.top-items.title')
            </x-slot>
            <div class="flex" wire:ignore>
                @foreach($this->topItems as $modelId => $modelName)
                    <div x-data="{active:false}" class="md:w-1/3 mr-5">
                        <div class="-ml-2 flex space-x-2 pb-2 border-b-3 border-transparent active  items-center question-indicator">
                            <section
                                    class="question-number rounded-full text-center cursor-pointer flex items-center justify-center active"
                            >
                                <span class="align-middle px-1.5">{{ $loop->iteration }}</span>
                            </section>
                            <div class="flex text-lg bold flex-grow border-b-3  border-sysbase ">{{ $modelName }}</div>

                        </div>
                        @foreach($this->taxonomies as $key=> $taxonomy)
                            <div
                                    x-data="expandableGraph({{ $key }}, '{{ $modelId }}', '{{ $taxonomy }}')"
                                    x-on:click="expanded = !expanded"
                                    class="cursor-pointer ml-10"
                            >
                            <span :class="{ 'rotate-svg-90' : expanded }">
                                <x-icon.chevron></x-icon.chevron>
                            </span>
                                <span>{{ __('student.Taxonomy') }} {{ $taxonomy }} {{__('student.Methode') }}</span>
                                <div x-show="expanded">
                                    <div wire:loading wire:target="getData({{ $modelId }}, '{{ $taxonomy }}')">loading</div>
                                    <div :id="containerId"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </x-content-section>
    </div>


</div>
