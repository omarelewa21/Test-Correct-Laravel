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
    <div class="flex flex-row justify-between gap-6">
        <x-input.toggle-row-with-title wire:model="question.all_or_nothing"
                                       :toolTip="__('cms.all_or_nothing_tooltip_text')"
                                       :disabled="isset($preview)"
        >
            <span class="bold"> {{ __('cms.Alles of niets correct') }}</span>
        </x-input.toggle-row-with-title>

        <x-input.toggle-row-with-title wire:model="question.fix_order"
                                       :disabled="isset($preview)">
            <span class="bold"> {{ __('cms.fix_order') }}</span>
        </x-input.toggle-row-with-title>
    </div>
    <div class="flex w-full mt-4">{{ __('cms.MultipleChoice Question Uitleg Text') }}</div>
    <div class="flex flex-col space-y-2 w-full mt-4"
         @if(!isset($preview)) wire:sortable="__call('updateMCOrder')" @endif
         x-data="{
            addPointsPosition: () => $refs.punten.style.right = (102 - $refs.punten.offsetWidth) +'px',
            syncInputValue: (property, value) => {
                    if($store.cms.dirty) {
                        getClosestLivewireComponentByAttribute(
                            document.querySelector('#cms'),
                            'cms'
                        )
                        .sync(property, value)
                    }
                }
            }"
         x-init="addPointsPosition()"
         @tabchange.window="setTimeout(() => addPointsPosition(), 100)"
         @resize.window.debounce.100ms="addPointsPosition()"
    >
        <div class="flex px-0 py-0 border-0 bg-white justify-between relative">
            <div class="bold text-base">{{ __('cms.Antwoord') }}</div>
            <div wire:ignore.self x-ref="punten" class="absolute bold text-base">{{ __('cms.Punten') }}</div>
        </div>
        @php
            $disabledClass = "icon disabled cursor-not-allowed";
            $showInputField = !(isset($preview) && isset($this->isCito) && $this->isCito === true);
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
                         class="flex !px-0 !py-0 !border-0 bg-white regular"
                         slotClasses="w-full space-x-2.5 justify-between"
                         sortIcon="reorder"
                         dragIconClasses="cursor-move {{ isset($preview) ? 'text-midgrey hover:text-midgrey' : '' }}"
                         alignItems="{{ $showInputField ? 'center' : 'start' }}"
            >
                @if($showInputField)
                    <x-input.text class="w-full  {{ $errorAnswerClass }} "
                                  wire:model.lazy="cmsPropertyBag.answerStruct.{{ $loop->index }}.answer"
                                  selid="answer-field"
                                  :disabled="isset($preview)"
                    />
                @else
                    <span>{!! $answer->answer !!}</span>
                @endif
                <div class=" text-center justify-center">
                    <x-input.text class="w-12 text-center {{ $errorScoreClass }}"
                                  wire:model.defer="cmsPropertyBag.answerStruct.{{ $loop->index }}.score"
                                  wire:key="cmsPropertyBag.answerStruct.{{ $answer->id }}"
                                  title="{{ $answer->score }}"
                                  type="number"
                                  step="0.5"

                                  selid="score-field"
                                  :disabled="isset($preview)"

                                  x-on:focusin="$el.value = ((Math.round($el.value*2)/2) === 0 ? '' : $el.value)"
                                  x-on:focusout="$el.value = ($el.value === '' ? 0 : (Math.round($el.value*2)/2) )"
                                  x-on:change="if(!$store.cms.dirty) $store.cms.dirty = true"
                                  x-on:store-current-question.window="syncInputValue('cmsPropertyBag.answerStruct.{{ $loop->index }}.score', $el.value)"
                    />
                </div>
                <x-slot name="after" >
                    @isset($preview)
                        <x-icon.remove class="mx-2 w-4 mid-grey"/>
                    @else
                        <x-icon.remove class="cursor-pointer {{ $disabledClass }}"
                                       id="remove_{{ $answer->order }}"
                                       wire:click="__call('delete', '{{$answer->id}}')"/>
                    @endisset
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
            <span>
                                    {{ __('cms.Item toevoegen') }}
                                    </span>
        </x-button.primary>
    </div>
@endsection
