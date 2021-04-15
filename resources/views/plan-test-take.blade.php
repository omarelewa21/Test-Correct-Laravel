<div id="planned-body"
     class="relative"
     x-data=""
     x-init="addRelativePaddingToBody('planned-body', 10); makeHeaderMenuActive('student-header-planned');"
     x-cloak
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('planned-body')"
     wire:ignore.self
>
    <div class="absolute top-0 -left-28 h-10 w-screen bg-all-red">

    </div>
    <div class="flex flex-col my-10 space-y-4">
        <div>
            <h1>binnenkort geplande toetsen</h1>
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
                                <x-partials.invigilator-list :invigilators="$this->giveInvigilatorNamesFor($testTake)"/>
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
                                <x-partials.start-take-button :timeStart=" $testTake->time_start " :status="$testTake->test_take_status_id" uuid="{{$testTake->uuid}}"/>
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
</div>