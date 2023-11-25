<div @class([
    'student-info-container flex px-6 items-center h-10 justify-between flex-shrink-0 cursor-pointer',
    ])
     x-bind:class="{active: {{ (string) !is_null($activeAnswerRating) ? $testParticipant->discussing_answer_rating_id === $activeAnswerRating->id : 'false'}}}"
     id="student-info-container-{{$testParticipant->uuid}}"
     wire:key="testParticipant-{{$testParticipant->uuid . $this->discussingQuestion->id }}"
     x-data="{
        tooltip: document.querySelector('[x-ref=\'{{ 'tt-'.$testParticipant->uuid }}\']')
     }"
>
    {{-- left --}}
    <div class="content | flex relative items-center h-full w-full">
        <div class="flex items-center h-full space-x-1"
             x-on:mouseenter="showToolTip(tooltip)"
             x-on:mouseleave="hideToolTip(tooltip)"
             x-on:mousemove="setPositionToolTip(tooltip, $event)"
        >
            <span class="min-w-[1rem] w-4 flex items-center justify-center">
                @switch($this->testParticipantStatusses[$testParticipant->uuid]['ratingStatus'])
                    @case(\tcCore\Http\Enums\CoLearning\RatingStatus::Green)
                        <x-icon.checkmark-small class="text-cta" />
                        @break
                    @case(\tcCore\Http\Enums\CoLearning\RatingStatus::Orange)
                        <x-icon.time-dispensation class="text-orange" />
                        @break
                    @case(\tcCore\Http\Enums\CoLearning\RatingStatus::Red)
                        <x-icon.warning class="text-allred" />
                        @break
                    @default
                        <x-icon.time-dispensation class="text-midgrey" />
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
                        <x-icon.smiley-default class="text-midgrey" />
                        @break
                @endswitch

            </span>
            <span class="student-name">{{$userFullName}}</span>
        </div>
        {{-- right --}}
        <button
                @click="openStudentAnswer('{{ $testParticipant->discussing_answer_rating_id }}')"
                @class([
                   'show-on-smartboard',
                   'absolute -right-1 text-sysbase hover:text-primary',
                   'disabled' => $smartboardButtonDisabled,
                ])
                @disabled($smartboardButtonDisabled)
                @if($smartboardButtonDisabled && $testParticipant->discussing_question_id !== $testParticipant->testTake->discussing_question_id)
                    title="@lang('co-learning.student-at-different-question')"
                @else
                    title="@lang($smartboardButtonActive ? 'co-learning.hide-from-smartboard' :'co-learning.show-on-smartboard')"
                @endif
        >
            <x-icon.on-smartboard-show />
        </button>
    </div>
    <div class="co-learning-tooltip"
         x-ref="{{ 'tt-'.$testParticipant->uuid }}"
    >
        <div class="flex items-center space-x-2">
            @switch($this->testParticipantStatusses[$testParticipant->uuid]['ratingStatus'])
                @case(\tcCore\Http\Enums\CoLearning\RatingStatus::Green)
                    <x-icon.checkmark-small class="text-cta" />
                    @break
                @case(\tcCore\Http\Enums\CoLearning\RatingStatus::Orange)
                    <x-icon.time-dispensation class="text-orange" />
                    @break
                @case(\tcCore\Http\Enums\CoLearning\RatingStatus::Red)
                    <x-icon.warning class="text-allred" />
                    @break
                @default
                    <x-icon.time-dispensation class="text-midgrey" />
                    @break
            @endswitch
            <div class="inline-flex items-center">
                <span class="bold">{{ $testParticipant->answer_rated }}</span>
                <span class="text-[14px]">/{{ $testParticipant->answer_to_rate }} {{ __('co-learning.rated_answers') }}</span>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            @switch($this->testParticipantStatusses[$testParticipant->uuid]['abnormalitiesStatus'])
                @case(\tcCore\Http\Enums\CoLearning\AbnormalitiesStatus::Happy)
                    <x-icon.smiley-happy />
                    <span class="text-[14px]">{{__('co-learning.good_rater')}}</span>
                    @break
                @case(\tcCore\Http\Enums\CoLearning\AbnormalitiesStatus::Neutral)
                    <x-icon.smiley-normal />
                    <span class="text-[14px]">{{__('co-learning.average_rater')}}</span>
                    @break
                @case(\tcCore\Http\Enums\CoLearning\AbnormalitiesStatus::Sad)
                    <x-icon.smiley-sad />
                    <span class="text-[14px]">{{__('co-learning.bad_rater')}}</span>
                    @break
                @default
                    <x-icon.smiley-default class="text-midgrey" />
                    <span class="text-[14px] text-midgrey">{{__('co-learning.unknown_rater')}}</span>
                    @break
            @endswitch
        </div>
    </div>
</div>