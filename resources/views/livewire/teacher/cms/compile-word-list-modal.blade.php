<div class="compile-list-modal | flex flex-col bg-white rounded-10 shadow-xl transform transition-all sm:w-full h-full">
    {{--HEADER--}}
    <div class="modal-header | flex flex-col gap-2.5 pt-6 px-5 sm:px-10 z-10"
         style="box-shadow: 0 3px 18px 0 rgba(77, 87, 143, 0.2);"
    >
        <div class="flex justify-between items-center">
            <h2>@lang('cms.Woorden opstellen')</h2>
            <x-button.close wire:click="$emit('closeModal')" class="relative -right-3" />
        </div>
        <div class="divider"></div>
    </div>

    {{--CONTENT--}}
    <div class="flex overflow-auto flex-1 z-1">
        <div class="word-list-container | flex flex-col flex-1 px-10 py-4 gap-4"
             x-data="compileWordListContainer(@js($this->wordLists))"
             wire:ignore
        >
            <template x-for="(wordList, wordListIndex) in wordLists">
                <div class="word-list | flex flex-col"
                     x-data="compileList(wordList, @js($this->columnHeads))"
                >
                    <div class=" | flex flex-col ">
                        <div class="flex w-full items-center gap-6">
                            <div class="flex flex-1 gap-2 items-center">
                                <h7 class="flex capitalize">@lang('cms.woordenlijst')</h7>
                                <x-input.text x-model="list.name" class="flex flex-1" />
                            </div>
                            <div class="flex gap-2 items-center"
                                 x-bind:class="{'rotate-svg-90': expanded}"
                            >
                                <span class="note text-sm"><span x-text="wordCount"></span> @lang('cms.woorden')</span>
                                <div class="group cursor-pointer">
                                    <x-button.collapse-chevron x-on:click="expanded = !expanded" />
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col flex-1">
                            <div x-show="expanded" x-collapse class="flex flex-col gap-4">
                                <div class="flex relative">
                                    <div class="relation-question-grid-container |">
                                        <div x-bind:id="'relation-question-grid-' + wordListIndex"
                                             class="relation-question-grid | "
                                             x-bind:class="{'!bg-allred': errorstate}"
                                             wire:ignore
                                        >
                                            <div class="grid-head-container row-head contents">
                                                <div class="grid-head head-checkmark"
                                                     x-on:change="toggleAll($event.target)"
                                                >
                                                    <x-input.checkbox />
                                                </div>
                                                <template x-for="(type, headerIndex) in cols">
                                                    <div class="grid-head"
                                                         x-bind:data-header-column="headerIndex"
                                                         x-on:change="columnValueUpdated(headerIndex, $event.target.dataset.value)"
                                                    >
                                                        <x-input.select placeholder="Kies..."
                                                                        class="!min-w-[130px]"
                                                                        :empty-option="true"
                                                        >
                                                            @foreach($this->columnHeads as $value => $label)
                                                                <x-input.option :value="$value" :label="$label" />
                                                            @endforeach
                                                        </x-input.select>
                                                    </div>
                                                </template>
                                            </div>

                                            <template x-for="(row, rowIndex) in rows">
                                                <div class="word-row contents relative"
                                                     x-bind:class="'row-'+rowIndex"
                                                >
                                                    <span class="row-checkmark"
                                                          x-on:change="toggleRow($event.target, rowIndex)"
                                                    >
                                                        <x-input.checkbox />
                                                    </span>
                                                    <template x-for="(word, wordIndex) in row">
                                                        <div>
                                                            <span x-model="word.text"
                                                                  x-bind="gridcell"
                                                                  x-bind:data-row-value="rowIndex"
                                                                  x-bind:data-column-value="wordIndex"
                                                                  x-on:focus="placeCursor($el); $el.parentElement.classList.add('focused')"
                                                                  x-on:blur="wordsUpdated(word, rowIndex, wordIndex); $el.parentElement.classList.remove('focused')"
                                                                  x-on:keydown.up="move('up', $el)"
                                                                  x-on:keydown.right="move('right', $el)"
                                                                  x-on:keydown.down="move('down', $el)"
                                                                  x-on:keydown.left="move('left', $el)"
                                                            ></span>
                                                        </div>
                                                    </template>

                                                </div>
                                            </template>
                                        </div>
                                        <div class="relation-grid-sticky-pseudo"></div>
                                    </div>
                                </div>

                                <div class="flex justify-between text-center">
                                    <div class="flex flex-col w-min">
                                        <x-button.primary class="whitespace-nowrap">
                                            <x-icon.plus />
                                            <span>@lang('cms.Uit woordenlijstenbank toevoegen')</span>
                                        </x-button.primary>
                                        <span class="note text-sm">@lang('cms.add_from_word_list_bank_explainer')</span>
                                    </div>
                                    <div class="flex flex-col w-min">
                                        <x-button.primary class="whitespace-nowrap">
                                            <x-icon.plus />
                                            <span>@lang('cms.Uit woordenbank toevoegen')</span>
                                        </x-button.primary>
                                        <span class="note text-sm">@lang('cms.add_from_word_bank_explainer')</span>
                                    </div>
                                    <div class="flex flex-col w-min">
                                        <x-button.primary class="whitespace-nowrap">
                                            <x-icon.plus />
                                            <span>@lang('cms.Excel bestand toevoegen')</span>
                                        </x-button.primary>
                                        <span class="note text-sm">@lang('cms.excel_import_explainer')</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </template>

            <div class="flex w-full border-t border-b border-bluegrey items-center gap-2.5 py-2.5 hover:text-primary hover:bg-primary/5 active:bg-primary/10 cursor-pointer transition-colors"
                 x-on:click="addWordList();"
            >
                <x-icon.plus-in-circle />
                <span class="bold">Woordenlijst toevoegen</span>
            </div>

            <template x-teleport=".footer-numbers">
                <div class="flex items-center gap-4 bold">
                    <div class="flex items-center">
                        <span x-text="globalSelectedWordCount"></span>
                        <span>/</span>
                        <span x-text="globalWordCount"></span>
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

            <template x-teleport=".compile-button">
                <x-button.cta x-on:click="compileLists()"
                              x-bind:class="{'text-red-600': compiling}"
                              size="md">
                    <span>@lang('cms.compile')</span>
                </x-button.cta>
            </template>
        </div>
    </div>

    {{--FOOTER--}}
    <div class="modal-footer | flex flex-col gap-2.5 py-2.5 px-5 sm:px-10 z-10"
         style="box-shadow: 0 -3px 18px 0 rgba(77, 87, 143, 0.2);"
         wire:ignore
    >
        <div class="flex justify-between items-center">
            <div class="footer-numbers"></div>

            <div class="flex gap-4 items-center">
                <x-button.text wire:click="$emit('closeModal')">@lang('general.cancel')</x-button.text>
                <div class="compile-button"></div>
            </div>
        </div>
    </div>
</div>