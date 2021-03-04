<div class="mt-10 flex-1 p-8">

        <div class="content-section mt-10 flex-1 p-8">
        <div><h1>UWLR Fetcher</h1></div>
            <div class="divider"></div>
            <x-button.primary class="mt-6" wire:click="showGrid">Overzicht</x-button.primaryc>

        <div class="flex space-x-4 mt-4">
            <x-input.group label="Klant code" class="w-1/2">
                <x-input.text wire:model="clientCode"></x-input.text>
            </x-input.group>
            <x-input.group label="Klant naam" class="w-1/2">
                <x-input.text wire:model="clientName"></x-input.text>
            </x-input.group>
        </div>
        <div class="flex space-x-4 mt-4">
            <x-input.group label="Schooljaar" class="w-1/2">
                <x-input.text wire:model="schoolYear"></x-input.text>
            </x-input.group>
            <x-input.group label="Brin code" class="w-1/2">
                <x-input.text wire:model="brinCode"></x-input.text>
            </x-input.group>
        </div>
        <div class="flex space-x-4 mt-4">
            <x-input.group label="Dependancecode" class="w-1/2">
                <x-input.text wire:model="dependanceCode"></x-input.text>
            </x-input.group>
        </div>

        <div class="flex space-x-4 mt-4 justify-end">
            <x-button.primary wire:click="fetch">
                <div wire:loading wire:target="fetch">
                    <div class="lds-hourglass"></div>
                </div>
                <div wire:loading.remove wire:target="fetch">
                    Ophalen
                </div>
            </x-button.primary>
        </div>
    </div>
    @if($report)
        <div class="content-section mt-10 flex-1 p-8">
            <div><h1>Report for Identifier {{ $this->resultIdendifier }}</h1></div>
            <div class="divider"></div>
            @foreach($report as $group => $count)
                {{ $group }} {{ $count }}<br>

            @endforeach
        </div>
    @endif

    <x-slot name="footerbuttons">&nbsp;</x-slot>
    <x-slot name="testTakeManager">&nbsp;</x-slot>

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
