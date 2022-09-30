<div class="mt-10 flex-1 p-8" id="uwlr-grid">
    <div class="flex flex-1 justify-between">
        <div><h1>Schoollocaties</h1></div> {{-- todo translation--}}
        <div class="flex-shrink-0">
            @if(Auth::user()->isA('Administrator'))
                {{-- link to cake, new school_location --}}
                <x-button.cta class="" wire:click="deleteImportData">Nieuwe schoollocatie</x-button.cta> {{-- todo translation--}}
            @endif
        </div>
    </div>

    @if (session()->has('error'))
        <div class="content-section mt-10 flex-1 p-8 error">
            {!!  session('error')  !!}
        </div>
    @endif

    <div class="content-section mt-10 flex-1 p-8" x-data="{}">

        <div class="flex space-x-4 mt-4">
            <x-table>
                <x-slot name="head">
                    {{-- Klantcode | School | Gemeenschap | Stad | BRIN | LVS | SSO | Licenties | Geactiveerd | Vraagitems | (actions) --}}
                    <x-table.heading :sortable="true" direction="desc">
                        Klantcode
                    </x-table.heading>
                    <x-table.heading>
                        School
                    </x-table.heading>
                    <x-table.heading>
                        Gemeenschap
                    </x-table.heading>
                    <x-table.heading>
                        Stad
                    </x-table.heading>
                    <x-table.heading>
                        BRIN
                    </x-table.heading>
                    <x-table.heading>
                        LVS
                    </x-table.heading>
                    <x-table.heading>
                        SSO
                    </x-table.heading>
                    <x-table.heading>
                        Licenties
                    </x-table.heading>
                    <x-table.heading >
                        Geactiveerd
                    </x-table.heading>
                    <x-table.heading>
                        Vraagitems
                    </x-table.heading>
                    <x-table.heading width="120px">
                        &nbsp;
                    </x-table.heading>

                </x-slot>
                <x-slot name="body">

{{--                    @foreach($schoolLocations as $schoolLocation)--}}
                        <x-table.row>
                            <x-table.cell>

                            </x-table.cell>
                            <x-table.cell>

                            </x-table.cell>
                            <x-table.cell>

                            </x-table.cell>
                            <x-table.cell>

                            </x-table.cell>
                            <x-table.cell>

                            </x-table.cell>
                            <x-table.cell>

                            </x-table.cell>
                            <x-table.cell>

                            </x-table.cell>
                            <x-table.cell>

                            </x-table.cell>
                            <x-table.cell>

                            </x-table.cell>
                            <x-table.cell>

                            </x-table.cell>
                            <x-table.cell>
                                        <x-button.text-button
                                                wire:click="">Open {{-- todo translation--}}
                                        </x-button.text-button>
                            </x-table.cell>
                        </x-table.row>
{{--                    @endforeach--}}
                </x-slot>
            </x-table>

        </div>


        <x-slot name="footerbuttons">&nbsp;</x-slot>
        <x-slot name="testTakeManager">&nbsp;</x-slot>
    </div>

</div>

{{-- Grid current vs new: --}}
{{-- Klantcode | School | Gemeenschap | Stad | Licenties | Geactiveerd | Vraagitems | (actions) --}}
{{-- Klantcode | School | Gemeenschap | Stad | BRIN | LVS | SSO | Licenties | Geactiveerd | Vraagitems | (actions) --}}

{{-- Sort and Filter. sort all columns, filter not all--}}
{{-- Klantcode, School, Gemeenschap, Klant*, Brin*, LVS*, SSO*  *is selectbox--}}

{{-- todo: administrator can create new school on this screen, account manager can't --}}