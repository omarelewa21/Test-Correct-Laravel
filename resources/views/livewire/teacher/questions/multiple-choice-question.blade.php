@extends('livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.rich-textarea
            wire:model.debounce.1000ms="question.question"
            editorId="{{ $questionEditorId }}"
            type="cms"
            lang="{{ $lang }}"
            :allowWsc="$allowWsc"
    />
@endsection

@section('question-cms-answer')
    <div class="flex flex-row justify-between gap-6">
        <x-input.toggle-row-with-title wire:model="question.all_or_nothing"
                                        :toolTip="__('cms.all_or_nothing_tooltip_text')"
        >
            <span class="bold"> {{ __('cms.Alles of niets correct') }}</span>
        </x-input.toggle-row-with-title>

        <x-input.toggle-row-with-title wire:model="question.fix_order" class="flex-1">
            <span class="bold"> {{ __('cms.fix_order') }}</span>
        </x-input.toggle-row-with-title>
    </div>
    <div class="flex w-full mt-4">{{ __('cms.MultipleChoice Question Uitleg Text') }}</div>
    <div class="flex flex-col space-y-2 w-full mt-4"
         wire:sortable="__call('updateMCOrder')"
         x-data="{}"
         x-init="
                                    $refs.punten.style.left = ($el.querySelector('input').offsetWidth+10) +'px';
                                   "
         @resize.window.debounce.100ms="$refs.punten.style.left = ($el.querySelector('input').offsetWidth+10) +'px';"
    >
        <div class="flex px-0 py-0 border-0 bg-system-white justify-between relative">
            <div class="">{{ __('cms.Antwoord') }}</div>
            <div wire:ignore.self x-ref="punten" class="absolute">{{ __('cms.Punten') }}</div>
        </div>
        @php
            $disabledClass = "icon disabled cursor-not-allowed";
            if($this->__call('canDelete')) {
                $disabledClass = "";
            }
        @endphp
        @foreach($cmsPropertyBag['answerStruct'] as $answer)
            @php
                $answer = (object) $answer;
                $errorAnswerClass = '';
                $errorScoreClass = '';
            @endphp
            @error('question.answers.'.$loop->index.'.answer')
            @php
                $errorAnswerClass = 'border-allred';
            @endphp
            @enderror
            @error('question.score')
            @php
                $errorScoreClass = 'border-allred';
            @endphp
            @enderror
            <x-drag-item id="mc-{{$answer->id}}" sortId="{{ $answer->order }}"
                         wireKey="option-{{ $answer->id }}" selid="drag-box"
                         :useHandle="true"
                         :keepWidth="true"
                         class="flex px-0 py-0 border-0 bg-system-white regular"
                         slotClasses="w-full space-x-2.5"
                         sortIcon="reorder"
                         dragIconClasses="cursor-move"
            >
                <x-input.text class="w-full  {{ $errorAnswerClass }} "
                              wire:model.lazy="cmsPropertyBag.answerStruct.{{ $loop->index }}.answer"
                              selid="answer-field"
                />
                <div class=" text-center justify-center">
                    <x-input.text class="w-12 text-center {{ $errorScoreClass }}"
                                  wire:model="cmsPropertyBag.answerStruct.{{ $loop->index }}.score"
                                  title="{{ $answer->score }}"
                                  type="number"
                                  :onlyInteger="true"
                                  selid="score-field"
                    />
                </div>
                <x-slot name="after">
                    <x-icon.remove class="cursor-pointer {{ $disabledClass }}"
                                   id="remove_{{ $answer->order }}"
                                   wire:click="__call('delete', '{{$answer->id}}')"/>
                </x-slot>
            </x-drag-item>
        @endforeach
    </div>
    <div class="flex flex-col space-y-2 w-full">
        <x-button.primary class="mt-3 justify-center" wire:click="__call('addAnswerItem')" selid="add-answer-option-btn">
            <x-icon.plus/>
            <span >
                                    {{ __('cms.Item toevoegen') }}
                                    </span>
        </x-button.primary>
    </div>
@endsection
