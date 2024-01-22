@props([
'question',
'number',
])
<div x-cloak
     x-data="studentPlayerQuestionContainer(@js($number),@js($question->id), @js($this->reinitializedTimeoutData ?? null))"
     x-show="showMe"
     x-transition:enter="transition duration-200"
     x-transition:enter-start="opacity-0 delay-200"
     x-transition:enter-end="opacity-100"
     x-on:current-updated.window="currentUpdated($event.detail.current)"
     x-on:change="Livewire.emit('current-question-answered', @js($number))"
     x-on:refresh-question.window="refreshQuestion($event.detail)"
     x-on:close-this-question.window="closeThisQuestion($event.detail)"
     x-on:close-this-group.window="closeThisGroup($event.detail)"
     x-on:start-timeout="startTimeout($event.detail)"
     x-on:mark-infoscreen-as-seen.window="markInfoscreenAsSeen($event.detail, @js($this->question->uuid))"
     x-on:force-taken-away-blur.window="$el.style.opacity = $event.detail.shouldBlur ? 0 : 1 ;"
     questionComponent
     :class="{ 'rs_readable': showMe }"
>
    @if($this->group)
        <x-accordion.container :active-container-key="'answer'">
            <x-accordion.block key="answer"
                :coloredBorderClass="'student'"
                :emitWhenSet="false"
                :disabledBackGround="true"
            >
            <x-slot:title>
                    <h4 wire:ignore class="inline-flex font-bold"> {{__('cms.group-question')}} : {{ $this->group->name }}</h4>
            </x-slot:title>
            <x-slot:body>
                    <div class="flex flex-1 flex-col">
                        <div class="flex flex-wrap">
                            <x-attachment.student-buttons-container :question="$question" :group="$this->group" :blockAttachments="$this->blockAttachments"/>
                        </div>
                        <div class="mb-5 questionContainer" questionHtml wire:ignore>{!! $this->group->question->converted_question_html !!}</div>
                    </div>
            </x-slot:body>
            </x-accordion.block>
        </x-accordion.container>
    @endif
    <div class="flex justify-end space-x-4 mt-6">
        @if(!$this->closed )
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

            <h2 wire:ignore class="inline-block ml-2 mr-6" selid="questiontitle">{!! __($question->caption) !!}</h2>

            @if ($question->score > 0)
                <h4 wire:ignore class="inline-block">{{ $question->score }} pt</h4>
            @endif
         
        </div>
        <div class="flex flex-1 flex-col">
            @if(!$this->closed)
                <div class="flex flex-wrap">
                    <x-attachment.student-buttons-container :questionAttachmentsExist="true" :question="$question" :group="$this->group" :blockAttachments="$this->blockAttachments"/>
                </div>
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
