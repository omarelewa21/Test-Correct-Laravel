@props([
    'question',
    'loop',
    'subQuestion' => false,
    'testQuestion' => null,
])

@php
    $active = false;
    if ($this->testQuestionId == $testQuestion->uuid) {
        if ($subQuestion === true) {
            if ($this->groupQuestionQuestionId == $question->groupQuestionQuestionUuid) {
                $active = true;
            }
        } else {
            $active = true;
        }
    }
@endphp
<div class="question-button flex items-center cursor-pointer bold py-2 hover:text-primary @if($subQuestion === false) pl-6 pr-4 @endif {{ $active ? 'question-active' : '' }}"
     wire:click="showQuestion('{{ $testQuestion ? $testQuestion->uuid : null }}', '{{ $question->uuid }}', {{ $subQuestion ? 1 : 0 }})"
{{--     @click="$dispatch('question-change', {old: '{{ $this->testQuestionId }}', new: '{{ $question->uuid }}' })"--}}
     @click="$store.cms.processing = true"
     style="max-width: 300px"
>
    <div class="flex w-full">
        <span class="rounded-full text-sm flex items-center justify-center border-3 relative px-1.5
              {{ $active ? 'text-white bg-primary border-primary ' : 'bg-white border-sysbase text-sysbase ' }}"
              style="min-width: 30px; height: 30px"
        >
            <span class="mt-px question-number">{{ $loop }}</span>
        </span>
        <div class="flex mt-.5 flex-1">
            <div class="flex flex-col flex-1 pl-2 pr-4">
                <span class="truncate" style="max-width: 160px; width: 160px"
                      title="{{ $question->title }}">{{ $question->title }}</span>
{{--                      title="{{ $question->title }}">({{ $question->getKey() }}){{ $question->title }}</span>--}}

                <div class="flex note text-sm regular justify-between">
                    <span>{{ __($this->getQuestionNameForDisplay($question)) }}</span>
                    <div class="flex items-center space-x-2">
                        <span class="flex">{{ $question->score }}pt</span>
                        @if($subQuestion === false)
                            <div class="flex items-center space-x-1 @if($question->attachments()->count() === 0) invisible @endif">
                                <x-icon.attachment class="flex"/>
                                <span class="flex">{{ $question->attachments()->count() }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-start space-x-2.5 mt-1 text-sysbase"

            >
                <div class="flex h-full rounded-md">
                    @if($question->closeable)
                        <x-icon.locked/>
                    @else
                        <x-icon.unlocked class="note"/>
                    @endif
                </div>
                <div class="flex">
                    <x-sidebar.cms.question-options :testQuestion="$testQuestion" :question="$question" :subQuestion="$subQuestion"/>
                </div>
            </div>
        </div>
    </div>
</div>
