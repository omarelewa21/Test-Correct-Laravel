<div class="compile-list-modal | flex flex-col bg-white rounded-10 shadow-xl transform transition-all sm:w-full h-full">
    {{--HEADER--}}
    <div class="modal-header | flex flex-col gap-2.5 pt-6 px-5 sm:px-10 z-10"
         style="box-shadow: 0 3px 18px 0 rgba(77, 87, 143, 0.2);"
    >
        <div class="flex justify-between items-center">
            <h2>@lang('cms.Wijzigingen woordenlijsten')</h2>
            <x-button.close wire:click="$emit('closeModal')" class="relative -right-3" />
        </div>
        <div class="divider"></div>
    </div>

    {{--CONTENT--}}
    <div class="flex flex-col overflow-auto flex-1 z-1 relative">
        <x-word-lists.container :word-lists="$this->wordLists">
            <x-slot:item>
                <x-word-lists.item :column-heads="$this->columnHeads">
                    <x-word-lists.item.heading :disabled="true" />

                    <x-word-lists.item.body>
                            <x-word-lists.item.grid>
                                <x-slot:head>
                                    <x-word-lists.item.grid.head :disabled="true"
                                                                 :column-heads="$this->columnHeads"
                                    />
                                </x-slot:head>
                                <x-slot:row>
                                    <x-word-lists.item.grid.row :disabled="true">
                                        <x-slot:cell>
                                            <x-word-lists.item.grid.cell x-bind:class="word.color">
                                                    <span x-text="word.text"
                                                          x-bind:data-row-value="rowIndex"
                                                          x-bind:data-column-value="wordIndex"
                                                          wire:ignore
                                                    ></span>
                                            </x-word-lists.item.grid.cell>
                                        </x-slot:cell>
                                    </x-word-lists.item.grid.row>
                                </x-slot:row>
                            </x-word-lists.item.grid>
                    </x-word-lists.item.body>
                </x-word-lists.item>
            </x-slot:item>


            <template x-teleport=".footer-numbers">
                <div class="flex items-center gap-4 bold">
                    <div class="flex items-center">
                        <span x-text="globalSelectedRelationCount"></span>
                        <span>/</span>
                        <span x-text="globalRelationCount"></span>
                        <span class="ml-1 font-normal">@lang('cms.woorden')</span>
                    </div>
                    <div class="flex gap-1">
                        <span x-text="wordLists.length"></span>
                        <span class="font-normal"
                              data-single="@lang('cms.woordenlijst')"
                              data-plural="@lang('cms.woordenlijsten')"
                              x-text="wordLists.length === 1 ? $el.dataset.single : $el.dataset.plural"
                        ></span>
                    </div>
                </div>
            </template>

            <template x-teleport=".footer-buttons">
                <div class="flex gap-4 items-center">
                    <x-button.text x-on:click="$wire.call('declineChanges')"
                                   x-bind:disabled="compiling">
                        <span>@lang('general.cancel')</span>
                    </x-button.text>

                    <x-button.cta x-on:click="$wire.call('acceptChanges')"
                                  x-bind:disabled="compiling || Object.keys(wordLists).length === 0"
                                  size="md">
                        <x-icon.checkmark/>
                        <span>@lang('cms.Wijzigingen overnemen')</span>
                    </x-button.cta>
                </div>
            </template>
        </x-word-lists.container>
    </div>

    {{--FOOTER--}}
    <div class="modal-footer | flex flex-col gap-2.5 py-2.5 px-5 sm:px-10 z-10"
         style="box-shadow: 0 -3px 18px 0 rgba(77, 87, 143, 0.2);"
         wire:ignore
    >
        <div class="flex justify-between items-center">
            <div class="footer-numbers"></div>

            <div class="footer-buttons"></div>
        </div>
    </div>
</div>