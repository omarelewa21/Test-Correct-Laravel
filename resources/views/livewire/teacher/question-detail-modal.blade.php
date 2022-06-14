<div class="">
    <div class="px-6 pb-4 pt-6 main-shadow">
        <div class="flex w-full justify-between mb-2">
            @if($question->type === 'GroupQuestion')
                <h3 class="line-clamp-2 min-h-[64px] @if(blank($question->name)) italic @endif" title="{{ $question->name }}">{{ filled($question->name) ? $question->name : __('question.no_question_text') }}</h3>
            @else
                <h3 class="line-clamp-2 min-h-[64px] @if(blank($question->title)) italic @endif" title="{{ $question->title }}">{{ $question->title ?? __('question.no_question_text') }}</h3>
            @endif

            <x-icon.close class="hover:text-primary cursor-pointer" wire:click="$emit('closeModal')"/>
        </div>
        <div class="flex w-full justify-between text-base mb-1">
            <div>
                <span class="bold">{{ $question->typeName }}</span>
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

            <div class="">
                @if($attachmentCount)
                    <span class="note flex items-center space-x-1 text-sm">
                        <x-icon.attachment/>
                        <span>{{ $attachmentCount }}</span>
                    </span>
                @endif
                <span class="note text-sm">{{ $question->score }}pt.</span>
            </div>
        </div>
    </div>
    <div class="px-6 py-4 h-[275px] overflow-auto">
        <div>
            <div class="flex items-center">
                <span class="note text-sm flex min-w-[70px]">{{ __('general.Eigenaar') }}:</span><span class="flex flex-1">Waarde</span>
            </div>
            <div class="flex items-center">
                <span class="note text-sm flex min-w-[70px]">{{ __('general.Auteurs') }}:</span><span class="flex flex-1">Waarde</span>
            </div>
            <div class="flex items-center">
                <span class="note text-sm flex min-w-[70px]">{{ __('general.Niveau') }}:</span><span class="flex flex-1">Waarde</span>
            </div>
            <div class="flex items-center">
                <span class="note text-sm flex min-w-[70px]">{{ __('general.Uniek ID') }}:</span><span class="flex flex-1">Waarde</span>
            </div>
            <div class="flex items-center">
                <span class="note text-sm flex min-w-[70px]">{{ __('general.Periode') }}:</span><span class="flex flex-1">Waarde</span>
            </div>
        </div>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium corporis delectus eos exercitationem explicabo harum magni nam, odit qui quidem rerum saepe vel voluptatum. Ab asperiores laudantium magni non placeat!</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium corporis delectus eos exercitationem explicabo harum magni nam, odit qui quidem rerum saepe vel voluptatum. Ab asperiores laudantium magni non placeat!</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium corporis delectus eos exercitationem explicabo harum magni nam, odit qui quidem rerum saepe vel voluptatum. Ab asperiores laudantium magni non placeat!</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium corporis delectus eos exercitationem explicabo harum magni nam, odit qui quidem rerum saepe vel voluptatum. Ab asperiores laudantium magni non placeat!</p>
    </div>
    <div class="px-6 py-4 flex justify-end w-full" style="box-shadow: 0 -3px 8px 0 rgba(4, 31, 116, 0.2);">
        <div class="flex space-x-2.5">
            <button class="new-button button-primary">
                <x-icon.settings/>
            </button>
            <button class="new-button button-primary">
                <x-icon.preview/>
            </button>
            <x-button.cta>
                <x-icon.plus-2/>
                <span>Selecteren</span>
            </x-button.cta>
        </div>
    </div>
</div>