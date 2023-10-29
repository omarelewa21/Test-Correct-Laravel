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

    <div class="flex w-full mt-4">
        {{ __('cms.Matching Question Uitleg Text') }}
    </div>
    <div class="flex flex-col space-y-2 w-full mt-4" @if(!isset($preview)) wire:sortable="__call('updateRankingOrder')" @endif >
        <div class="flex px-0 py-0 border-0 bg-white">
            <div class="w-full mr-6">{{ __('cms.Stel je naar te slepen items op') }}</div>
            <div class="w-full mr-2">{{ __('cms.Stel je te slepen items op') }}</div>
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
                $errorAnswerClassLeft = '';
                $errorAnswerClassRight = '';
            @endphp
            @error('question.answers.'.$loop->index.'.left')
            @php
                $errorAnswerClassLeft = 'border-allred'
            @endphp
            @enderror
            @error('question.answers.'.$loop->index.'.right')
            @php
                $errorAnswerClassRight = 'border-allred'
            @endphp
            @enderror
            <x-drag-item id="mc-{{ $answer->id }}" sortId="{{ $answer->order }}"
                         wireKey="option-{{ $answer->id }}" selid="drag-box"
                         class="flex px-0 py-0 border-0 bg-white relative regular"
                         slotClasses="w-full mr-0 "
                         dragClasses="hover:text-primary transition"
                         dragIconClasses="cursor-move {{ isset($preview) ? 'text-midgrey hover:text-midgrey' : '' }}"
                         :useHandle="true"
                         :keepWidth="true"
                         sortIcon="reorder"
            >
                <x-input.text class="w-full mr-2 {{ $errorAnswerClassLeft }} "
                              wire:model.lazy="cmsPropertyBag.answerStruct.{{ $loop->index }}.left"
                              selid="left-answer"
                              :disabled="isset($preview)"
                />
                <x-input.text class="w-full mr-1 {{ $errorAnswerClassRight }} "
                              wire:model.lazy="cmsPropertyBag.answerStruct.{{ $loop->index }}.right"
                              selid="right-answer"
                              :disabled="isset($preview)"
                />
                <x-slot name="after">
                    @if(isset($preview))
                        <x-icon.remove class="mx-2 w-4 mid-grey"/>
                    @else
                        <x-icon.remove class="mx-2 w-4 cursor-pointer  {{ $disabledClass }}"
                                       id="remove_{{ $answer->order }}"
                                       wire:click="__call('delete','{{$answer->id}}')"/>
                    @endif
                </x-slot>
            </x-drag-item>
        @endforeach
    </div>
    <div class="flex flex-col space-y-2 w-full">
        <x-button.primary class="mt-3 justify-center"
                          wire:click="__call('addAnswerItem')"
                          selid="add-answer-option-btn"
                          :disabled="isset($preview)"
        >
            <x-icon.plus/>
            <span >
                                    {{ __('cms.Itemset toevoegen') }}
                                    </span>
        </x-button.primary>
    </div>
@endsection
