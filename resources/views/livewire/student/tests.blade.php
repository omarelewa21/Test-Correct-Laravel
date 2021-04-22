<div id="planned-body"
     x-data="{ activeTab: @entangle('activeTab') }"
     x-init="addRelativePaddingToBody('planned-body'); makeHeaderMenuActive('student-header-tests');"
     x-cloak
     :class="{'student-bg': activeTab === {{ $this->waitingroomTab }}}"
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('planned-body')"
     wire:ignore.self
>
    <div class="border-b border-system-secondary">
        <div class="flex mx-4 md:mx-8 lg:mx-12 xl:mx-28 space-x-4">
            <div class="py-2" :class="{'border-b-2 border-system-base': activeTab === {{ $this->plannedTab }} || activeTab === {{ $this->waitingroomTab }}}"
                 wire:click="changeActiveTab({{ $this->plannedTab }})">
                <x-button.text-button>Gepland</x-button.text-button>
            </div>
            <div class="py-2" :class="{'border-b-2 border-system-base': activeTab === {{ $this->discussTab }}}"
                 wire:click="changeActiveTab({{ $this->discussTab }})">
                <x-button.text-button>Bespreken</x-button.text-button>
            </div>
            <div class="py-2" :class="{'border-b-2 border-system-base': activeTab === {{ $this->reviewTab }}}"
                 wire:click="changeActiveTab({{ $this->reviewTab }})">
                <x-button.text-button>Inzien</x-button.text-button>
            </div>
            <div class="py-2" :class="{'border-b-2 border-system-base': activeTab === {{ $this->gradedTab }}}"
                 wire:click="changeActiveTab({{ $this->gradedTab }})">
                <x-button.text-button>Becijferd</x-button.text-button>
            </div>
        </div>
    </div>
    <div class="flex flex-col my-10 mx-4 md:mx-8 lg:mx-12 xl:mx-28 ">
        <div x-show="activeTab === {{ $this->plannedTab }}" class="flex flex-col space-y-4">
            <div>
                <h1>Geplande toetsen</h1>
            </div>
            <div class="content-section p-8">
                <x-table>
                    <x-slot name="head">
                        <x-table.heading width="20" sortable="true">Toets</x-table.heading>
                        <x-table.heading width="5">Vragen</x-table.heading>
                        <x-table.heading width="8">Surveillanten</x-table.heading>
                        <x-table.heading width="8">Inplanner</x-table.heading>
                        <x-table.heading width="10" sortable="true">Vak</x-table.heading>
                        <x-table.heading width="8" sortable="true">Afname</x-table.heading>
                        <x-table.heading width="3" sortable="true">Weging</x-table.heading>
                        <x-table.heading width="10" sortable="true">Type</x-table.heading>
                        <x-table.heading sortable="true"></x-table.heading>
                    </x-slot>
                    <x-slot name="body">
                        @foreach($testTakes as $testTake)

                            <x-table.row>
                                <x-table.cell>{{ $testTake->test->name }}</x-table.cell>
                                <x-table.cell class="text-right">{{ $testTake->test->question_count }}</x-table.cell>
                                <x-table.cell>
                                    <x-partials.invigilator-list
                                            :invigilators="$this->giveInvigilatorNamesFor($testTake)"/>
                                </x-table.cell>
                                <x-table.cell>{{ $testTake->user->getFullNameWithAbbreviatedFirstName() }}</x-table.cell>
                                <x-table.cell>Software Development</x-table.cell>
                                <x-table.cell class="text-right">
                                    @if($testTake->time_start == \Carbon\Carbon::today())
                                        <span class="capitalize">vandaag</span>
                                    @else
                                        {{ \Carbon\Carbon::parse($testTake->time_start)->format('d-m-Y') }}
                                    @endif
                                </x-table.cell>
                                <x-table.cell class="text-right">{{ $testTake->weight }}</x-table.cell>
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
            <div>
                {{ $testTakes->links('components.partials.tc-paginator') }}
            </div>
        </div>
        <div x-show="activeTab === {{ $this->discussTab }}" class="flex flex-col space-y-4">
            <div>
                <h1>Te bespreken toetsen</h1>
            </div>
        </div>
        <div x-show="activeTab === {{ $this->reviewTab }}" class="flex flex-col space-y-4">
            <div>
                <h1>Toetsen om in te zien</h1>
            </div>
        </div>
        <div x-show="activeTab === {{ $this->gradedTab }}" class="flex flex-col space-y-4">
            <div>
                <h1>Becijferde toetsen</h1>
            </div>
        </div>
        <div x-show="activeTab === {{ $this->waitingroomTab }}" class="flex flex-col space-y-4">
            <div class="flex body2 bold items-center space-x-2">
                <div class="flex items-center space-x-2"><x-icon.schedule/><span>Gepland</span></div>
                <x-icon.chevron-small class="opacity-50 w-2 h-3"/>
                <div class="flex items-center space-x-2 opacity-50"><x-icon.discuss/><span>Bespreken</span></div>
                <x-icon.chevron-small class="opacity-50"/>
                <div class="flex items-center space-x-2 opacity-50"><x-icon.preview/><span>Inzien</span></div>
                <x-icon.chevron-small class="opacity-50"/>
                <div class="flex items-center space-x-2 opacity-50"><x-icon.grade/><span>Becijferd</span></div>
            </div>
            <div>
                <x-button.text-button class="rotate-svg-180" wire:click="changeActiveTab({{$this->plannedTab}})">
                    <x-icon.arrow/>
                    <span class="text-[32px]">{{ $waitingTestTake->test->name }}</span>
                </x-button.text-button>
            </div>
            <div>
                <div class="grid gap-4 grid-cols-2 md:grid-cols-3 lg:grid-cols-4 body2">

                    <div class="flex flex-col space-y-2">
                        <span>Grid heading</span>
                        <h6>Grid body</h6>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <span>Grid heading</span>
                        <h6>Grid body</h6>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <span>Grid heading</span>
                        <h6>Grid body</h6>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <span>Grid heading</span>
                        <h6>Grid body</h6>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <span>Grid heading</span>
                        <h6>Grid body</h6>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <span>Grid heading</span>
                        <h6>Grid body</h6>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <span>Grid heading</span>
                        <h6>Grid body</h6>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <span>Grid heading</span>
                        <h6>Grid body</h6>
                    </div>

                </div>
            </div>
            <div class="flex w-full">
                <div class="divider flex flex-1"></div>
                <div class="mx-4">Wachten op docent</div>
                <div class="divider flex flex-1"></div>
            </div>
            <div class="flex w-full justify-center">
                <x-illustrations.waiting-room/>
            </div>
        </div>
    </div>
</div>