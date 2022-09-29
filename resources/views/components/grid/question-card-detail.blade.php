<div {{ $attributes->merge(['class' => 'grid-card bg-white p-6 rounded-10 card-shadow hover:text-primary cursor-pointer relative']) }}
     wire:key="questioncard-{{ $question->uuid }}"
     @if($question->isType('GroupQuestion'))
         @click.stop="showGroupDetails('{{ $question->uuid }}')"
     @else
         wire:click.stop="openDetail('{{ $question->uuid }}')"
     @endif
>
    <div class="flex w-full justify-between mb-2">
        <div class="flex items-start gap-2.5 pr-2.5">
            @if($question->isType('GroupQuestion'))
                <x-icon.chevron class="mt-2.5"/>
                <h3 class="line-clamp-2 word-break-words min-h-[64px] @if(blank($question->name)) italic @endif"
                    title="{{ $question->name }}">
                    {{ $question->name ?? __('question.no_question_text') }}
                </h3>
            @else
                <span class="rounded-full border-current text-sm flex items-center justify-center border-3 relative px-1.5 min-w-[30px] h-[30px]"
                      style="">
                    <span class="mt-px bold">{{ $testQuestion->order }}</span>
                </span>
                <h3 class="line-clamp-2 word-break-words min-h-[64px] @if(blank($question->title)) italic @endif"
                    title="{{ $question->title }}">
                    {{ $question->title ?? __('question.no_question_text') }}
                </h3>
            @endif

        </div>
        <div class="flex flex-col">

            <div id="question-card-option-button-{{ $question->uuid }}"
                 wire:key="question-card-option-button-{{ $question->uuid }}"
                 class="flex justify-center items-center w-10 h-10 absolute top-3 right-3 rounded-full hover:bg-primary/5 hover:text-primary text-sysbase"
                 style="transition: background-color ease-in-out 100ms"
                 :class="{'option-menu-active !text-white hover:!text-primary': menuOpen }"
                 x-data="{
                    menuOpen: false,
                    uuid: '{{ $question->uuid }}',
                 }"
                 @close-menu="menuOpen = false"
                 @click.stop="
                    menuOpen = !menuOpen;
                    if(menuOpen) {
                        $dispatch('question-card-context-menu-show', {
                            uuid,
                            button: $el,
                            coords: {
                                top: $el.closest('.grid-card').offsetTop,
                                left: $el.closest('.grid-card').offsetLeft + $el.closest('.grid-card').offsetWidth
                            },
                            contextData: {}
                        })
                    } else {
                        $dispatch('question-card-context-menu-close')
                    }
                    "
            >
                <x-icon.options/>
            </div>

            @if($testQuestion->closeable)
                <x-icon.locked class="mt-auto mb-2"/>
            @else
                <x-icon.unlocked class="mt-auto mb-2"/>
            @endif
        </div>
    </div>
    <div class="flex w-full justify-between text-base mb-1">
        <div>
            <span class="bold">{{ $question->typeName }}</span>
            <span>{{ optional($question->subject)->name ?? __('general.unavailable') }}</span>
        </div>
        <div class="text-sm">
            <span class="note">{{ __('general.Laatst gewijzigd') }}:</span>
            <span class="note">{{ Carbon\Carbon::parse($question->updated_at)->format('d/m/\'y') }}</span>
        </div>
    </div>
    <div class="flex w-full justify-between items-center text-base">
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
        <div class="flex space-x-2.5">
            @if($question->isType('GroupQuestion'))
                <span class="note flex items-center space-x-1 text-sm">{{ trans_choice('cms.vraag', $question->getQuestionCount()) }}</span>
            @endif
            @if($attachmentCount)
                <span class="note flex items-center space-x-1 text-sm">
                    <x-icon.attachment/>
                    <span>{{ $attachmentCount }}</span>
                </span>
            @endif
            <span class="note text-sm">{{ $question->isType('GroupQuestion') ?  $question->total_score ?? 0 : $question->score ?? 0 }}pt.</span>
        </div>
    </div>
</div>
