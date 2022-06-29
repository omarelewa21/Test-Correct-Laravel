@extends($preview ?? 'livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.rich-textarea
            wire:model.debounce.1000ms="question.question"
            editorId="{{ $questionEditorId }}"
            type="cms"
            :disabled="isset($preview)"
    />
@endsection

@section('question-cms-answer')

    <div class="flex  col-2 space-x-2 w-full mt-4">
        <div class="flex inline-block w-full items-center">{{ __('cms.Is bovenstaande vraag/stelling juist of onjuist?') }}</div>
        <div class="inline-flex bg-off-white max-w-max border rounded-lg truefalse-container transition duration-150
                    @error('question.answers') border-allred @else border-blue-grey @enderror
                    ">
            @foreach( ['true', 'false'] as $optionValue)

                <label id="truefalse-{{$optionValue}}" wire:key="truefalse-{{$optionValue}}"
                       selid="{{$optionValue}}-answer"
                       for="link{{ $optionValue }}"
                       class="bg-off-white border border-off-white rounded-lg trueFalse bold transition duration-150
                                          @if($loop->iteration == 1) true border-r-0 @else false border-l-0 @endif
                       {!! $cmsPropertyBag['tfTrue'] === $optionValue ? 'active' : '' !!}"
                >
                    <input
                        wire:model="cmsPropertyBag.tfTrue"
                        id="link{{ $optionValue }}"
                        name="Question_TrueFalse"
                        type="radio"
                        class="hidden"
                        value="{{ $optionValue }}"
                        selid="testtake-radiobutton"
                    >
                    <span>
                                                @if ($optionValue == 'true')
                            <x-icon.checkmark/>
                        @else
                            <x-icon.close/>
                        @endif
                                            </span>
                </label>
                @if($loop->first)
                    <div class="bg-blue-grey" style="width: 1px; height: 30px; margin-top: 3px"></div>
                @endif
            @endforeach
        </div>
    </div>

@endsection
