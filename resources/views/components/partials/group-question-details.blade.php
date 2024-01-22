<div wire:key="group-question-detail-{{ $context }}-{{ $uuid }}">
    <div class="sticky @if($context !== 'question-bank') sticky-pseudo-bg @endif z-10 top-0 bg-lightGrey flex items-center px-8 py-1 border-b border-bluegrey"
         @if($context !== 'question-bank')
         :style="{top: $root.offsetTop + 'px'}"
         @endif
    >
        <div class="w-full max-w-screen-2xl mx-auto @if($context !== 'question-bank') px-10 @endif z-1 flex items-center justify-between">
            <div class="flex items-center space-x-2.5 z-1">
                <x-button.back-round x-on:click="$el.closest('[group-container]').dispatchEvent(new CustomEvent('close-group-details'));"/>
                <div class="flex text-lg bold">
                    <span>{{ __('question.Vraaggroep') }}: {{ $name ?? '' }}</span>
                </div>
            </div>

            <div class="flex gap-4 note">
                @if($closeable)
                    <x-icon.locked class="text-sysbase"/>
                @else
                    <x-icon.unlocked class="note"/>
                @endif
                <x-icon.options/>
            </div>
        </div>
    </div>
    <div class="w-full max-w-screen-2xl mx-auto px-10">
        <div class="py-6 flex w-full flex-col gap-2.5">
            <div class="flex w-full justify-between text-base">
                <div class="flex gap-4">
                    <span class="bold text-bold">{!! $subject->name !!}</span>
                    <span class="italic">{!! $subject->abbreviation !!}</span>
                    <span>{{ $authors->implode(', ') }}</span>
                </div>

                <div class="text-sm">
                    <span class="note">{{ __('general.Laatst gewijzigd') }}:</span>
                    <span class="note">{{ $lastUpdated }}</span>
                </div>
            </div>
            <div class="flex w-full justify-between note">
                <span class="flex note text-sm regular">{{ trans_choice('cms.vraag', $subQuestions->count()) }}</span>
                <div class="flex">
                    @if($attachmentCount)
                        <span class="flex items-center note text-sm regular pr-2"><x-icon.attachment class="mr-1"/> {{ $attachmentCount }}</span>
                    @endif
                    <span class="note text-sm">{{ $totalScore ?? 0 }}pt.</span>
                </div>
            </div>
            <div class="flex w-full items-center justify-end gap-4">
                <button class="new-button button-primary"
                        wire:click="$emit('openModal', 'teacher.question-cms-preview-modal', {uuid: @js($uuid)} );"
                >
                    <x-icon.preview/>
                </button>
                @if($inTest)
                    <span wire:ignore wire:key="checked-for-{{ $uuid }}"
                          title="{{ __('cms.Deze vraag is aanwezig in de toets.') }}">
                        <x-icon.checkmark-circle color="var(--cta-primary)"/>
                    </span>
                @endif
                <x-button.cta x-data="{}"
                              x-show="Alpine.store('questionBank').active && !Alpine.store('questionBank').inGroup"
                              x-on:click.stop="addQuestionToTestFromTestCard($el, '{{ $uuid }}', false );$el.disabled = true">
                    <x-icon.plus/>
                    <span>{{ __('cms.Toevoegen') }}</span>
                </x-button.cta>
            </div>
        </div>
        <x-grid class="subquestion-grid w-full">
            @forelse($subQuestions as $sub)
                <x-grid.question-card :question="$sub->question"
                                      :testUuid="$this->testId ?? null"
                                      :order="$loop->iteration"
                                      :showQuestionBankAddConfirmation="$showQuestionBankAddConfirmation"
                                      :inTest="$this->testContainsQuestion($sub->question)"
                />
            @empty
                <span>Geen subvragen</span>
            @endforelse
        </x-grid>
        <livewire:context-menu.question-card/>
    </div>
</div>