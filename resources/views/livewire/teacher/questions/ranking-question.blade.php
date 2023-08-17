@extends($preview ?? 'livewire.teacher.questions.cms-layout')
@section('question-cms-question')
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

    <div class="flex w-full mt-4">
        {{ __('cms.Ranking Question Uitleg Text') }}
    </div>
    <div class="flex flex-col space-y-2 w-full mt-4"
         @if(!isset($preview)) wire:sortable="__call('updateRankingOrder')" @endif>
        <div class="flex px-0 py-0 border-0 bg-white">
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
                         class="flex px-0 py-0 border-0 bg-white relative"
                         sortId="{{ $answer->order }}"
                         wireKey="option-{{ $answer->id }}"
                         selid="drag-box"
                         slotClasses="w-full mr-0 "
                         dragClasses="hover:text-primary transition"
                         dragIconClasses="cursor-move {{ isset($preview) ? 'text-midgrey hover:text-midgrey' : '' }}"
                         :useHandle="true"
                         :keepWidth="true"
                         sortIcon="reorder"
            >
                <x-input.text class="w-full mr-1 {{ $errorAnswerClass }} "
                              wire:model.lazy="cmsPropertyBag.answerStruct.{{ $loop->index }}.answer"
                              selid="answer-field"
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
            <span>{{ __('cms.Item toevoegen') }}</span>
        </x-button.primary>
    </div>
@endsection
