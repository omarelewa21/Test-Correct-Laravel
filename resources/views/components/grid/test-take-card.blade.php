<div {{ $attributes->merge(['class' => 'grid-card bg-white p-6 rounded-10 card-shadow hover:text-primary cursor-pointer']) }}
     wire:key="test-take-{{ $testTake->uuid }}"
     wire:click="openTestTakeDetail('{{ $testTake->uuid }}')"
     wire:loading.class="hidden"
     wire:target="filters,clearFilters,$set,$toggle"
>
    <div class="flex w-full justify-between mb-2 align-middle">
        <h3 class="line-clamp-2 min-h-[64px] text-inherit @if(blank($testTake->test->name)) italic @endif"
            title="{{ $testTake->test->name }}"
            style="color:inherit"
        >{{ $testTake->test->name ?? __('test.test_name') }}</h3>
        <x-button.options id="test-take-card-{{ $testTake->uuid }}"
                          context="test-take-card"
                          :uuid="$testTake->uuid"
        />
    </div>
    <div class="flex flex-wrap w-full justify-between text-base mb-1">
        <div>
            <span class="bold">{!! $testTake->test->subject->name  !!}</span>
            <span class="italic">{{ trans_choice('cms.vraag', $testTake->test->question_count) }}</span>
            @if($withParticipantStats)
                <span class="cursor-default" title="{{ __('test-take.Studenten aanwezig/afwezig') }}">
                    <span class="text-cta">{{ $participantsTaken }}</span>/{{ $participantsNotTaken }}
                </span>
            @endif
        </div>
        <div class="text-sm">
            <span class="note">{{ __('test-take.Afgenomen op') }}:</span>
            <span class="note">{{ Carbon\Carbon::parse($testTake->time_start)->format('d/m/\'y') }}</span>
        </div>
    </div>
    <div class="flex flex-wrap w-full justify-between text-base">
        <div>
            <span class="">{{ $author }}</span>
        </div>
        <div class="text-sm">
            <span class="note ">{{ $schoolClasses }}</span>

            @if($archived)
                <span class="card-tag grey">{{ __('test-take.Gearchiveerd') }}</span>
            @endif
        </div>
    </div>
</div>
