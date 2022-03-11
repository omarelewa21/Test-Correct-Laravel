@props([
    'question',
    'loop',
    'subQuestion' => 'false',
    'testQuestion' => null,
])
<div class="question-button flex items-center cursor-pointer bold py-2 @if(!$subQuestion) px-6 @endif @if($this->testQuestionId === $question->uuid) primary @endif"
     wire:click="showQuestion('{{ $testQuestion ? $testQuestion->uuid :null}}', '{{ $question->uuid }}', {{ $subQuestion }})"
     @click="$dispatch('question-change', {old: '{{ $this->testQuestionId }}', new: '{{ $question->uuid }}' })"
     style="max-width: 300px"
>
    <div class="flex w-full">
        <span class="rounded-full text-sm flex items-center justify-center border-3 relative px-1.5
        @if($this->testQuestionId === $question->uuid) text-white bg-primary border-primary @else bg-white border-sysbase text-sysbase @endif"
              style="min-width: 30px; height: 30px"
        >
            <span class="mt-px question-number">{{ $loop  }}</span>
        </span>
        <div class="flex mt-.5 flex-1">
            <div class="flex flex-col flex-1 pl-2 pr-4">
                <span class="max-w-[167px] truncate">{{ $question->getQuestionHtml() }}</span>

                <div class="flex note text-sm regular justify-between">
                    <span>{{ __($this->getQuestionNameForDisplay($question)) }}</span>
                    <div class="flex items-center space-x-2">
                        <span class="flex">{{ $question->score }}pt</span>
                        @if(!$subQuestion)
                            <div class="flex items-center space-x-1">
                                <x-icon.attachment class="flex"/>
                                <span class="flex">{{ $question->attachments()->count() }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex space-x-2.5 mt-1 text-sysbase">
                <x-icon.locked/>
                <x-icon.options/>
            </div>
        </div>
    </div>
</div>
