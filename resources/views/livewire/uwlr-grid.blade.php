<?php
$struct = [
    'school'              => [
        'dependancecode', 'brincode', 'schooljaar', 'xsdversie',
    ],
    'groep'               => [
        'key', 'naam', 'mutatiedatum',
    ],
    'samengestelde_groep' => [
        'key', 'naam',
    ],
    'leerling'            => [
        'eckid', 'key', 'achternaam', 'roepnaam', 'geboortedatum', 'groep',
        'samengestelde_groepen',
    ],
    'leerkracht'          => [
        'key', 'roepnaam', 'emailadres', 'groepen', 'achternaam', 'samengestelde_groepen', 'eckid'
    ],
];
?>

<div class="mt-10 flex-1 p-8 mx-8 xl:mx-28" id="uwlr-grid">
    <div class="flex flex-1 justify-between">
        <div><h1>UWLR Grid</h1></div>
        <div class="flex-shrink-0">
            @if(\Illuminate\Support\Str::contains(url()->current(),'testwelcome'))
                <x-button.cta class="" wire:click="deleteImportData"><span>Delete Import data</span></x-button.cta>
            @endif
            <x-button.primary class="" wire:click="newImport"><span>Import</span></x-button.primary>
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
                    <x-table.heading>
                       Progress
                    </x-table.heading>
                    <x-table.heading width="120px">
                        &nbsp;
                    </x-table.heading>
                    <x-table.heading width="120px">
                        &nbsp;
                    </x-table.heading>
                    <x-table.heading width="60px">
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

                                    {{ $set->import_progress }}

                            </x-table.cell>
                            <x-table.cell>
                                @if($set->status !== 'PROCESSING' && !\Illuminate\Support\Str::contains($set->status,'MOVEDTOMASTER'))
                                    <x-button.text wire:click="activateResult({{ $set->getKey() }})"><span>Bekijk
                                            details</span>
                                    </x-button.text>
                                @elseif($set->status === 'PROCESSING' || $set->status === 'READYTOPROCESS')
                                    <div class="lds-hourglass" wire:poll></div>
                                @endif

                            </x-table.cell>
                            <x-table.cell>
                                @if($set->status !== 'PROCESSING' && $set->status !== 'READYTOPROCESS' && !\Illuminate\Support\Str::contains($set->status,'MOVEDTOMASTER'))
                                    <x-button.text wire:click="processResult({{ $set->getKey() }})">
                                        <span>Verwerken</span>
                                    </x-button.text>
                                @endif
                            </x-table.cell>
                            <x-table.cell>
                                @if($set->status !== 'PROCESSING')
                                    @if(\Illuminate\Support\Str::contains(url()->current(),'testwelcome'))
                                        <x-button.text class=""
                                                              @click="if(confirm('Weet je zeker dat je hier alles van wilt verijderen?\nLet op: Dit kan even duren het scherm ververst zichzelf!')){ livewire.find(document.querySelector('#uwlr-grid').getAttribute('wire:id')).call('deleteImportDataForResultSet','{{ $set->getKey() }}')}">
                                            <div wire:loading wire:target="deleteImportDataForResultSet">
                                                <div class="lds-hourglass"></div>
                                            </div>
                                            <div wire:loading.remove wire:target="deleteImportDataForResultSet">
                                                <span class="error"><x-icon.trash></x-icon.trash></span>
                                            </div>
                                        </x-button.text>
                                    @endif
                                @endif
                            </x-table.cell>
                            <x-table.cell>
                                @if($set->status !== 'PROCESSING' && $set->status !== 'READYTOPROCESS')
                                    @if ($set->error_messages)
                                        <x-button.text wire:click="triggerErrorModal( {{ $set->getKey() }} )">
                                            <span>Warnings</span>
                                        </x-button.text>
                                    @endif
                                @endif
                            </x-table.cell>
                            <x-table.cell>
                                @if($set->status !== 'PROCESSING' && $set->status !== 'READYTOPROCESS')
                                    @if ($set->failure_messages)
                                        <x-button.text
                                            wire:click="triggerFailureModal( {{ $set->getKey() }} )">
                                            <span>Error</span>
                                        </x-button.text>
                                    @endif
                                @endif
                            </x-table.cell>
                        </x-table.row>
                    @endforeach
                </x-slot>
            </x-table>

        </div>


        <x-slot name="footerbuttons">&nbsp;</x-slot>
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
                    <div class="mt-8 mb-8" x-data="clipboard()">
                        <button wire:key="copy-{{ $this->modalActiveTab }}"
                                x-on:click="copytable($event, 'table-{{ $this->modalActiveTab }}')">Copy to clipboard
                        </button>
                        <x-table id="table-{{ $this->modalActiveTab }}">
                            <x-slot name="head">
                                @foreach($this->modalActiveTabHtml as $object)
                                    @if($loop->first)
                                        @foreach($struct[$this->modalActiveTab] as $prop)
                                            {{--                                        @foreach($object as $prop => $value)--}}
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
                                        @foreach($struct[$this->modalActiveTab] as $prop)
                                            @if (!array_key_exists($prop, $object))
                                                <x-table.cell>&nbsp;</x-table.cell>
                                            @elseif( is_array($object[$prop]))
                                                <x-table.cell>{!! collect($object[$prop]).join([',']) !!}</x-table.cell>
                                            @else
                                                <x-table.cell>{!! $object[$prop] !!}</x-table.cell>
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
                            <x-button.text wire:click="triggerErrorModal()">
                                <span>Toon errors</span>
                            </x-button.text>
                        @endif

                    </div>
                </div>

                <x-button.primary wire:click="startProcessingResult"><span>Start</span></x-button.primary>
            </div>
        </x-slot>
        <x-slot name="actionButton">&nbsp;</x-slot>
    </x-modal>

    <x-modal wire:model="showErrorModal" maxWidth="7xl">
        <x-slot name="title">Warnings</x-slot>
        <x-slot name="body">
            <div class="sm:block">
                <div class="border-b border-gray-200" id="melding">

                    <PRE style="white-space: pre-wrap;"> {!! $this->errorMessages !!}</PRE>
                </div>


            </div>
        </x-slot>
        <x-slot name="actionButton"></x-slot>
    </x-modal>

    <x-modal wire:model="showFailureModal" maxWidth="7xl">
        <x-slot name="title">Failure errors</x-slot>
        <x-slot name="body">
            <div class="sm:block">
                <div class="border-b border-gray-200" id="melding">

                    <PRE style="white-space: pre-wrap;"> {!! $this->failureMessages !!}</PRE>
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
    <script>
        function clipboard() {
            return {
                copytable(event, selectorId) {
                    event.target.innerHTML = 'Copied to clipboard'
                    event.target.classList.add('cta-button')
                    var body = document.body, range, sel;
                    el = document.getElementById(selectorId)
                    if (document.createRange && window.getSelection) {
                        range = document.createRange();
                        sel = window.getSelection();
                        sel.removeAllRanges();
                        try {
                            range.selectNodeContents(el);
                            sel.addRange(range);
                        } catch (e) {
                            range.selectNode(el);
                            sel.addRange(range);
                        }
                    } else if (body.createTextRange) {
                        range = body.createTextRange();
                        range.moveToElementText(el);
                        range.select();
                    }
                    document.execCommand('copy')
                    setTimeout(() => {
                        event.target.innerHTML = 'Copy to clipboard'
                        event.target.classList.remove('cta-button')
                    }, 3000);
                }
            }
        }
    </script>

</div>



