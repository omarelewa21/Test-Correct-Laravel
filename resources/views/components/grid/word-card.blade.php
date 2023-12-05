<div @class(["grid-card context-menu-container !pb-5", $attributes->get('class')])
        {{ $attributes->except('class') }}
>
    <div class="flex w-full justify-between mb-2 align-middle">
        <div class="flex flex-col truncate w-full">
            <h3 class="truncate text-inherit"
                title="{{ $word->text }}"
                style="color:inherit"
            >
                {{ $word->text }}
            </h3>
            <h3 class="truncate italic min-h-[2rem]" title="{{ $wordsString }}">
                {{ $wordsString }}
            </h3>
        </div>
{{--        <x-button.options id="test{{ $word->uuid }}"--}}
{{--                          context="word-card"--}}
{{--                          :uuid="$word->uuid"--}}
{{--        />--}}
    </div>
    <div class="flex w-full gap-8 text-base">
        <span class="bold">{{ __('question.relation') }}</span>
        <span class="italic">{!! $word->subject->name !!}</span>
    </div>
    <div class="flex w-full justify-between text-base items-center">
        <span>{{ $word->user->name_full }}</span>
        <div class="flex gap-4 items-center">
            @if($addable)
                @if($used)
                    <span class="w-10 h-10 flex rounded-10 border-3 border-cta items-center justify-center">
                        <x-icon.checkmark class="text-cta"/>
                    </span>
                @else
                    <x-button.icon x-on:click="addWord('{{ $word->uuid }}', {{ $word->getKey() }})"
                                   :title="__('cms.Toevoegen aan woordenlijst')">
                        <x-icon.plus />
                    </x-button.icon>
                @endif
            @endif
        </div>
    </div>
    <div class="flex w-full justify-center text-sm gap-1 note">
        <span>{{ __('general.Laatst gewijzigd') }}:</span>
        <span>{{ Carbon\Carbon::parse($word->updated_at)->format('j M \'y') }}</span>
    </div>
</div>