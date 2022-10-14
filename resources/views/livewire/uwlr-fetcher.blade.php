<div class="mt-10 flex-1 p-8">
    <div class="flex flex-1 justify-between">
        <div><h1>UWLR Fetcher</h1></div>
        <div class="flex-shrink-0">
            <x-button.primary class="mb-8" wire:click="showGrid">Overzicht</x-button.primary>
        </div>
    </div>

    <div class="content-section p-8">
        <div class="flex space-x-4 mt-4">
            <x-input.group label="DataSource" class="w-1/2">
                <x-input.select wire:model.lazy="currentSource">
                    @foreach($this->uwlrDatasource as $key => $source)
                        <option value="{{ $key }}" wire:key="{{ $source['brin_code'].$key }}">{{ $source['name']  }}</option>
                    @endforeach
                </x-input.select>
            </x-input.group>
            <div class="w-1/2"></div>
        </div>
        <div class="flex space-x-4 mt-4">
            <x-input.group label="Klant code" class="w-1/2">
                <x-input.text wire:model="clientCode" disabled></x-input.text>
            </x-input.group>
            <x-input.group label="Klant naam" class="w-1/2">
                <x-input.text wire:model="clientName" disabled></x-input.text>
            </x-input.group>
        </div>
        <div class="flex space-x-4 mt-4">
            <x-input.group label="Schooljaar" class="w-1/2">
                @error('no_school_years')
                    <span class="error text-xs p-2 mt-2 rounded-md">{{ $message }}</span>
                @enderror
                <x-input.select wire:model="schoolYear">
                    @foreach($this->schoolYears as $schoolYear)
                        <option value="{{ $schoolYear }}" @if($loop->first) checked="true" @endif wire:key="{{ $schoolYear.$loop->index }}">{{ $schoolYear }}</option>
                    @endforeach
                </x-input.select>
            </x-input.group>
            <x-input.group label="Brin code" class="w-1/2">
                <x-input.text wire:model="brinCode" disabled></x-input.text>
            </x-input.group>
        </div>
        <div class="flex space-x-4 mt-4">
            <x-input.group label="Dependancecode" class="w-1/2">
                <x-input.text wire:model="dependanceCode" disabled></x-input.text>
            </x-input.group>
            <div class="w-1/2"></div>
        </div>

        <div class="flex space-x-4 mt-4 justify-end">
            <x-button.primary wire:click="fetch" class="space-x-0">
                <div wire:loading wire:target="fetch">
                    <div class="lds-hourglass"></div>
                </div>
                <span wire:loading.remove wire:target="fetch">Ophalen</span>
            </x-button.primary>
        </div>
    </div>
    @if($report)
        <div class="flex flex-1 justify-between mt-8">
            <div><h1>Report for Identifier {{ $this->resultIdendifier }}</h1></div>
            <div class="flex-shrink-0">
                <x-button.primary class="mb-8" wire:click="showGridWithModal">Details</x-button.primary>
            </div>
        </div>
        <div class="content-section flex-1 p-8">
            <x-table>
                <x-slot name="head">
                    <x-table.heading>Key</x-table.heading>
                    <x-table.heading>Count</x-table.heading>
                </x-slot>
                <x-slot name="body">
                    @forelse($report as $group => $count)
                        <x-table.row>
                            <x-table.heading>{{ $group }}</x-table.heading>
                            <x-table.cell>{{ $count }}</x-table.cell>
                        </x-table.row>
                        @empty
                        <x-table.heading>&nbsp;</x-table.heading>
                        <x-table.cell>geen resultaten</x-table.cell>
                    @endforelse
                </x-slot>
            </x-table>
        </div>
    @endif

    <x-slot name="footerbuttons">&nbsp;</x-slot>

    <style>
        .lds-hourglass {
            display: inline-block;
            position: relative;
            width: 40px;
            height: 40px;
        }

        .lds-hourglass:after {
            content: " ";
            display: block;
            border-radius: 50%;
            width: 0;
            height: 0;
            margin: 8px;
            box-sizing: border-box;
            border: 12px solid #fff;
            border-color: #fff transparent #fff transparent;
            animation: lds-hourglass 1.2s infinite;
        }

        @keyframes lds-hourglass {
            0% {
                transform: rotate(0);
                animation-timing-function: cubic-bezier(0.55, 0.055, 0.675, 0.19);
            }
            50% {
                transform: rotate(900deg);
                animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);
            }
            100% {
                transform: rotate(1800deg);
            }
        }
    </style>
</div>
