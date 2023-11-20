<div @class(["grid-card context-menu-container !pb-5", $attributes->get('class')])
        {{ $attributes->except('class') }}
>
    <div class="flex w-full justify-between mb-2 align-middle">
        <div class="flex flex-col truncate w-full">
            <h3 class="truncate text-inherit"
                title="{{ $wordList->name }}"
                style="color:inherit"
            >
                {{ $wordList->name }}
            </h3>
            <h3 class="truncate italic min-h-[2rem]" title="{{ $wordsString }}">
                {{ $wordsString }}
            </h3>
        </div>
        <x-button.options id="test{{ $wordList->uuid }}"
                          context="word-list-card"
                          :uuid="$wordList->uuid"
        />
    </div>
    <div class="flex w-full gap-8 text-base">
        <span class="bold">{{ __('question.relation') }}</span>
        <span class="italic">{!! $wordList->subject->name !!}</span>
    </div>
    <div class="flex w-full justify-between text-base items-center">
        <span>{{ $wordList->user->name_full }}</span>
        <div class="flex gap-4 items-center">
            <span class="note text-sm">{{ $wordList->words->count() }} @lang('cms.woorden')</span>

            @if($addable)
                <x-button.icon x-on:click="add('list', '{{ $wordList->uuid }}')" :title="__('cms.Woordenlijst toevoegen')">
                    <x-icon.plus />
                </x-button.icon>
            @endif
        </div>
    </div>
    <div class="flex w-full justify-center text-sm gap-1 note">
        <span>{{ __('general.Laatst gewijzigd') }}:</span>
        <span>{{ Carbon\Carbon::parse($wordList->updated_at)->format('j M \'y') }}</span>
    </div>
</div>