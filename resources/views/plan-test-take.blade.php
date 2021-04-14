<div>
    <x-partials.header.dashboard/>

    <main>
        <div class="m-4 lg:mx-28 lg:mt-40 space-y-6">
            <div>
                <h1>Geplande toetsen</h1>
            </div>
            <div class="content-section p-8">
                <x-table>
                    <x-slot name="head">
                        <x-table.heading width="20" sortable="true">Toets</x-table.heading>
                        <x-table.heading width="5">Vragen</x-table.heading>
                        <x-table.heading width="12">Surveillanten</x-table.heading>
                        <x-table.heading width="12">Inplanner</x-table.heading>
                        <x-table.heading width="10" sortable="true">Vak</x-table.heading>
                        <x-table.heading width="8" sortable="true">Afname</x-table.heading>
                        <x-table.heading width="3" sortable="true">Weging</x-table.heading>
                        <x-table.heading width="8" sortable="true">Type</x-table.heading>
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
                                <x-table.cell>{{ $testTake->user_id }}</x-table.cell>
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
                                    @if($testTake->time_start == \Carbon\Carbon::today())
                                        <x-button.cta size="sm">Maken</x-button.cta>
                                    @else
                                        <span class="italic">gepland</span>
                                    @endif
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
    </main>
</div>