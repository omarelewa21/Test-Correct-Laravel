<div {{ $attributes->merge(['class' => 'grid-card bg-white p-6 rounded-10 card-shadow hover:text-primary cursor-pointer']) }}
     wire:key="questioncard-{{ $question->getQuestionInstance()->uuid }}"
     wire:click="openDetail('{{ $question->uuid }}')"
>
    <div class="flex w-full justify-between mb-2">
        <div class="flex">
            @if($question->isType('GroupQuestion'))
                <h3 class="line-clamp-2 break-all min-h-[64px] @if(blank($question->name)) italic @endif"
                    title="{{ $question->name }}">{{ filled($question->name) ? $question->name : __('question.no_question_text') }}</h3>
            @else
                <h3 class="line-clamp-2 break-all min-h-[64px] @if(blank($question->title)) italic @endif"
                    title="{{ $question->title }}">{{ $question->title ?? __('question.no_question_text') }}</h3>
            @endif
        </div>
        <div class="flex">
            <x-icon.options class="w-full h-8 w-5 text-sysbase hover:text-primary px-2 py-1"/>
        </div>
    </div>
    <div class="flex w-full justify-between text-base mb-1">
        <div class="flex">
            <span class="bold min-w-[125px]">{{ $question->typeName }}</span>
            <span>{!! optional($question->subject)->name ?? __('general.unavailable') !!}</span>
        </div>
        <div class="text-sm">
            <span class="note">Laatst gewijzigd:</span>
            <span class="note">{{ $lastUpdated }}</span>
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
            <div class="flex space-x-2.5 items-center">
                @if($inTest)
                    <span title="{{ __('cms.Deze vraag is aanwezig in de toets.') }}">
                        <x-icon.checkmark-circle color="var(--cta-primary)"/>
                    </span>
                @endif
                <button class="new-button button-primary w-10 items-center justify-center flex"
                        wire:click.stop="handleCheckboxClick('{{ $question->uuid }}')"
                        @click="$el.disabled = true"
                >
                    <x-icon.plus-2/>
                </button>
            </div>

        </div>

        {{--            <x-input.custom-checkbox wire:click.stop="handleCheckboxClick({{ $question->getKey() }})"--}}
        {{--                                     wire:key="checkbox-for-question{{ $question->uuid }}"--}}
        {{--                                     :checked="$this->isQuestionInTest($question->getKey())"--}}
        {{--            />--}}
    </div>
</div>