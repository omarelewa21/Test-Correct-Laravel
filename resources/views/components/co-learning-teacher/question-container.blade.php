@props([
'question',
])
@php
    $editorId = 'question'.$this->questionIndex;
@endphp

<div class="flex flex-col pt-[14px] px-10 content-section rs_readable relative">
    <div class="flex flex-wrap items-center question-indicator pb-[13px] justify-between">
        <div class="flex items-center">

            <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                <span class="align-middle cursor-default">{{ $this->questionIndex }}</span>
            </div>
            <h4 class="inline-block ml-2 mr-6"
                selid="questiontitle">{{ __('co-learning.question') }}:
                {{ $question?->typeName }}
            </h4>
            <h7 class="inline-block">{{ $question->score }} pt</h7>
        </div>

        <div class="absolute right-[-14px] group" @click="showQuestion = ! showQuestion">
            <div class="w-10 h-10 rounded-full flex items-center justify-center group-hover:bg-primary group-hover:opacity-[0.05]"></div>
            <template x-if="true">
                <x-icon.chevron class="absolute top-[14px] left-4 text-sysbase transition"
                                x-bind:class="showQuestion ? 'rotate-90' : ''"
                                x-cloak
                />
            </template>
        </div>

    </div>

    <div x-show="showQuestion" x-collapse.duration.500ms x-cloak>
        <div class="w-full flex question-bottom-line mb-2"></div>

        <div class="flex flex-wrap">
            @foreach($question->attachments as $attachment)
                <x-attachment.badge-view :upload="false"
                                         :attachment="$attachment"
                                         :title="$attachment->title"
                                         wire:key="a-badge-{{ $attachment->id.$this->testTake->discussing_question_id }}"
                                         :question-id="$question->getKey()"
                                         :question-uuid="$question->uuid"
                />
            @endforeach
        </div>

        <div class="mt-2 pb-[33px] questionContainer">
            @if($question->type !== 'CompletionQuestion')
                {!! $question->getQuestionInstance()->question !!}
            @else
                {!! $this->convertCompletionQuestionToHtml() !!}
            @endif
        </div>
    </div>
</div>

