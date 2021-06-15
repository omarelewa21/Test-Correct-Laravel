<div class="mt-10 flex-1 p-8">
    <div class="flex flex-1 justify-between">
        <div><h1>UWLR Grid</h1></div>
        <div class="flex-shrink-0">
            <x-button.cta class="" wire:click="deleteMagister">Delete Magister</x-button.cta>
            <x-button.primary class="" wire:click="newImport">Import</x-button.primary>
        </div>
    </div>
    <div class="content-section mt-10 flex-1 p-8">

        <div class="flex space-x-4 mt-4">
            <x-table>
                <x-slot name="head">
                    <x-table.heading>
                        Datum
                    </x-table.heading>
                    <x-table.heading>
                        School
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
                    <x-table.heading width="120px">
                        &nbsp;
                    </x-table.heading>
                    <x-table.heading width="120px">
                        &nbsp;
                    </x-table.heading>
                    <x-table.heading width="120px">
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
                                {{ $set->school_name }}
                            </x-table.cell>
                            <x-table.cell>
                                {{ $set->brin_code }} {{ $set->dependance_code  }}
                            </x-table.cell>
                            <x-table.cell>
                                {{ $set->client_name }}
                            </x-table.cell>
                            <x-table.cell>
                                {{ $set->client_code }}
                            </x-table.cell>
                            <x-table.cell>
                                <x-button.text-button wire:click="activateResult({{ $set->getKey() }})">Bekijk details
                                </x-button.text-button>
                            </x-table.cell>
                            <x-table.cell>
                                <x-button.text-button wire:click="processResult({{ $set->getKey() }})">Verwerken
                                </x-button.text-button>
                            </x-table.cell>
                            <x-table.cell>
                                @if ($set->error_messages)
                                    <x-button.text-button wire:click="triggerErrorModal( {{ $set->getKey() }} )">Error
                                    </x-button.text-button>
                                @endif
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
            <div class="sm:block">
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
                                            @if( is_array($value))
                                                <x-table.cell>{!! collect($value).join([',']) !!}</x-table.cell>
                                            @else
                                                <x-table.cell>{!! $value !!}</x-table.cell>
                                            @endif
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

    <x-modal wire:model="showProcessResultModal" maxWidth="7xl">
        <x-slot name="title">ProcesResult</x-slot>
        <x-slot name="body">
            <div class="sm:block">
                <div class="border-b border-gray-200" id="melding">
                    @foreach($this->processingResultErrors as $error)
                        <div class="error-section md:mb-20">
                            <div class="notification error mt-4">
                                <span class="title">  {{ $error }}</span>
                            </div>
                        </div>
                    @endforeach
                    <div wire:loading>
                        <div wire:key="loading-text">Processing Result...</div>
                        <div wire:key="hourglass" class="lds-hourglass"></div>
                    </div>
                    <div wire:key="processing-result">
                        {{ $this->processingResult }}
                        @if ($this->displayGoToErrorsButton)
                            <BR>
                            <x-button.text-button wire:click="triggerErrorModal()">Toon errors
                            </x-button.text-button>
                        @endif

                    </div>
                </div>

                <x-button.primary wire:click="startProcessingResult">Start</x-button.primary>
            </div>
        </x-slot>
        <x-slot name="actionButton">&nbsp;</x-slot>
    </x-modal>

    <x-modal wire:model="showErrorModal" maxWidth="7xl">
        <x-slot name="title">Errors</x-slot>
        <x-slot name="body">
            <div class="sm:block">
                <div class="border-b border-gray-200" id="melding">

                    <PRE style="white-space: pre-wrap;"> {!! $this->errorMessages !!}</PRE>
                </div>


            </div>
        </x-slot>
        <x-slot name="actionButton"></x-slot>
    </x-modal>

    <x-modal wire:model="showSuccessDialog" maxWidth="7xl">
        <x-slot name="title">Success</x-slot>
        <x-slot name="body">
            <div class="sm:block">
                <div class="border-b border-gray-200" id="melding">

                    <PRE> {{ $this->successDialogMessage }}</PRE>
                </div>


            </div>
        </x-slot>
        <x-slot name="actionButton"></x-slot>
    </x-modal>


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
            border: 12px solid #0000ff;
            border-color: #0000ff transparent #0000ff transparent;
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



