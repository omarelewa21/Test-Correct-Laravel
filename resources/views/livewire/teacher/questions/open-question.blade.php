@extends($preview ?? 'livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.rich-textarea
            wire:model.debounce.1000ms="question.question"
            editorId="{{ $questionEditorId }}"
            type="cms"
            :disabled="isset($preview)"
            lang="{{ $lang }}"
            :allowWsc="$allowWsc"
    />
@endsection
@section('question-cms-answer')
    <div x-data="writeDownCms(@js($answerEditorId),@js((bool)$this->question['restrict_word_amount']), @entangle('question.max_words'))"
        x-on:selected-word-count.window="addSelectedWordCounter($event.detail, '@lang('question.selected_words')')"
    >
        <div class="border-b border-bluegrey note text-center text-sm uppercase">@lang('cms.Antwoord opties voor student')</div>
        <div class="open-question-settings | general-settings-grid mb-6">
            @if(settings()->canUseCmsWscWriteDownToggle())
                <div class="spell_check_available">
                    <x-input.toggle-row-with-title wire:model="question.spell_check_available"
                                                   :disabled="isset($preview)"
                    >
                        <x-icon.spellcheck class="min-w-[1rem]" />
                        <span class="regular">@lang('cms.spell_check_available')</span>
                        <x-slot:toolTip>@lang('cms.spell_check_available_tooltip')</x-slot:toolTip>
                    </x-input.toggle-row-with-title>
                </div>
            @endif
            <div class="mathml_functions">
                <x-input.toggle-row-with-title wire:model="question.mathml_functions"
                                               :disabled="isset($preview)"
                >
                    <x-icon.math-equation class="min-w-[1rem]" />
                    <span class="regular">@lang('cms.mathml_functions')</span>
                    <x-slot:toolTip>@lang('cms.mathml_functions_tooltip')</x-slot:toolTip>
                </x-input.toggle-row-with-title>
            </div>
            <div>
                <x-input.toggle-row-with-title wire:model="question.restrict_word_amount"
                                               x-on:change="wordCounter = !wordCounter"
                                               :disabled="isset($preview)"
                                               container-class="restrict_word_amount"
                >
                    <x-icon.text-align-left class="min-w-[1rem]" />
                    <span class="regular">@lang('cms.restrict_word_amount')</span>
                    <span class=" max_words">
                        <x-input.text type="number"
                                      value="10"
                                      class="w-20 ml-auto text-center"
                                      wire:model="question.max_words"
                                      :disabled="isset($preview)"
                        />
                    </span>
                </x-input.toggle-row-with-title>
            </div>
            <div class="text_formatting">
                <x-input.toggle-row-with-title wire:model="question.text_formatting"
                                               :disabled="isset($preview)"
                >
                    <x-icon.font class="min-w-[1rem]" />
                    <span class="regular">@lang('cms.text_formatting')</span>
                    <x-slot:toolTip>@lang('cms.text_formatting_tooltip')</x-slot:toolTip>
                </x-input.toggle-row-with-title>
            </div>
        </div>
        <x-input.rich-textarea
                wire:model.debounce.1000ms="question.answer"
                editorId="{{ $answerEditorId }}"
                type="cms"
                :disabled="isset($preview)"
                lang="{{ $lang }}"
                selid="answer-textarea"
                :allowWsc="$allowWsc"
                :maxWords="$this->question['max_words']"
                :maxWordOverride="true"
        />
        <div class="flex">
            <div id="word-count-{{ $answerEditorId }}"
                wire:ignore
                class="word-count note text-sm mt-2 mr-2"
                x-show="wordCounter"
            ></div>
            <div id="selected-word-count-{{ $answerEditorId }}"
                wire:ignore
                class="word-count note text-sm mt-2"
            ></div>
        </div>
        
    </div>
@endsection