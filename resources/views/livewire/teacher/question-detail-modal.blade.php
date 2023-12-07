<div class="">
    <div class="px-6 pb-4 pt-6 main-shadow">
        <div class="flex w-full justify-between mb-2">
            <div class="flex pr-2.5">
                @if($question->type === 'GroupQuestion')
                    <h3 class="line-clamp-2 word-break-words min-h-[64px] @if(blank($question->name)) italic @endif"
                        title="{!! $question->name !!}">{!! filled($question->name) ? $question->name : __('question.no_question_text') !!} </h3>
                @else
                    <h3 class="line-clamp-2 word-break-words min-h-[64px] @if(blank($question->title)) italic @endif"
                        title="{{ $question->title }}">{{ $question->title ?? __('question.no_question_text') }}</h3>
                @endif
            </div>
            <x-button.close class="relative -top-3 -right-3" wire:click="$emit('closeModal')"/>
        </div>
        <div class="flex w-full justify-between text-base mb-1">
            <div class="flex">
                <span class="bold min-w-[125px]">{{ $question->typeName }}</span>
                <span>{!! optional($question->subject)->name ?? __('general.unavailable') !!}</span>
            </div>
            <div class="text-sm">
                <span class="note">{{ __('general.Laatst gewijzigd') }}:</span>
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
                <span class="note text-sm">{{ $question->isType('GroupQuestion') ?  $question->total_score ?? 0 : $question->score ?? 0 }}pt.</span>
            </div>
        </div>
    </div>
    <div class="px-6 py-4 h-[275px] overflow-auto">
        <div class="space-y-1.5">
            <div class="flex items-center">
                <span class="note text-sm flex min-w-[70px]">{{ __('general.Eigenaar') }}:</span>
                <span class="flex flex-1">{!! $question->owner()->value('name') !!}</span>
            </div>
            <div class="flex items-center">
                <span class="note text-sm flex min-w-[70px]">{{ __('general.Auteurs') }}:</span>
                <span class="flex flex-1">{!! $authors->implode(', ') !!}</span>
            </div>
            <div class="flex items-center">
                <span class="note text-sm flex min-w-[70px]">{{ __('general.Niveau') }}:</span>
                <span class="flex flex-1">{!! $question->educationLevel()->value('name') !!}</span>
            </div>
            <div class="flex items-center">
                <span class="note text-sm flex min-w-[70px]">{{ __('general.Leerjaar') }}:</span>
                <span class="flex flex-1">{!! $question->education_level_year !!}</span>
            </div>
            <div class="flex items-center">
                <span class="note text-sm flex min-w-[70px]">{{ __('general.Uniek ID') }}:</span>
                <span class="flex flex-1">{{ $question->getKey() }}</span>
            </div>
        </div>
        <div>
            <x-divider-with-title title="{{ __('cms.Instellingen') }}"/>
            <div>
                <x-input.toggle-row-with-title :small="true" :disabled="true"
                                               :toolTip="__('cms.close_after_answer_tooltip_text')"
                                               :checked="$question->closeable"
                                               :tooltipAlwaysLeft="true"
                >
                    <x-icon.locked/>
                    <span>{{ __('cms.Sluiten na beantwoorden') }}</span>
                </x-input.toggle-row-with-title>

                <x-input.toggle-row-with-title :small="true" :disabled="true"
                                               :toolTip="__('cms.make_public_tooltip_text')"
                                               :checked="$question->add_to_database"
                                               :tooltipAlwaysLeft="true"
                >
                    <x-icon.preview/>
                    <span>{{ __('cms.Openbaar maken') }}</span>
                </x-input.toggle-row-with-title>

                <x-input.toggle-row-with-title :small="true"
                                               :disabled="true"
                                               :checked="$question->maintain_position"
                >
                    <x-icon.shuffle-off/>
                    <span>{{ __('cms.Deze vraag niet shuffelen') }}</span>
                </x-input.toggle-row-with-title>
                @if(!$question->isType('Group'))
                    <x-input.toggle-row-with-title :small="true"
                                                   :disabled="true"
                                                   :checked="$question->discuss"
                    >
                        <x-icon.discuss/>
                        <span>{{ __('cms.Bespreken in de klas') }}</span>
                    </x-input.toggle-row-with-title>
                    <x-input.toggle-row-with-title :small="true"
                                                   :disabled="true"
                                                   :checked="$question->allow_notes"
                    >
                        <x-icon.notepad/>
                        <span>{{ __('cms.Notities toestaan') }}</span>
                    </x-input.toggle-row-with-title>
                    <x-input.toggle-row-with-title :small="true"
                                                   :disabled="true"
                                                   :checked="$question->decimal_score"
                    >
                        <x-icon.half-points/>
                        <span>{{ __('cms.Halve puntenbeoordeling mogelijk') }}</span>
                    </x-input.toggle-row-with-title>
                @endif
                @if($question->isType('Completion'))
                    <x-input.toggle-row-with-title :small="true"
                                                   :disabled="true"
                                                   :checked="$question->auto_check_incorrect_answer"
                    >
                        <x-icon.autocheck/>
                        <span>{{ __('cms.Automatisch nakijken') }}</span>
                    </x-input.toggle-row-with-title>
                    <x-input.toggle-row-with-title :small="true"
                                                   :disabled="true"
                                                   :checked="$question->auto_check_answer_case_sensitive"
                    >
                        <x-icon.case-sensitive/>
                        <span>{{ __('cms.Hoofdlettergevoelig nakijken') }}</span>
                    </x-input.toggle-row-with-title>
                @endif
                @if($question->isType('Group'))
                    <x-input.toggle-row-with-title :small="true"
                                                   :disabled="true"
                                                   :checked="$question->shuffle"
                    >
                        <x-icon.shuffle/>
                        <span>{{ __('cms.Vragen in deze group shuffelen') }}</span>
                    </x-input.toggle-row-with-title>
                @endif
            </div>
        </div>
        <div>
            <x-divider-with-title class="-mt-px" title="{{ __('cms.p_value_statistics') }}"/>
            <div class="py-3">
                @forelse($pValues as $pValue)
                    <x-pvalues-small :pValue="$pValue"/>
                @empty
                    <span class="note text-sm">{{ __('cms.no_statistics_available') }}</span>
                @endforelse
            </div>
        </div>

        <div>
            <x-divider-with-title title="{{ __('cms.Taxonomie') }}"/>
            <div class=""
                 x-data="{rtti: @js($question->rtti), bloom: @js($question->bloom), miller: @js($question->miller) }"
            >
                <div>
                    <x-input.toggle-row-with-title :small="true"
                                                   x-model="rtti"
                                                   disabled="true"
                                                   :checked="filled($question->rtti)"
                    >
                        <span class="bold">RTTI {{ __('cms.methode') }}</span>
                    </x-input.toggle-row-with-title>
                    <div x-show="rtti" class="grid grid-cols-4 pt-2 gap-2.5">
                        @foreach(['R'  , 'T1' , 'T2' , 'I'] as $value)
                            <label class="radio-custom">
                                <input wire:key="rtti-{{ $value }}"
                                       name="rtti"
                                       type="radio"
                                       value="{{ $value }}"
                                       disabled
                                       @if($value === $question->rtti) checked @endif
                                />
                                <span class="ml-2">{{ $value }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <x-input.toggle-row-with-title :small="true"
                                                   x-model="bloom"
                                                   :disabled="true"
                                                   :checked="filled($question->bloom)"
                    >
                        <span class="bold">BLOOM {{ __('cms.methode') }}</span>
                    </x-input.toggle-row-with-title>
                    <div x-show="bloom" class="grid grid-cols-3 pt-2 gap-2.5">
                        @foreach([ __('cms.Onthouden'), __('cms.Begrijpen'), __('cms.Toepassen'), __('cms.Analyseren'), __('cms.Evalueren'), __('cms.Creëren')] as $value)
                            <label class="radio-custom">
                                <input wire:key="bloom-{{ $value }}"
                                       name="bloom"
                                       type="radio"
                                       value="{{ $value }}"
                                       disabled
                                       @if($value === $question->bloom) checked @endif
                                />
                                <span class="ml-2">{{ $value }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <x-input.toggle-row-with-title :small="true"
                                                   x-model="miller"
                                                   :disabled="true"
                                                   :checked="filled($question->miller)"
                    >
                        <span class="bold">Miller {{ __('cms.methode') }}</span>
                    </x-input.toggle-row-with-title>
                    <div x-show="miller" class="grid grid-cols-2 pt-2 gap-2.5">
                        @foreach([ __('cms.Weten'), __('cms.Weten hoe'), __('cms.Laten zien'), __('cms.Doen'),] as $value)
                            <label class="radio-custom">
                                <input wire:key="miller-{{ $value }}"
                                       name="miller"
                                       type="radio"
                                       value="{{ $value }}"
                                       disabled
                                       @if($value === $question->miller) checked @endif
                                />
                                <span class="ml-2">{{ __($value) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="px-6 py-4 flex justify-end w-full" style="box-shadow: 0 -3px 8px 0 rgba(4, 31, 116, 0.2);">
        <div class="flex space-x-2.5 items-center">
            @if($this->showPreviewButton)
                <button class="new-button button-primary"
                        wire:click="openPreviewMode()"
                >
                    <x-icon.preview/>
                </button>
            @endif
            @if($this->inTest)
                <span title="{{ __('cms.Deze vraag is aanwezig in de toets.') }}">
                    <x-icon.checkmark-circle color="var(--cta-primary)"/>
                </span>
            @endif
            <button x-data="{}" x-cloak x-show="Alpine.store('questionBank').active" class="new-button button-primary w-10 items-center justify-center flex"
                    wire:click.stop="addQuestion"
                    @click="$el.disabled = true"
            >
                <x-icon.plus/>
            </button>
        </div>
    </div>
</div>
