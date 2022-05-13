@props([
'question',
'number',
])
<div x-cloak
     x-data="{ showMe: false, progressBar: false, startTime: 0, endTime: 1, progress: 0 }"
     x-init="$watch('showMe', () => { if(showMe) { $dispatch('visible-component', {el: $el});} })"
     x-show="showMe"
     x-on:current-updated.window="
        showMe = ({{ $number }} == $event.detail.current);
        if(showMe) {$wire.updateAnswerIdForTestParticipant();}
        "
     x-transition:enter="transition duration-200"
     x-transition:enter-start="opacity-0 delay-200"
     x-transition:enter-end="opacity-100"
     x-on:change="Livewire.emit('current-question-answered', {{ $number }})"
     x-on:refresh-question.window="
        if ($event.detail.indexOf({{ $number }}) !== -1) {
                $wire.set('closed', true);
        }"

     x-on:close-this-question.window="
        if(showMe) {
            $wire.set('showCloseQuestionModal', true);
            $wire.set('nextQuestion', $event.detail);
        }

    "
     x-on:close-this-group.window="
        if(showMe) {
            $wire.set('showCloseGroupModal', true);
            $wire.set('nextQuestion', $event.detail);
        }
    "
     x-on:start-timeout="
             $wire.set('blockAttachments',true);
             progressBar = true;
             startTime = $event.detail.timeout;
             progress = startTime;

             var timer = setInterval(function () {
                progress = startTime - endTime;
                endTime += 1;

                if(progress === 0) {
                    showMe ? $wire.closeQuestion({{ $number+1 }}) : $wire.closeQuestion();
                    clearInterval(timer);
                    progressBar = false;
                    endTime = 1;
                }
             }, 1000);
         "
     x-on:mark-infoscreen-as-seen.window="if('{{ $this->question->uuid }}' == $event.detail){ $wire.markAsSeen($event.detail) }"
     x-on:force-taken-away-blur.window="$el.style.opacity = $event.detail.shouldBlur ? 0 : 1 ;"
     questionComponent
     :class="{ 'rs_readable': showMe }"

>
    <div class="flex justify-end space-x-4 mt-6">
        @if(!$this->closed )
            <x-attachment.attachments-button :question="$question" :blockAttachments="$this->blockAttachments"></x-attachment.attachments-button>
            <x-question.notepad-button :question="$question" ></x-question.notepad-button>
        @endif
    </div>

    <x-timeout-progress-bar/>

    <div class="flex flex-col p-8 sm:p-10 content-section relative">
        <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
            <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
                <span class="align-middle" wire:ignore >{{ $number }}</span>
            </div>

            @if($question->closeable && !$this->closed)
                <x-icon.unlocked class="ml-2"/>
            @elseif($this->closed)
                <x-icon.locked class="ml-2"/>
            @endif

            <h1 wire:ignore class="inline-block ml-2 mr-6" selid="questiontitle">{!! __($question->caption) !!}</h1>

            @if ($question->score > 0)
                <h4 wire:ignore class="inline-block">{{ $question->score }} pt</h4>
            @endif
            @if($this->group)
                <h6 wire:ignore class="inline-flex ml-auto">{{ $this->group->name }}</h6>
            @endif
        </div>
        <div class="flex flex-1 flex-col">
            @if(!$this->closed)
                @if($this->group)
                    <div class="mb-5" questionHtml wire:ignore>{!! $this->group->question->converted_question_html !!}</div>
                @endif
                <div class="questionContainer">
                    {{ $slot }}
                </div>
            @else
                <span>{{ __('test_take.question_closed_text') }}</span>
            @endif
        </div>
    </div>

    <x-modal maxWidth="lg" wire:model="showCloseQuestionModal">
        <x-slot name="title">{{ __('test_take.close_question') }}</x-slot>
        <x-slot name="body">{{ __('test_take.close_question_modal_text') }}</x-slot>
        <x-slot name="actionButton">
            <x-button.primary size="sm" wire:click="closeQuestion('{{$this->nextQuestion}}')" @click="show = false">
                <span>{{__('test_take.continue')}}</span>
                <x-icon.chevron/>
            </x-button.primary>
        </x-slot>
    </x-modal>

    <x-modal maxWidth="lg" wire:model="showCloseGroupModal">
        <x-slot name="title">{{ __('test_take.close_group') }}</x-slot>
        <x-slot name="body">{{ __('test_take.close_group_modal_text') }}</x-slot>
        <x-slot name="actionButton">
            <x-button.primary size="sm" wire:click="closeGroup('{{$this->nextQuestion}}')" @click="show = false">
                <span>{{__('test_take.continue')}}</span>
                <x-icon.chevron/>
            </x-button.primary>
        </x-slot>
    </x-modal>
</div>
