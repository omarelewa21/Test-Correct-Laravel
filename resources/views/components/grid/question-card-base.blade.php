<div {{ $attributes->merge(['class' => 'grid-card context-menu-container relative isolate', 'selid' => 'existing-question']) }}
     wire:key="questioncard-{{ $question->getQuestionInstance()->uuid }}-{{ $context }}"
     @if($question->isType('GroupQuestion'))
        x-on:click.stop="questionCardOpenGroup($el, @js($question->uuid), @js($inTest) )"
     @else
         wire:click="$emit('openModal', 'teacher.question-cms-preview-modal', {uuid: @js($question->uuid) , inTest: @js($inTest) } );"
     @endif
>
    <div class="flex w-full justify-between mb-2">
        <div class="flex gap-2.5 pr-2">
            @if($question->isType('GroupQuestion'))
                <x-icon.chevron class="mt-2.5"/>
            @elseif($order)
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
        <div class="flex items-end flex-col">
            <x-button.options id="question-card-option-button-{{ $question->uuid }}"
                              :uuid="$question->uuid"
                              context="question-card"
                              contextDataJson="{
                                  inTest: {{ $inTest ? 1 : 0 }},
                                  showQuestionBankAddConfirmation: {{ $showQuestionBankAddConfirmation ? 'true' : 'false' }}
                              }"
            />
            @hasSection('question-closed')
                @yield('question-closed')
            @endif
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
                @if($inTest)
                    <span title="{{ __('cms.Deze vraag is aanwezig in de toets.') }}">
                        <x-icon.checkmark-circle color="var(--cta-primary)"/>
                    </span>
                @endif
                <button x-show="Alpine.store('questionBank').active && !Alpine.store('questionBank').inGroup"
                        x-cloak
                        selid="existing-question-add-btn"
                        class="new-button button-primary w-10 items-center justify-center flex"
                        x-on:click.stop="addQuestionToTestFromTestCard($el, @js($question->uuid), @js($showQuestionBankAddConfirmation) )"
                >
                    <x-icon.plus/>
                </button>
            </div>
        </div>
    </div>
</div>