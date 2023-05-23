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
    <div x-data="writeDownCms(@js($answerEditorId),@js((bool)$this->question['restrict_word_amount']), @entangle('question.max_words'))">
        <div class="border-b border-bluegrey note text-center text-sm uppercase">@lang('cms.Antwoord opties voor student')</div>
        <div class="general-settings-grid mb-6">
            <div class="">
                <x-input.toggle-row-with-title wire:model="question.spell_check_available">
                    <x-icon.spellcheck class="min-w-fit" />
                    <span class="regular">@lang('cms.spell_check_available')</span>
                    <x-slot:toolTip>@lang('cms.spell_check_available_tooltip')</x-slot:toolTip>
                </x-input.toggle-row-with-title>
            </div>
            <div class="">
                <x-input.toggle-row-with-title wire:model="question.mathml_functions">
                    <x-icon.math-equation class="min-w-fit" />
                    <span class="regular">@lang('cms.mathml_functions')</span>
                    <x-slot:toolTip>@lang('cms.mathml_functions_tooltip')</x-slot:toolTip>
                </x-input.toggle-row-with-title>
            </div>
            <div class="">
                <x-input.toggle-row-with-title wire:model="question.restrict_word_amount"
                                               x-on:change="wordCounter = !wordCounter"
                >
                    <x-icon.text-align-left class="min-w-fit" />
                    <span class="regular">@lang('cms.restrict_word_amount')</span>
                    <x-input.text type="number"
                                  value="10"
                                  class="w-20 ml-auto text-center"
                                  wire:model="question.max_words"
                    />
                </x-input.toggle-row-with-title>
            </div>
            <div class="">
                <x-input.toggle-row-with-title x-model="maxWords">
                    <x-icon.font class="min-w-fit" />
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
        <div id="word-count-{{ $answerEditorId }}"
             wire:ignore
             class="word-count note text-sm mt-2"
             x-show="wordCounter"
        ></div>
    </div>
@endsection