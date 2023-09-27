<div class="fixed max-w-md w-max bg-off-white rounded-10 p-6 main-shadow z-[5] flex text-sysbase cursor-default"
     x-data="participantDetailPopup()"
     x-show="participantPopupOpen"
     x-cloak
     x-on:open-participant-popup.window="openPopup($event.detail)"
     x-on:click.outside="closePopup"
     x-on:keydown.escape.window="closePopup"
     x-on:scroll.window="handleScroll()"
     x-transition:enter="transition ease-out origin-bottom duration-200"
     x-transition:enter-start="opacity-0 transform scale-90"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition origin-bottom ease-in duration-100"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-90"
     wire:ignore.self
>
    @if($this->testParticipant)
        <div class="grid grid-cols-[auto_auto] gap-2">
            <div class="bold">@lang('test-take.Cijfer voor deze toets'):</div>
            <div>{{ $testParticipant->rating ?? '-' }}</div>

            <div class="bold">@lang('test-take.Cijfer voor dit vak'):</div>
            <div>{{ $testParticipant->user->averageRatings->first()?->rating ?? '-'}}</div>

            <div class="bold">@lang('test-take.Tijd totaal'):</div>
            <div>{{ \Carbon\CarbonInterval::second($testParticipant->total_time)->cascade()->forHumans() }}</div>

            <div class="bold">@lang('test-take.Tijd per vraag'):</div>
            <div>{{ \Carbon\CarbonInterval::second($testParticipant->total_time / $testParticipant->questions)->cascade()->forHumans() }}</div>

            <div class="bold">@lang('test-take.Duurde het langst'):</div>
            <div title="{{ $testParticipant->longest_answer?->question?->title }}"
                 class="overflow-hidden text-ellipsis"
            >
                {{ $testParticipant->longest_answer?->question?->title ?? __('general.unavailable') }}
            </div>
            @if($testParticipant->invigilator_note)
                <div class="col-span-2 bold mt-2">@lang('test-take.Notitie van surveillant'):</div>

                <div class="max-w-full col-span-2">
                    {{ $testParticipant->invigilator_note }}
                </div>

            @endif
        </div>
    @endif
</div>
