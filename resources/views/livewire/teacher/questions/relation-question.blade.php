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

@section('question-cms-settings')
    <div class="relation-question-toggles | grid grid-cols-2 gap-4">
        <div class="flex flex-col w-full">
            <div class="border-b border-bluegrey flex w-full justify-between items-center h-[50px]">
                <div class="flex items-center gap-2.5">
                    <x-input.toggle class="mr-2" wire:model="question.shuffle" />
                    <x-icon.shuffle />
                    <span class="bold">@lang('cms.Aantal woorden als carrousel')</span>
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
            <div class="flex flex-col w-full pl-[66px] border-b border-bluegrey">
                <div class="flex w-full justify-between items-center h-[50px]">
                    <div class="flex items-center gap-2.5">
                        <x-input.toggle class="mr-2" wire:model="question.shuffle_per_participant"
                                        :disabled="!$this->question['shuffle']" />
                        <span class="bold">@lang('cms.Verschillend per student')</span>
                    </div>
                </div>
            </div>
        </div>
        <div></div>
    </div>
@endsection

@section('question-cms-answer')
    <div @class(["relation-answer-list | ", 'pointer-events-none' => isset($preview)])
         wire:key="relation-question-section-{{ $this->uniqueQuestionKey }}"
    >

        <div class="relation-question-intro | mb-4">
            <p>@lang('cms.relation-question-intro')</p>
        </div>
        <div class="relation-question-grid-container | ">
            <div id="relation-question-grid"
                 class="relation-question-grid | "
                 style="--relation-grid-cols: @js(count(\tcCore\Http\Enums\WordType::cases()))"
                 x-data="relationQuestionGrid"
                 x-on:relation-rows-updated.window="handleIncomingUpdatedRows($event.detail)"
                 wire:ignore
                 wire:key="relation-question-grid-{{ $this->uniqueQuestionKey }}"
            >
                <div class="grid-head-container contents"
                     x-on:input="selectColumn($event.target.value)"
                >
                    @foreach($this->cmsPropertyBag['column_heads'] as $case => $description)
                        <div class="grid-head"
                             wire:key="case-{{ $case }}"
                             x-bind:class="{'radio-disabled': disabledColumns.includes(@js($case))}"
                        >
                            <x-input.radio :value="$case"
                                           name="relation-column"
                                           :text-left="$description"
                                           label-classes="bold gap-2 hover:text-primary"
                                           x-bind:checked="selectedColumn === $el.value"
                                           x-bind:disabled="disabledColumns.includes($el.value)"
                                           :disabled="isset($preview)"
                            />
                        </div>
                    @endforeach
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
                              :disabled="isset($preview)"
            >
                <x-icon.edit />
                <span>@lang('cms.Woorden opstellen')</span>
            </x-button.primary>

            <span class="text-sm">@lang('cms.Stel de woorden op voor het vraagmodel uit de woordenbank, woordenlijstenbank of upload een bestand.')</span>
        </div>
    </div>
@endsection

{{-- Syncing met skiprender naar de backend? En dan volledig JS based zodat t niet laggy wordt?--}}
