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
        <x-content-section>
            <x-slot name="title">
                {{ __('student.p waarde') }}
            </x-slot>

            <div id="pValueChart" style="width: 500px; height: 400px;"></div>

            <script src="https://cdn.anychart.com/releases/8.11.0/js/anychart-base.min.js"
                    type="text/javascript"></script>
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
                {{ __('student.top 3 vakken om aan te werken') }}
            </x-slot>
            <div class="flex">
                <div x-data="{active:1}" class="md:w-1/3 mr-5">
                    <div class="-ml-2 flex space-x-2 pb-2 border-b-3 border-transparent active  items-center question-indicator">
                        <section
                                class="question-number rounded-full text-center cursor-pointer flex items-center justify-center active"
                        >
                            <span class="align-middle px-1.5">1</span>
                        </section>
                        <div class="flex text-lg bold flex-grow border-b-3  border-sysbase ">Biologie</div>

                    </div>
                    <div x-data="{
                    id: 1,
                    get expanded() {
                        return this.active === this.id
                    },
                    set expanded(value) {
                        this.active = value ? this.id: null
                    }
                }"
                         x-on:click="expanded = !expanded"
                         class="cursor-pointer ml-10"
                    >
                    <span :class="{ 'rotate-svg-90' : expanded }">
                        <x-icon.chevron></x-icon.chevron>
                    </span>
                        <span>Taxonomie RTTI methode</span>
                        <div x-show="expanded"><h1>Grafiek</h1>

                            <div id="barChart"></div>
                        </div>
                    </div>
                    <div x-data="{
                    id: 2,
                    get expanded() {
                        return this.active === this.id
                    },
                    set expanded(value) {
                        this.active = value ? this.id: null
                    }
                }"
                         x-on:click="expanded = !expanded"
                         class="cursor-pointer ml-10"
                    >
                    <span :class="{ 'rotate-svg-90' : expanded }">
                        <x-icon.chevron></x-icon.chevron>
                    </span>
                        <span>Taxonomie Bloom methode</span>
                        <div x-show="expanded"><h1>Grafiek</h1></div>
                    </div>
                    <div x-data="{
                    id: 3,
                    get expanded() {
                        return this.active === this.id
                    },
                    set expanded(value) {
                        this.active = value ? this.id: null
                    }
                }" x-on:click="expanded = !expanded"
                         class="cursor-pointer ml-10"
                    >
                   <span :class="{ 'rotate-svg-90' : expanded }">
                        <x-icon.chevron></x-icon.chevron>
                    </span>
                        <span>Taxonomie Miller methode</span>
                        <div x-show="expanded"><h1>Grafiek</h1></div>
                    </div>
                </div>

                <div x-data="{active:null}" class="md:w-1/3 mr-5">
                    <div class="-ml-2 flex space-x-2 pb-2 border-b-3 border-transparent active  items-center question-indicator">
                        <section
                                class="question-number rounded-full text-center cursor-pointer flex items-center justify-center active"
                        >
                            <span class="align-middle px-1.5">1</span>
                        </section>
                        <div class="flex text-lg bold flex-grow border-b-3  border-sysbase ">Biologie</div>

                    </div>
                    <div x-data="{
                    id: 1,
                    get expanded() {
                        return this.active === this.id
                    },
                    set expanded(value) {
                        this.active = value ? this.id: null
                    }
                }"
                         x-on:click="expanded = !expanded"
                         class="cursor-pointer ml-10"
                    >
                    <span :class="{ 'rotate-svg-90' : expanded }">
                        <x-icon.chevron></x-icon.chevron>
                    </span>
                        <span>Taxonomie RTTI methode</span>
                        <div x-show="expanded"><h1>Grafiek</h1></div>
                    </div>
                    <div x-data="{
                    id: 2,
                    get expanded() {
                        return this.active === this.id
                    },
                    set expanded(value) {
                        this.active = value ? this.id: null
                    }
                }"
                         x-on:click="expanded = !expanded"
                         class="cursor-pointer ml-10"
                    >
                    <span :class="{ 'rotate-svg-90' : expanded }">
                        <x-icon.chevron></x-icon.chevron>
                    </span>
                        <span>Taxonomie Bloom methode</span>
                        <div x-show="expanded"><h1>Grafiek</h1></div>
                    </div>
                    <div x-data="{
                    id: 3,
                    get expanded() {
                        return this.active === this.id
                    },
                    set expanded(value) {
                        this.active = value ? this.id: null
                    }
                }" x-on:click="expanded = !expanded"
                         class="cursor-pointer ml-10"
                    >
                   <span :class="{ 'rotate-svg-90' : expanded }">
                        <x-icon.chevron></x-icon.chevron>
                    </span>
                        <span>Taxonomie Miller methode</span>
                        <div x-show="expanded"><h1>Grafiek</h1></div>
                    </div>
                </div>

                <div x-data="{active:null}" class="md:w-1/3 mr-5">
                    <div class="-ml-2 flex space-x-2 pb-2 border-b-3 border-transparent active  items-center question-indicator">
                        <section
                                class="question-number rounded-full text-center cursor-pointer flex items-center justify-center active"
                        >
                            <span class="align-middle px-1.5">1</span>
                        </section>
                        <div class="flex text-lg bold flex-grow border-b-3  border-sysbase ">Biologie</div>

                    </div>
                    <div x-data="{
                    id: 1,
                    get expanded() {
                        return this.active === this.id
                    },
                    set expanded(value) {
                        this.active = value ? this.id: null
                    }
                }"
                         x-on:click="expanded = !expanded"
                         class="cursor-pointer ml-10"
                    >
                    <span :class="{ 'rotate-svg-90' : expanded }">
                        <x-icon.chevron></x-icon.chevron>
                    </span>
                        <span>Taxonomie RTTI methode</span>
                        <div x-show="expanded"><h1>Grafiek</h1></div>
                    </div>
                    <div x-data="{
                    id: 2,
                    get expanded() {
                        return this.active === this.id
                    },
                    set expanded(value) {
                        this.active = value ? this.id: null
                    }
                }"
                         x-on:click="expanded = !expanded"
                         class="cursor-pointer ml-10"
                    >
                    <span :class="{ 'rotate-svg-90' : expanded }">
                        <x-icon.chevron></x-icon.chevron>
                    </span>
                        <span>Taxonomie Bloom methode</span>
                        <div x-show="expanded"><h1>Grafiek</h1></div>
                    </div>
                    <div x-data="{
                    id: 3,
                    get expanded() {
                        return this.active === this.id
                    },
                    set expanded(value) {
                        this.active = value ? this.id: null
                    }
                }" x-on:click="expanded = !expanded"
                         class="cursor-pointer ml-10"
                    >
                   <span :class="{ 'rotate-svg-90' : expanded }">
                        <x-icon.chevron></x-icon.chevron>
                    </span>
                        <span>Taxonomie Miller methode</span>
                        <div x-show="expanded"><h1>Grafiek</h1></div>
                    </div>
                </div>


            </div>


        </x-content-section>


    </div>

    <script>
        anychart.onDocumentReady(function () {
            var data = [
                ['Reproductie', 0.39],
                ['Toepassen 1', 0.54],
                ['Toepassen 2', 0.2],
                ['Inzicht', 0.1],
            ];

            // create bar chart
            var chart = anychart.bar();

            // turn on chart animation
            //chart.padding([10, 40, 5, 20])
            // set chart title text settings




            // create area series with passed data
            var series = chart.bar(data);
            var tooltip = series
                .tooltip()

            series.stroke('#FF0000').fill('#FF0000')

            tooltip.title(false)
                .separator(false)
                .position('right')
                .anchor('left-center')
                .offsetX(5)
                .offsetY(0)
                .background('#FFFFFF')
                .fontColor('#000000')
                .format(function () {
                    return (
                        'P ' + Math.abs(this.value).toLocaleString()

                    );
                });


            chart.tooltip().positionMode('point');
            // set scale minimum
            chart.xAxis().stroke('#041F74')
            chart.xAxis().stroke('none')

            var yAxis = chart.yAxis()


            // set container id for the chart
            chart.container('barChart');
            // initiate chart drawing
            chart.draw();
        });


    </script>


</div>
