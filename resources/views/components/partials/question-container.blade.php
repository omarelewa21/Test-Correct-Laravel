@props([
'question',
'number',
])
<div x-cloak
     x-data="{ showMe: false, progressBar: false, startTime: 0, endTime: 0, progress: 0 }"
     x-on:current-updated.window="showMe = ({{ $number }} == $event.detail.current)" x-show="showMe"
     x-transition:enter="transition duration-200"
     x-transition:enter-start="opacity-0 delay-200"
     x-transition:enter-end="opacity-100"
     x-on:change="$dispatch('current-question-answered')"
     x-on:start-timeout="
             progressBar = true;
             startTime = $event.detail.timeout;
             progress = startTime;
             attachment = $event.detail.attachment;

             timer = setInterval(function () {
                progress = startTime - endTime;
                endTime += 1;
                console.log(progress);

                if(progress === 0) {
                    clearInterval(timer);
                    $wire.closeQuestion(attachment);
                    progressBar = false;
                }
             }, 1000);
         "
>
    <div class="flex justify-end space-x-4 mt-6">
        @if(!$this->closedByAttachment)
            <x-attachment.attachments-button :question="$question"></x-attachment.attachments-button>
            <x-question.notepad-button :question="$question"></x-question.notepad-button>
        @endif
    </div>

    <div class="flex flex-col items-end mb-3 bg-white p-2 rounded-10" x-show.transition="progressBar">
        <span class="mb-1" x-text="`${progress} seconden over`"></span>
        <span class="p-3 w-full rounded-md bg-gray-200 overflow-hidden relative flex items-center">
            <span class="absolute h-full w-full bg-primary left-0 transition-all duration-300" :style="`width: ${ progress/startTime * 100 }%`"></span>
        </span>
    </div>

    <div class="flex flex-col p-8 sm:p-10 content-section relative">
        <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
            <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
                <span class="align-middle">{{ $number }}</span>
            </div>
            @if($this->closedByAttachment)
                <x-icon.locked class="ml-2"/>
            @endif
            <h1 class="inline-block ml-2 mr-6">{!! __($question->caption) !!}</h1>
            @if ($question->score > 0)
                <h4 class="inline-block">{{ $question->score }} pt</h4>
            @endif
        </div>
        <div class="flex flex-1">
            @if(!$this->closedByAttachment)
                {{ $slot }}
            @else
                <span>{{ __('test_take.question_closed') }}</span>
            @endif
        </div>
    </div>
</div>
