<div {{ $attributes->merge(['class' => 'grid-card bg-white p-6 rounded-10 card-shadow hover:text-primary']) }}
        wire:key="questioncard-{{ $question->getQuestionInstance()->uuid }}"
>
    <div class="flex w-full justify-between mb-2">
        <h3 class="line-clamp-2 min-h-[64px] @if(blank($question->title)) italic @endif" title="{{ $question->title }}">{{ $question->title ? $question->title : __('question.no_question_text') }}</h3>

        <x-icon.options class="text-sysbase"/>
    </div>
    <div class="flex w-full justify-between text-base mb-1">
        <div>
            <span class="bold">{{ $question->typeName }}</span>
            <span>{{ optional($question->subject)->name ?? __('general.unavailable') }}</span>
        </div>
        <div class="text-sm">
            <span class="note">Laatst gewijzigd:</span>
            <span class="note">{{ Carbon\Carbon::parse($question->updated_at)->format('d/m/\'y') }}</span>
        </div>
    </div>
    <div class="flex w-full justify-between text-base">
        <div>
            <span>{{ $question->getAuthorNamesString() }}</span>
        </div>

        <x-button.cta class="text-white" @click="$el.disabled = true" wire:click="handleCheckboxClick({{ $question->getKey() }})">
            {{ __('cms.Toevoegen') }}
        </x-button.cta>
{{--        @if($this->isQuestionInTest($question->getKey()))--}}
{{--            toegevoegd--}}
{{--        @else--}}
{{--            <x-input.custom-checkbox wire:click.stop="handleCheckboxClick({{ $question->getKey() }})"--}}
{{--                                     wire:key="checkbox-for-question{{ $question->uuid }}"--}}
{{--                                     :checked="$this->isQuestionInTest($question->getKey())"--}}
{{--            />--}}
{{--        @endif--}}
    </div>
</div>