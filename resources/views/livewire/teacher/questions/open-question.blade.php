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
    <div class="border-b border-bluegrey note text-center text-sm uppercase">@lang('cms.Antwoord opties voor student')</div>
    <div class="general-settings-grid mb-6">
        <div class="">
            <x-input.toggle-row-with-title>
                <x-icon.spellcheck class="min-w-fit" />
                <span>@lang('cms.spell_check_available')</span>
                <x-slot:toolTip>@lang('cms.spell_check_available_tooltip')</x-slot:toolTip>
            </x-input.toggle-row-with-title>
        </div>
        <div class="">
            <x-input.toggle-row-with-title>
                <x-icon.questionmark class="min-w-fit" />
                <span>@lang('cms.mathml_functions')</span>
                <x-slot:toolTip>@lang('cms.mathml_functions_tooltip')</x-slot:toolTip>
            </x-input.toggle-row-with-title>
        </div>
        <div class="">
            <x-input.toggle-row-with-title>
                <x-icon.text-align-left class="min-w-fit" />
                <span>@lang('cms.restrict_word_amount')</span>
                <x-input.text type="number" value="10" class="w-20 ml-auto text-center" />
            </x-input.toggle-row-with-title>
        </div>
        <div class="">
            <x-input.toggle-row-with-title>
                <x-icon.questionmark class="min-w-fit" />
                <span>@lang('cms.text_formatting')</span>
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
    />
@endsection