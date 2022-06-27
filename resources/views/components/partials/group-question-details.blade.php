<div>
    <div class="flex items-center justify-between px-8 py-1 border-b border-bluegrey">
        <x-button.text-button @click="closeGroupDetail()">
            <x-icon.arrow-left/>
            <span>{{ __('question.Vraaggroep') }}: {{ $name ?? 'kaas' }}</span>
        </x-button.text-button>

        <div class="flex gap-4 note">
            @if($closeable)
            <x-icon.locked class="text-sysbase"/>
            @else
            <x-icon.unlocked class="note"/>
                @endif
            <x-icon.options/>
        </div>
    </div>
    <div class="flex flex-col mx-8">
        <div class="py-6 flex w-full flex-col gap-2.5">
            <div class="flex w-full justify-between text-base">
                <div class="flex gap-4">
                    <span class="bold text-bold">{!! $subject->name !!}</span>
                    <span class="italic">{!! $subject->abbreviation !!}</span>
                    <span>{{ $authors->implode(', ') }}</span>
                </div>

                <div class="text-sm">
                    <span class="note">Laatst gewijzigd:</span>
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
            <div class="flex w-full justify-end">
                <x-button.cta wire:click.stop="handleCheckboxClick('{{ $uuid }}')"
                              @click="$el.disabled = true">
                    <x-icon.plus-2/>
                    <span>{{ __('cms.Toevoegen') }}</span>
                </x-button.cta>
            </div>
        </div>


        <x-grid class="subquestion-grid w-full">

            @foreach($subQuestions as $sub)
                <x-grid.question-card :question="$sub->question->getQuestionInstance()" :testUuid="$this->testId"/>
            @endforeach
        </x-grid>
    </div>
</div>