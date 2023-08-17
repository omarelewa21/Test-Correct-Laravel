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
    <div class="flex flex-col space-y-2 w-full mt-4"
    >
        <div class="flex px-0 py-0 border-0 bg-white justify-between relative flex-col">

            <div class="flex flex-col w-full space-y-2">
                <div class="flex w-full border-b border-system-base pb-2 bold">
                    <div class="w-10"></div>
                    <div class="w-28 ml-1">{{ __('test_take.thesis') }} 1</div>
                    <div class="w-28">{{ __('test_take.thesis') }} 2</div>
                    <div class="flex flex-1">{{ __('cms.Reden') }}</div>
                    <div class="w-14 mr-4">{{ __('cms.Punten') }}</div>
                </div>

                @foreach($this->cmsPropertyBag['arqStructure'] as $key => $arq)
                    <div class="flex w-full items-center transition border-2 border-secondary rounded-10 p-3"
                         @focusin="$el.classList.add('border-primary')"
                         @focusout="$el.classList.remove('border-primary')"
                         wire:ignore.self
                    >
                        <div class="w-8">{{ __($arq[0]) }}</div>
                        <div class="w-28">{{ __($arq[1]) }}</div>
                        <div class="w-28">{{ __($arq[2]) }}</div>
                        <div class="flex flex-1">{{ __($arq[3]) }}</div>
                        <div class="w-14" >
                            <x-input.text class="w-12 text-center"
                                          wire:model.debounce.250ms="cmsPropertyBag.answerStruct.{{ $key }}.score"
                                          title="{{ $this->cmsPropertyBag['answerStruct'][$key]['score'] }}"
                                          type="number"
                                          :onlyInteger="true"
                                          selid="score-field"
                                          :disabled="isset($preview)"
                            />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @error('question.answers.*.*')
    <div class="notification error stretched mt-4">
        <span class="title">{{ $message }}</span>
    </div>
    @enderror

    @error('question.score')
    <div class="notification error stretched mt-4">
        <span class="title">{{ $message }}</span>
    </div>
    @enderror

@endsection
