<div id="dashboard-body"
     class="px-4 lg:px-8 xl:px-24 relative w-full pb-10"
     x-data="{}"
     x-init="addRelativePaddingToBody('dashboard-body'); makeHeaderMenuActive('student-header-dashboard');"
     x-cloak
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('dashboard-body')"
     wire:ignore.self
>
    <div class="flex my-10">
        <h1>{{ __('header.Analyses') }}</h1>
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
        <div id="pValueChart" style="width: 500px; height: 400px;"></div>
        <script src="https://cdn.anychart.com/releases/8.11.0/js/anychart-base.min.js" type="text/javascript"></script>
        <div x-data="{


            data:@entangle('dataValues'),
            title: @entangle('title'),

            renderGraph : function () {
                 this.headers = this.data.map( pValue => pValue.subject);
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
                        ).name(this.headers[key]);
                });

                var colorIndex = 0;

                for(var i=0; i < chart.getSeriesCount(); i++) {
                    // reset the color index when color array out of bounds;
                    if (color[colorIndex] === undefined) {
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
    </div>


</div>
