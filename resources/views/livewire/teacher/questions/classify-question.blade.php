@extends('livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.rich-textarea
            wire:model.debounce.1000ms="question.question"
            editorId="{{ $questionEditorId }}"
            type="cms"
    />
@endsection

@section('question-cms-answer')

    <div class="flex w-full mt-4">
        {{ __('cms.Classify Question Uitleg Text') }}
    </div>
    <div class="grid mt-2 grid-cols-2 gap-y-4 gap-x-3.5">
        @foreach($cmsPropertyBag['answerStruct'] as $key => $subStruct)

                @php
                $subStruct = (object) $subStruct;
                $disabledMainClass = "icon disabled cursor-not-allowed";
                if($this->__call('canDelete',$key)) {
                    $disabledMainClass = "";
                }
                $errorMainClass = '';
                $refIndex = $loop->index;
                @endphp
            @error('question.answers.'.$loop->index.'.left')
            @php
                $errorMainClass = 'border-allred';
            @endphp
            @enderror
                <div >
                    <div class="flex items-center space-x-2.5">
                        <x-input.text class="w-full mr-1 text-center relative z-10 {{ $errorMainClass }}" wire:key="left-{{$key}}" wire:model.lazy="cmsPropertyBag.answerStruct.{{$key}}.left" selid="left-answer"/>
                        <x-icon.remove class="mx-2 w-4 cursor-pointer  {{ $disabledMainClass }}" wire:key="remove-{{$key}}" id="remove_{{ $key }}" wire:click="__call('delete','{{ $key }}')"></x-icon.remove>
                    </div>
                    <div class="w-full mt-4"
                         wire:sortable="__call('updateRankingOrder')">
                        @php
                            $disabledClass = "icon disabled cursor-not-allowed";
                            if($this->__call('canDeleteSubItem',$key)) {
                                $disabledClass = "";
                            }
                        @endphp
                        @foreach($subStruct->rights as $answer)
                            @php
                                $answer = (object) $answer;
                                $errorAnswerClass = '';
                            @endphp
                            @error('question.answers.'.$refIndex.'.right')
                            @php
                                $errorAnswerClass = 'border-allred';
                            @endphp
                            @enderror
                            <x-drag-item id="mc-{{ $key }}-{{$answer->id}}" sortId="{{ $key }}={{ $answer->id }}"
                                         wireKey="option-{{ $key }}-{{ $answer->id }}" selid="drag-box"
                                         class="flex ml-3 pr-2.5  mb-2 px-0 py-0 border-0 bg-system-white relative relative sub-item-with-connecting-line regular"
                                         slotClasses="w-full mr-0 "
                                         dragClasses="absolute right-14 hover:text-primary transition"
                                         dragIconClasses=" cursor-move"
                                         :useHandle="true"
                                         :keepWidth="true"
                                         sortIcon="reorder"
                            >
                                <x-input.text class="w-full mr-0.5 {{ $errorAnswerClass }} " wire:key="input-{{$key}}-{{$answer->id}}" wire:model.lazy="cmsPropertyBag.answerStruct.{{$key}}.rights.{{ $loop->index }}.answer" selid="right-answer"/>
                                <x-slot name="after">
                                    <x-icon.remove class="mx-2 w-4 cursor-pointer  {{ $disabledClass }}" wire:key="remove-{{$key}}-{{$answer->id}}" id="remove_{{ $answer->order }}" wire:click="__call('deleteSubItem','{{ $key }}={{$answer->id}}')"></x-icon.remove>
                                </x-slot>
                            </x-drag-item>
                        @endforeach
                    </div>
                    <x-button.primary class="mt-1 justify-center w-full" wire:click="__call('addAnswerSubItem','{{$key}}')" selid="add-answer-sub-option-btn">
                        <x-icon.plus/>
                        <span >
                                        {{ __('cms.Item toevoegen') }}
                                        </span>
                    </x-button.primary>
                </div>

        @endforeach
    </div>
    <div class="flex flex-col space-y-2 w-full">
        <x-button.primary class="mt-3 justify-center" wire:click="__call('addAnswerItem')" selid="add-answer-option-btn">
            <x-icon.plus/>
            <span >
                                    {{ __('cms.Insleepvak toevoegen') }}
                                    </span>
        </x-button.primary>
    </div>
@endsection
