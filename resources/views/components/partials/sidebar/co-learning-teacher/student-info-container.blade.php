<div class="flex mx-6 items-center h-10 justify-between flex-shrink-0"
     id="student-info-container"
>
    @php
        $i = rand(0,4);
    @endphp
    {{-- left --}}
    <div class="flex items-center h-full space-x-1">
        <span class="min-w-[1rem] w-4 flex items-center justify-center">
            @switch($this->testParticipantStatusses[$testParticipant->uuid]['ratingStatus'])
                @case(\tcCore\Http\Enums\CoLearning\RatingStatus::Green)
                    <x-icon.checkmark-small class="text-cta"/>
                    @break
                @case(\tcCore\Http\Enums\CoLearning\RatingStatus::Orange)
                    <x-icon.time-dispensation class="text-orange"/>
                    @break
                @case(\tcCore\Http\Enums\CoLearning\RatingStatus::Red)
                    <x-icon.warning class="text-allred"/>
                    @break
                @default
                    <x-icon.time-dispensation class="text-midgrey"/>
                    @break
            @endswitch

        </span>
        <span class="min-w-[1rem] w-4 flex items-center justify-center">

            @switch($this->testParticipantStatusses[$testParticipant->uuid]['abnormalitiesStatus'])
                @case(\tcCore\Http\Enums\CoLearning\AbnormalitiesStatus::Happy)
                    <x-icon.smiley-happy />
                    @break
                @case(\tcCore\Http\Enums\CoLearning\AbnormalitiesStatus::Neutral)
                    <x-icon.smiley-normal />
                    @break
                @case(\tcCore\Http\Enums\CoLearning\AbnormalitiesStatus::Sad)
                    <x-icon.smiley-sad />
                    @break
                @default
                    <x-icon.smiley-default class="text-midgrey"/>
                    @break
            @endswitch

        </span>
        <span class="student-name">{{$userFullName}}</span>
    </div>
    {{-- right --}}
    <div class="show-on-smartboard relative" @click="showStudentAnswer = true" wire:click.prevent="showStudentAnswer('{{ $testParticipant->discussing_answer_rating_id }}')">
        <x-icon.on-smartboard-show />
    </div>
</div>