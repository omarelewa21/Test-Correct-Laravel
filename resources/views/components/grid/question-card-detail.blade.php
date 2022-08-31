<div {{ $attributes->merge(['class' => 'grid-card bg-white p-6 rounded-10 card-shadow hover:text-primary cursor-pointer']) }}
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
            <x-icon.options class="ml-auto"/>
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
                <span class="note flex items-center space-x-1 text-sm">{{ trans_choice('cms.vraag', ['count' => $question->getQuestionCount()]) }}</span>
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
