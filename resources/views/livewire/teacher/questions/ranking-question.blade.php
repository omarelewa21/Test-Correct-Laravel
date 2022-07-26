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

    <div class="flex w-full mt-4">
        {{ __('cms.Ranking Question Uitleg Text') }}
    </div>
    <div class="flex flex-col space-y-2 w-full mt-4"
         wire:sortable="__call('updateRankingOrder')">
        <div class="flex px-0 py-0 border-0 bg-system-white">
            <div class="w-full mr-2">{{ __('cms.Stel je te rangschikken items op') }}</div>
            <div class="w-20"></div>
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
            @endphp
            @error('question.answers.'.$loop->index.'.answer')
            @php
                $errorAnswerClass = 'border-allred'
            @endphp
            @enderror
            <x-drag-item id="mc-{{$answer->id}}"
                         class="flex px-0 py-0 border-0 bg-system-white relative"
                         sortId="{{ $answer->order }}"
                         wireKey="option-{{ $answer->id }}"
                         selid="drag-box"
                         slotClasses="w-full mr-0 "
                         dragClasses="absolute right-14 hover:text-primary transition"
                         dragIconClasses=" cursor-move"
                         :useHandle="true"
                         :keepWidth="true"
                         sortIcon="reorder"
            >
                <x-input.text class="w-full mr-1 {{ $errorAnswerClass }} "
                              wire:model.lazy="cmsPropertyBag.answerStruct.{{ $loop->index }}.answer"
                              selid="answer-field"/>
                <x-slot name="after">
                    <x-icon.remove class="mx-2 w-4 cursor-pointer  {{ $disabledClass }}"
                                   id="remove_{{ $answer->order }}"
                                   wire:click="__call('delete','{{$answer->id}}')"/>
                </x-slot>
            </x-drag-item>
        @endforeach
    </div>
    <div class="flex flex-col space-y-2 w-full">
        <x-button.primary class="mt-3 justify-center" wire:click="__call('addAnswerItem')"
                          selid="add-answer-option-btn">
            <x-icon.plus/>
            <span>{{ __('cms.Item toevoegen') }}</span>
        </x-button.primary>
    </div>
@endsection
