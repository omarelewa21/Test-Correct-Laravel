<div class="compile-list-modal | flex flex-col bg-white rounded-10 shadow-xl transform transition-all sm:w-full h-full">
    {{--HEADER--}}
    <div class="modal-header | flex flex-col gap-2.5 pt-6 px-5 sm:px-10 z-10"
         style="box-shadow: 0 3px 18px 0 rgba(77, 87, 143, 0.2);"
    >
        <div class="flex justify-between items-center">
            <h2>@lang('cms.Woorden opstellen')</h2>
            <x-button.close wire:click="close" class="relative -right-3" />
        </div>
        <div class="divider"></div>
    </div>

    {{--CONTENT--}}
    <div class="flex flex-col overflow-auto flex-1 z-1 relative">
        <x-word-lists.container :word-lists="$this->wordLists"
                                x-on:add-new="addNewWordList();"
                                x-on:open-add-existing-panel="openAddExistingWordListPanel()"
                                x-on:upload="uploadWordList();"
                                x-on:add-list="addExistingWordList($event.detail.uuid)"
                                x-on:handle-upload="addUploadToNew($event.detail.file)"
        >
            <x-slot:item>
                <x-word-lists.item :column-heads="$this->columnHeads"
                                   x-on:add-list="addExistingWordListToList($event.detail.uuid)"
                                   x-on:add-word="addExistingWordToList($event.detail.uuid)"
                                   x-on:handle-upload="addUploadToList($event.detail.file)"
                >
                    <x-word-lists.item.heading />
                    <x-word-lists.item.body>
                        <x-word-lists.item.grid>
                            <x-slot:head>
                                <x-word-lists.item.grid.head :column-heads="$this->columnHeads"
                                                             :empty-select-placeholder="__('general.Kies') . '...'"
                                />
                            </x-slot:head>
                            <x-slot:row>
                                <x-word-lists.item.grid.row>
                                    <x-slot:cell>
                                        <x-word-lists.item.grid.cell
                                                x-on:click.stop="$el.firstElementChild.focus()"
                                        >
                                            <span x-model="word.text"
                                                  x-bind="gridcell"
                                                  x-bind:data-row-value="rowIndex"
                                                  x-bind:data-column-value="wordIndex"
                                                  x-on:focus="placeCursor($el); $el.parentElement.classList.add('focused')"
                                                  x-on:blur="wordsUpdated(word, rowIndex, wordIndex); $el.parentElement.classList.remove('focused')"
                                                  x-on:keydown.enter.prevent="move($event, $el)"
                                                  wire:ignore
                                            ></span>
                                        </x-word-lists.item.grid.cell>
                                    </x-slot:cell>
                                </x-word-lists.item.grid.row>
                            </x-slot:row>
                        </x-word-lists.item.grid>

                        <div class="flex justify-between text-center">
                            <div class="flex flex-col w-min">
                                <x-button.primary class="whitespace-nowrap" x-on:click="addFromWordListBank()">
                                    <x-icon.plus />
                                    <span>@lang('cms.Uit woordenlijstenbank toevoegen')</span>
                                </x-button.primary>
                                <span class="note text-sm">@lang('cms.add_from_word_list_bank_explainer')</span>
                            </div>
                            <div class="flex flex-col w-min">
                                <x-button.primary class="whitespace-nowrap" x-on:click="addFromWordBank()">
                                    <x-icon.plus />
                                    <span>@lang('cms.Uit woordenbank toevoegen')</span>
                                </x-button.primary>
                                <span class="note text-sm">@lang('cms.add_from_word_bank_explainer')</span>
                            </div>
                            <div class="flex flex-col w-min">
                                <x-button.primary class="whitespace-nowrap" x-on:click="addFromUpload()">
                                    <x-icon.plus />
                                    <span>@lang('cms.Excel bestand toevoegen')</span>
                                </x-button.primary>
                                <span class="note text-sm">@lang('cms.excel_import_explainer')</span>
                            </div>
                        </div>
                    </x-word-lists.item.body>
                </x-word-lists.item>
            </x-slot:item>

            <div class="flex w-full border-b border-bluegrey items-center gap-2.5 py-2.5 hover:text-primary hover:bg-primary/5 active:bg-primary/10 cursor-pointer transition-colors"
                 x-on:click="addWordList();"
            >
                <x-icon.plus-in-circle />
                <span class="bold">@lang('cms.Woordenlijst toevoegen')</span>
            </div>
            <div class="flex w-full"><span class="h-4"></span></div>

            <template x-teleport=".footer-numbers">
                <div class="flex items-center gap-4 bold">
                    <div class="flex items-center">
                        <span x-text="globalSelectedRelationCount"></span>
                        <span>/</span>
                        <span x-text="globalRelationCount"></span>
                        <span class="ml-1 font-normal">@lang('cms.relaties')</span>
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
                    <x-button.text x-on:click="$wire.call('close')"
                                   x-bind:disabled="compiling">
                        <span>@lang('general.cancel')</span>
                    </x-button.text>

                    <x-button.cta x-on:click="compileLists()"
                                  x-bind:disabled="compiling || Object.keys(wordLists).length === 0"
                                  size="md">
                        <x-icon.checkmark />
                        <span>@lang('cms.compile')</span>
                    </x-button.cta>
                </div>
            </template>
        </x-word-lists.container>

        @error('import_empty_values')
        <div class="absolute bottom-2 flex w-full justify-center">
            <x-notification-message title="Excel importeren mislukt." :$message />
        </div>
        @enderror
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

    {{-- Modals & more --}}
    <x-modal.compile-word-list-add-list-modal />

    <x-modal.compile-word-list-upload-modal />

    <div id="word-list-modal-validation-strings"
         class="hidden invisible"
         data-and="{{ __('test-take.and') }}"
         @foreach($this->validationMessages() as $error => $translation)
             data-{{ str($error)->kebab() }}="{{ $translation }}"
            @endforeach
    ></div>
</div>