<div id="dashboard-body"
     class="mx-4 md:mx-8 lg:mx-12 xl:mx-28 relative"
     x-data=""
     x-init="addRelativePaddingToBody('dashboard-body', 10); makeHeaderMenuActive('student-header-dashboard');"
     x-cloak
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('dashboard-body')"
     wire:ignore.self
>
    <div class="flex my-10">
        <h1>Welkom in jouw Test-Correct Dashboard</h1>
    </div>
    <div class="flex flex-col space-y-4 lg:flex-row lg:space-x-4 lg:space-y-0">
        <div class="flex flex-col lg:w-4/6">
            <div class="flex flex-col space-y-4">
                <div>
                    <h4>Binnenkort geplande toetsen</h4>
                </div>
                <div class="content-section p-8">
                    <x-table>
                        <x-slot name="head">
                            <x-table.heading width="30">Toets</x-table.heading>
                            <x-table.heading width="20">Vak</x-table.heading>
                            <x-table.heading width="20" textAlign="right">Afname</x-table.heading>
                            <x-table.heading width="15">Type</x-table.heading>
                            <x-table.heading></x-table.heading>
                        </x-slot>
                        <x-slot name="body">
                            @foreach($testTakes as $testTake)
                                <x-table.row>
                                    <x-table.cell>{{ $testTake->test->name }}</x-table.cell>
                                    <x-table.cell>{!! $testTake->test->subject->name !!}</x-table.cell>
                                    <x-table.cell class="text-right">
                                        @if($testTake->time_start == \Carbon\Carbon::today())
                                            <span class="capitalize">vandaag</span>
                                        @else
                                            {{ \Carbon\Carbon::parse($testTake->time_start)->format('d-m-Y') }}
                                        @endif
                                    </x-table.cell>
                                    <x-table.cell>
                                        <x-partials.test-take-type-label type="{{ $testTake->retake }}"/>
                                    </x-table.cell>
                                    <x-table.cell class="text-right">
                                        <x-partials.start-take-button :timeStart=" $testTake->time_start "
                                                                      :status="$testTake->test_take_status_id"
                                                                      uuid="{{$testTake->uuid}}"/>
                                    </x-table.cell>
                                </x-table.row>
                            @endforeach
                        </x-slot>
                    </x-table>
                </div>
                <div class="flex">
                    <x-button.primary class="ml-auto" type="link" href="{{ route('student.tests') }}">
                        <span>Geplande toetsen</span>
                        <x-icon.chevron/>
                    </x-button.primary>
                </div>
            </div>

            <div class="flex flex-col space-y-4">
                <div>
                    <h4>Laatst behaalde cijfers</h4>
                </div>
                <div class="content-section p-8">
                    <x-table>
                        <x-slot name="head">
                            <x-table.heading width="30">Toets</x-table.heading>
                            <x-table.heading width="20">Vak</x-table.heading>
                            <x-table.heading width="20" textAlign="right">Afname</x-table.heading>
                            <x-table.heading width="15">Type</x-table.heading>
                            <x-table.heading>Cijfer</x-table.heading>
                        </x-slot>
                        <x-slot name="body">
                            @foreach($ratings as $rating)
                                <x-table.row>
                                    <x-table.cell>{{ $rating->name }}</x-table.cell>
                                    <x-table.cell>{!! $rating->subject_id !!}</x-table.cell>
                                    <x-table.cell class="text-right">
                                        @if($rating->time_start == \Carbon\Carbon::today())
                                            <span class="capitalize">vandaag</span>
                                        @else
                                            {{ \Carbon\Carbon::parse($rating->time_start)->format('d-m-Y') }}
                                        @endif
                                    </x-table.cell>
                                    <x-table.cell>
                                        <x-partials.test-take-type-label type="{{ $rating->retake }}"/>
                                    </x-table.cell>
                                    <x-table.cell class="text-right">
                                        <span class="px-2 py-1 text-sm rounded-full {!! $this->getBgColorForRating($rating->rating) !!}">
                                            {!! round($rating->rating, 1) !!}
                                        </span>
                                    </x-table.cell>
                                </x-table.row>
                            @endforeach
                        </x-slot>
                    </x-table>
                </div>
                <div class="flex">
                    <x-button.primary class="ml-auto" type="link"
                                      href="{{ route('student.tests', ['tab' => 'grades']) }}">
                        <span>Bekijk cijfers</span>
                        <x-icon.chevron/>
                    </x-button.primary>
                </div>
            </div>
        </div>

        <div class="flex flex-1">
            <div class="flex flex-col space-y-4">
                <div>
                    <h4>Laatste berichten</h4>
                </div>
                <div class="content-section p-6 divide-y-2 ">
                    <div class="flex border-system-base">
                        <div class="flex flex-col flex-1 p-2 pt-4 text-md space-y-2">
                            <h6>Scholen sluiten weer</h6>
                            <p>Vanaf 16 december 2020 sluiten alle scholen weer op last van de Overheid. Dit betekent
                                dat we weer starten met leren en toetsen op afstand.</p>
                            <x-button.text-button>
                                <span>Lees meer</span>
                                <x-icon.arrow></x-icon.arrow>
                            </x-button.text-button>
                        </div>
                    </div>

                    <div class="flex border-system-base">
                        <div class="flex flex-col flex-1 p-2 pt-4 text-md space-y-2">
                            <h6>Scholen sluiten weer</h6>
                            <p>Vanaf 16 december 2020 sluiten alle scholen weer op last van de Overheid. Dit betekent
                                dat we weer starten met leren en toetsen op afstand.</p>
                            <x-button.text-button>
                                <span>Lees meer</span>
                                <x-icon.arrow></x-icon.arrow>
                            </x-button.text-button>
                        </div>
                    </div>

                    <div class="flex border-system-base">
                        <div class="flex flex-col flex-1 p-2 pt-4 text-md space-y-2">
                            <h6>Scholen sluiten weer</h6>
                            <p>Vanaf 16 december 2020 sluiten alle scholen weer op last van de Overheid. Dit betekent
                                dat we weer starten met leren en toetsen op afstand.</p>
                            <x-button.text-button>
                                <span>Lees meer</span>
                                <x-icon.arrow></x-icon.arrow>
                            </x-button.text-button>
                        </div>
                    </div>
                </div>

                <div class="flex">
                    <x-button.primary class="ml-auto">
                        <span>Alle berichten</span>
                        <x-icon.chevron/>
                    </x-button.primary>
                </div>
            </div>
        </div>
    </div>
</div>