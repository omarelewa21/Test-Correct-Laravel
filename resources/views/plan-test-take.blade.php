<script>
    function menuItemStates() {
        return {
            toetsing: false,
            analyses: false,
            berichten: false,
            kennisbank: false,
        }
    }
</script>
<x-layouts.base>

    <header class="header fixed w-full content-center"
            x-data="menuItemStates();"
            x-on:click.away="menuItemStates()"
    >
        <div class="mx-4 lg:mx-28 flex h-full items-center">
            <div>
                <a class="" href="{{config('app.url_login')}}">
                    <img class="h-8 lg:h-12" src="/svg/logos/Logo-Test-Correct-2.svg"
                         alt="Test-Correct">
                </a>
            </div>

            <div id="menu" class="menu hidden flex-wrap content-center md:flex md:ml-4">
                <div class="menu-item px-2 py-1">
                    <button @click="" class="text-button">Dashboard</button>
                </div>

                <x-menu.item label="Toetsing" name="toetsing" />
                <x-menu.item label="Analyses" name="analyses" />
                <x-menu.item label="Berichten" name="berichten" />
                <x-menu.item label="Kennisbank" name="kennisbank" />

            </div>

            <div class="user flex flex-wrap items-center ml-auto space-x-6">
                <x-dropdown label="{{ Auth::user()->getNameFullAttribute() }}">
                    <x-dropdown.item>
                        Uitloggen
                    </x-dropdown.item>
                </x-dropdown>
            </div>
        </div>
        <div>
            <div class="z-0 relative">
                <x-menu.dropdown name="toetsing">
                    <x-button.text-button>Geplande toetsen</x-button.text-button>
                    <x-button.text-button>Te bespreken</x-button.text-button>
                    <x-button.text-button>Inzien</x-button.text-button>
                    <x-button.text-button>Becijferd</x-button.text-button>
                </x-menu.dropdown>

                <x-menu.dropdown name="analyses">
                    <x-button.text-button>Jouw analyses</x-button.text-button>
                </x-menu.dropdown>

                <x-menu.dropdown name="berichten">
                    <x-button.text-button>Berichten</x-button.text-button>
                </x-menu.dropdown>

                <x-menu.dropdown name="kennisbank">
                    <x-button.text-button>Bezoek de kennisbank</x-button.text-button>
                </x-menu.dropdown>
            </div>
        </div>
    </header>

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
                        @for ($i = 0; $i < 4; $i++)
                            <x-table.row x-data="{i: {{$i}}}" id="row-{{ $i }}"
                                         @mouseenter="if (i !== 0) changeRowBorder(i,'transparent')"
                                         @mouseleave="if (i !== 0) changeRowBorder(i,'var(--blue-grey)')">
                                <x-table.cell>Super mooie table mane</x-table.cell>
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
                        @endfor
                    </x-slot>
                </x-table>
            </div>
            <div class="flex items-center justify-center space-x-4">
                <div class="flex paginator space-x-2">
                    <div class="question-number rounded-full text-center cursor-pointer bg-primary text-white active">
                        <span class="align-middle">1</span>
                    </div>
                    <div class="question-number rounded-full text-center cursor-pointer bg-primary text-white">
                        <span class="align-middle">2</span>
                    </div>
                    <div class="question-number rounded-full text-center cursor-pointer bg-primary text-white">
                        <span class="align-middle">3</span>
                    </div>
                    <div class="question-number rounded-full text-center cursor-pointer bg-primary text-white">
                        <span class="align-middle">4</span>
                    </div>
                </div>
                <div>
                    <x-button.text-button>
                        <span>Volgende</span>
                        <x-icon.chevron/>
                    </x-button.text-button>
                </div>
            </div>
        </div>
    </main>
    <script>
        function changeRowBorder(i, style) {
            document.getElementById('row-' + (i - 1)).style.borderBottomColor = style
        }
    </script>

</x-layouts.base>