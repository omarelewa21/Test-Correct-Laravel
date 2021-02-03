@props([
'question',
'q',
'number',
])
<div x-cloak x-data="{ showMe: {!! $number === $q ? 'true' : 'false'  !!} }"
     x-on:current-updated.window="showMe = ({{ $number }} == $event.detail.current)" x-show="showMe">
    <div class="flex justify-end space-x-4 mt-6">
        <x-attachment.attachments-button :question="$question"></x-attachment.attachments-button>
        <x-question.note :question="$question"></x-question.note>
    </div>
    <div class="flex flex-col p-8 sm:p-10 content-section">
        <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
            <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
                <span class="align-middle">{{ $number }}</span>
            </div>
            <h1 class="inline-block ml-2 mr-6">{!! __($question->caption) !!}</h1>
            @if ($question->score > 0)
                <h4 class="inline-block">{{ $question->score }} pt</h4>
            @endif
        </div>
        <div class="flex flex-1">
            {{ $slot }}
        </div>
    </div>
</div>
