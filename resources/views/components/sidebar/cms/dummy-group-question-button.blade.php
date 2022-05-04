@props(['loop' => 1, 'testQuestionUuid' => ''])
<div class="question-button"
     x-data="{mode: @entangle('action'), owner: @entangle('owner'), name: @entangle('newQuestionTypeName'), groupId: '{{ $testQuestionUuid}}' }"
     x-init=""
     x-show="mode === 'add' && owner === 'group' && groupId === '{{ $this->testQuestionId}}'"
     x-cloak
     wire:key="dummy-{{ $loop.$testQuestionUuid.$this->testQuestionId }}"
>
    <div class="question-button flex items-center cursor-pointer bold py-2 hover:text-primary question-active"
         style="max-width: 300px"
    >
        <div class="flex w-full">
        <span class="rounded-full text-sm flex items-center justify-center border-3 relative px-1.5 text-white bg-midgrey border-mid-grey"
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
                <div class="flex items-start space-x-2.5 mt-1 text-sysbase hover:text-primary" wire:click="removeDummy">
                    <x-icon.trash/>
                    {{--                    <span class="note italic text-sm regular">Concept</span>--}}
                </div>
            </div>
        </div>
    </div>
</div>