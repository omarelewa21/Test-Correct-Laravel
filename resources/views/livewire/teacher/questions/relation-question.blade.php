@extends($preview ?? 'livewire.teacher.questions.cms-layout')
@section('question-cms-question')
{{--    <x-input.textarea wire:model.debounce.1000ms="question.question"></x-input.textarea>--}}
        <x-input.rich-textarea
                wire:model.debounce.1000ms="question.question"
                editorId="{{ $questionEditorId }}"
                type="cms"
                lang="{{ $lang }}"
                :allowWsc="$allowWsc"
                :disabled="isset($preview)"
        />
@endsection

@section('question-cms-answer')
    <div class="relation-answer-list | ">
        <div class="relation-question-toggles | ">
            <div class="border-b border-bluegrey flex w-full justify-between items-center h-[50px]">
                <div class="flex items-center gap-2.5">
                    <x-input.toggle class="mr-2" wire:model="question.shuffle" />
                    <x-icon.shuffle />
                    <span class="bold">@lang('cms.Carrousel verdeling per student')</span>
                </div>
                <div class="flex items-center gap-2">
                    <x-input.text class="text-center w-[3.375rem]"
                                  :only-integer="true"
                                  wire:model.lazy="question.selection_count"
                                  :disabled="!$this->question['shuffle']"
                                  :error="$this->getErrorBag()->has('selection_count')"
                    />
                    <x-tooltip>@lang('cms.relation_carousel_tooltip')</x-tooltip>
                </div>
            </div>
        </div>
        <div class="relation-question-intro | mt-6 mb-4">
            <p>Kies wat de student ziet (vraagstelling). Je kan dit per categorie (kolom) aangeven, maar ook speciferen
                per woord (cel) door daarop te klikken. Kies je bijv. synoniem, definitie, etc. als vraagstelling, dan
                zal de student het taalvak woord moeten antwoorden.</p>
        </div>
        <div class="relation-question-grid-container | ">
            <div id="relation-question-grid"
                 class="relation-question-grid | "
                 style="--relation-grid-cols: @js(count(\tcCore\Http\Enums\WordType::cases()))"
                 x-data="relationQuestionGrid"
                 x-on:relation-rows-updated.window="handleIncomingUpdatedRows($event.detail)"
                 wire:ignore
            >
                <div class="grid-head-container contents"
                     x-on:input="selectColumn($event.target.value)"
                >
                    <div class="grid-head">
                        <x-input.radio value="subject"
                                       name="relation-column"
                                       text-left="subject"
                                       label-classes="bold gap-2 hover:text-primary"
                                       x-bind:checked="selectedColumn === $el.value"
                                       x-bind:disabled="disabledColumns.includes($el.value)"
                        />
                    </div>
                    <div class="grid-head">
                        <x-input.radio value="translation"
                                       name="relation-column"
                                       text-left="translation"
                                       label-classes="bold gap-2 hover:text-primary"
                                       x-bind:checked="selectedColumn === $el.value"
                                       x-bind:disabled="disabledColumns.includes($el.value)"
                        />
                    </div>
                    <div class="grid-head">
                        <x-input.radio value="definition"
                                       name="relation-column"
                                       text-left="definition"
                                       label-classes="bold gap-2 hover:text-primary"
                                       x-bind:checked="selectedColumn === $el.value"
                                       x-bind:disabled="disabledColumns.includes($el.value)"
                        />
                    </div>
                    <div class="grid-head">
                        <x-input.radio value="synonym"
                                       name="relation-column"
                                       text-left="synonym"
                                       label-classes="bold gap-2 hover:text-primary"
                                       x-bind:checked="selectedColumn === $el.value"
                                       x-bind:disabled="disabledColumns.includes($el.value)"
                        />
                    </div>
                </div>

                <template x-for="(row, rowIndex) in rows">
                    <div class="word-row contents" wire:key="">

                        <template x-for="(word, wordType) in row">
                            <div x-bind:class="{'selected': word.selected, 'empty': !word.word_id}"
                                 x-on:click="selectWord(rowIndex, word)"
                            >
                                <span x-text="getText(word, rowIndex)"></span>
                                <x-icon.checkmark-small class="text-white min-w-[13px]" />
                            </div>
                        </template>

                    </div>
                </template>
            </div>
            <div class="relation-grid-sticky-pseudo"></div>
        </div>

        <div class="flex flex-col w-full note items-center justify-center text-center">
            <span class="text-sm mt-2">{{ $this->cmsPropertyBag['word_count'] }} @lang('cms.woorden')</span>

            <x-button.primary class="w-full mt-4 mb-2"
                              wire:click="openCompileListsModal"
            >
                <x-icon.edit />
                <span>@lang('cms.Woorden opstellen')</span>
            </x-button.primary>

            <span class="text-sm">@lang('cms.Stel de woorden op voor het vraagmodel uit de woordenbank, woordenlijstenbank of upload een bestand.')</span>
        </div>
    </div>
@endsection

{{-- Syncing met skiprender naar de backend? En dan volledig JS based zodat t niet laggy wordt?--}}
