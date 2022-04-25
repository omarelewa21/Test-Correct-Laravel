@props(['testQuestion' => null, 'loop' => 1])
@php
    $groupDummy = $testQuestion != null
@endphp
<div class=""
     x-data="{mode: @entangle('action'), owner: @entangle('owner'), name: @entangle('newQuestionTypeName')}"
     x-init=""
     x-show="mode === 'add'"
     x-cloak
>
    <div class="question-button flex items-center cursor-pointer bold py-2 hover:text-primary @if(!$groupDummy) pl-6 pr-4 @endif question-active"
         style="max-width: 300px"
    >
        <div class="flex w-full">
        <span class="rounded-full text-sm flex items-center justify-center border-3 relative px-1.5 text-white bg-primary border-primary"
              style="min-width: 30px; height: 30px"
        >
            <span class="mt-px question-number">{{ $loop+1  }}</span>
        </span>
            <div class="flex mt-.5 flex-1">
                <div class="flex flex-col flex-1 pl-2 pr-4">
                <span class="truncate italic" style="max-width: 140px;"
                      title="">{{ __('question.no_question_text') }}</span>

                    <div class="flex note text-sm regular justify-between">
                        <span x-text="name"></span>
                        <div class="flex items-center space-x-2"></div>
                    </div>
                </div>
                <div class="flex items-start space-x-2.5 mt-1">
                    <span class="note italic text-sm regular">Concept</span>
                </div>
            </div>
        </div>
    </div>

    <div x-ref="translate" class="hidden invisible"
         data-
    ></div>
</div>