<div class="mt-10 flex-1 p-8">
    <div class="flex flex-1 justify-between">
        <div><h1>UWLR Grid</h1></div>
        <div class="flex-shrink-0"><x-button.primary class="" wire:click="newImport">Import</x-button.primary></div>
    </div>
    <div class="content-section mt-10 flex-1 p-8">

        <div class="flex space-x-4 mt-4">
            <x-table>
                <x-slot name="head">
                    <x-table.heading>
                        Datum
                    </x-table.heading>
                    <x-table.heading>
                        Brin
                    </x-table.heading>
                    <x-table.heading>
                        Naam
                    </x-table.heading>
                    <x-table.heading>
                        Code
                    </x-table.heading>
                    <x-table.heading>
                        &nbsp;
                    </x-table.heading>
                </x-slot>
                <x-slot name="body">

                    @foreach($resultSets as $set)
                        <x-table.row>
                            <x-table.cell>
                                {{ $set->created_at->diffForHumans() }}
                            </x-table.cell>
                            <x-table.cell>
                                {{ $set->brin_code }}
                            </x-table.cell>
                            <x-table.cell>
                                {{ $set->client_name }}
                            </x-table.cell>
                            <x-table.cell>
                                {{ $set->client_code }}
                            </x-table.cell>
                            <x-table.cell>
                                <x-button.text-button wire:click="activateResult({{ $set->getKey() }})">Modal
                                </x-button.text-button>
                            </x-table.cell>
                        </x-table.row>
                    @endforeach
                </x-slot>
            </x-table>

        </div>


        <x-slot name="footerbuttons">&nbsp;</x-slot>
        <x-slot name="testTakeManager">&nbsp;</x-slot>
    </div>
    <x-modal wire:model="showImportModal" maxWidth="7xl">
        <x-slot name="title">Import</x-slot>
        <x-slot name="body">
            <div class="hidden sm:block">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        @if($this->activeResult)
                            @foreach($this->activeResult as $key => $item )
                                @if($key == $this->modalActiveTab)
                                    <a href="#"
                                       wire:click="$set('modalActiveTab','{{ $key }}')"
                                       class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                       aria-current="page">
                                        {{ $key }}
                                    </a>
                                @else
                                    <a href="#"
                                       wire:click="$set('modalActiveTab','{{ $key }}')"
                                       class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                        {{ $key }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    </nav>
                    <div class="mt-8 mb-8">
                        <x-table>
                            <x-slot name="head">
                                @foreach($this->modalActiveTabHtml as $object)
                                    @if($loop->first)
                                        @foreach($object as $prop => $value)
                                            <x-table.heading>
                                                {{ $prop }}
                                            </x-table.heading>
                                        @endforeach
                                    @endif
                                @endforeach
                            </x-slot>
                            <x-slot name="body">
                                @foreach($this->modalActiveTabHtml as $object)
                                    <x-table.row>
                                        @foreach($object as $value)
                                            <x-table.cell>{{ $value }}</x-table.cell>
                                        @endforeach
                                    </x-table.row>
                                @endforeach
                            </x-slot>
                        </x-table>
                    </div>
                </div>
            </div>
        </x-slot>
        <x-slot name="actionButton">me</x-slot>
    </x-modal>
</div>



