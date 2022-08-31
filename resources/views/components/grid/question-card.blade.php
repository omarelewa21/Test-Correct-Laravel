<div {{ $attributes->merge(['class' => 'grid-card bg-white p-6 rounded-10 card-shadow hover:text-primary cursor-pointer relative']) }}
     wire:key="questioncard-{{ $question->getQuestionInstance()->uuid }}"
     @if($question->isType('GroupQuestion'))
         @click="showGroupDetails('{{ $question->uuid }}', @js($inTest));"
     @else
         wire:click="openDetail('{{ $question->uuid }}', @js($this->isQuestionInTest($question->id) || $this->isQuestionInTest($question->derived_question_id)))"
        @endif
>
    <div class="flex w-full justify-between mb-2 pr-10">
        <div class="flex gap-2.5">
            @if($order)
                <span class="rounded-full border-current text-sm flex items-center justify-center border-3 relative px-1.5 min-w-[30px] h-[30px]"
                      style="">
                    <span class="mt-px bold">{{ $order }}</span>
                </span>
            @endif
            @if($question->isType('GroupQuestion'))
                <h3 class="line-clamp-2 word-break-words min-h-[64px] @if(blank($question->name)) italic @endif"
                    title="{!! $question->name !!}">{!! filled($question->name) ? $question->name : __('question.no_question_text') !!}</h3>
            @else
                <h3 class="line-clamp-2 word-break-words min-h-[64px] @if(blank($question->title)) italic @endif"
                    title="{{ $question->title }}">{{ $question->title ?? __('question.no_question_text') }}</h3>
            @endif
        </div>
        <div id="question-card-option-button-{{ $question->uuid }}"
             wire:key="question-card-option-button-{{ $question->uuid }}"
             class="flex justify-center items-center w-10 h-10 absolute top-3 right-3 rounded-full hover:bg-primary/5 hover:text-primary text-sysbase"
             style="transition: background-color ease-in-out 100ms"
             :class="{'option-menu-active !text-white hover:!text-primary': menuOpen }"
             x-data="{
                    menuOpen: false,
                    mouseOver: false,
                    questionUuid: '{{ $question->uuid }}',
                    inTest: @js($this->isQuestionInTest($question->id) || $this->isQuestionInTest($question->derived_question_id))
                 }"
             @close-menu="menuOpen = false"
             @click.stop="
                    menuOpen = !menuOpen;
                    if(menuOpen) {
                        $dispatch('question-card-context-menu-show', {questionUuid, inTest, button: $el,
                        coords: {
                            top: $el.closest('.grid-card').offsetTop,
                            left: $el.closest('.grid-card').offsetLeft + $el.closest('.grid-card').offsetWidth
                        }})
                    } else {
                        $dispatch('question-card-context-menu-close')
                    }
                    "
        >
            <x-icon.options/>
        </div>
    </div>
    <div class="flex w-full justify-between text-base mb-1">
        <div class="flex gap-5">
            <span class="bold">{{ $question->typeName }}</span>
            <span>{!! optional($question->subject)->name ?? __('general.unavailable') !!}</span>
        </div>
        <div class="text-sm">
            <span class="note">{{ __('general.Laatst gewijzigd') }}: {{ $lastUpdated }}</span>
        </div>
    </div>
    <div class="flex w-full justify-between text-base">
        <div title="{{ $authors->implode(', ') }}">
            @if($authors->count() > 1)
                <span>{{ $authors->first() }}, {{ $authors[1] }}</span>
                @if($authors->count() > 2)
                    <span>+{{ ($authors->count() - 2) }}</span>
                @endif
            @else
                <span>{{ $authors->first() }}</span>
            @endif
        </div>

        <div class="flex space-x-2.5 items-center">
            <div class="flex space-x-2.5">
                @if($attachmentCount)
                    <span class="note flex items-center space-x-1 text-sm">
                            <x-icon.attachment/>
                            <span>{{ $attachmentCount }}</span>
                        </span>
                @endif
                <span class="note text-sm">{{ $question->isType('GroupQuestion') ?  $question->total_score ?? 0 : $question->score ?? 0 }}pt.</span>
            </div>
            <div class="flex space-x-2.5 items-center" wire:key="is_present_{{ $question->id }}">
                @if($this->isQuestionInTest($question->id) || $this->isQuestionInTest($question->derived_question_id))
                    <span title="{{ __('cms.Deze vraag is aanwezig in de toets.') }}">
                        <x-icon.checkmark-circle color="var(--cta-primary)"/>
                    </span>
                @endif
                <button x-show="Alpine.store('questionBank').active"
                        class="new-button button-primary w-10 items-center justify-center flex"
                        @click.stop="addQuestionToTest($el, '{{ $question->uuid }}')"
                >
                    <x-icon.plus-2/>
                </button>
            </div>

        </div>
    </div>
</div>