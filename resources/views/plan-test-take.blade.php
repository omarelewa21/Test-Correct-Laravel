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
                                    <x-table.cell class="text-right">123</x-table.cell>
                                    <x-table.cell>L. Hamilton</x-table.cell>
                                    <x-table.cell>D. Ricciardo</x-table.cell>
                                    <x-table.cell>Software Development</x-table.cell>
                                    <x-table.cell class="text-right">05-09-1996</x-table.cell>
                                    <x-table.cell class="text-right">25</x-table.cell>
                                    <x-table.cell>
                                        <span class="text-xs uppercase bold px-2.5 py-1 bg-off-white base">Standaard</span>
                                    </x-table.cell>
                                    <x-table.cell class="text-right">
                                        <x-button.cta size="sm">Maken</x-button.cta>
                                    </x-table.cell>
                                </x-table.row>
                        @endforeach
                    </x-slot>
                </x-table>
                            {{ $testTakes->links() }}
            </div>

        </div>
    </main>
</div>