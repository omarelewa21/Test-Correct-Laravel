@extends('components.partials.header.collapsable')

@section('title')
    <h6 class="text-white">@lang('assessment.Start nakijken'): </h6>
    <h4 class="text-white truncate">{!!  clean($testName) !!}</h4>
@endsection

@section('subtitle')
    @lang('assessment.Kies je nakijkmethode')
@endsection

@section('collapsedLeft')
    <div class="flex items-center gap-6">
        <x-numeric-navigator :current="1"
                             :total="$this->questionCount"
                             property="answer"
                             methodCall="loadAnswer"
        />

        <x-numeric-navigator :current="1"
                             :total="$this->studentCount"
                             property="student"
                             methodCall="loadStudent"
                             iconName="chevron"
        />
    </div>
@endsection
@section('panels')
    <x-partials.header.panel>
        <x-slot:sticker>
            <x-stickers.questions-all />
        </x-slot:sticker>
        <x-slot:title>{{  str(__('co-learning.all_questions'))->ucfirst() }}</x-slot:title>
        <x-slot:subtitle>
            <div>{{ __('assessment.all_questions_text') }}</div>
        </x-slot:subtitle>
        <x-slot:button>
            <x-button.cta size="md"
                          @click.prevent="handleHeaderCollapse(['ALL',true])"
            >
                <span>{{ __('co-learning.start') }}</span>
                <x-icon.arrow />
            </x-button.cta>
        </x-slot:button>
    </x-partials.header.panel>

    <x-partials.header.panel>
        <x-slot:sticker>
            <x-stickers.questions-open-only />
        </x-slot:sticker>
        <x-slot:title>{{ str(__('co-learning.open_questions_only'))->ucfirst() }}</x-slot:title>
        <x-slot:subtitle>{{ __('assessment.open_questions_text') }}</x-slot:subtitle>
        <x-slot:button>
            <x-button.cta size="md"
                          @click.prevent="handleHeaderCollapse(['OPEN_ONLY',true])"
            >
                <span>{{ __('co-learning.start') }}</span>
                <x-icon.arrow />
            </x-button.cta>
        </x-slot:button>
    </x-partials.header.panel>
@endsection()

@section('additionalInfo')
    <div @class(["flex flex-col w-3/4 self-center divide-white divide-y border-t border-b border-white mt-6", 'border-b-white/25' => $this->skippedCoLearning])>
        <div class="flex py-2 px-4 items-center justify-between">
            <span>@lang('assessment.Alles wordt tussentijds opgeslagen')</span>
            <x-tooltip idle-classes="bg-transparent text-white border-white border">
                <span class="text-left">@lang('assessment.continuously_saved_tooltip')</span>
            </x-tooltip>
        </div>
        <div class="flex py-2 px-4 items-center justify-between">
            <span>@lang('assessment.Gesloten vragen worden automatisch nagekeken')</span>
            <x-tooltip idle-classes="bg-transparent text-white border-white border">
                <span class="text-left">@lang('assessment.closed_question_checked_tooltip')</span>
            </x-tooltip>
        </div>
        <div class="flex py-2 px-4 items-center justify-between">
            <span @class(['opacity-25 pointer-events-none' => $this->skippedCoLearning])>@lang('assessment.Score wordt overgenomen uit CO-Learning')</span>
            <x-tooltip @class(['opacity-25 pointer-events-none' => $this->skippedCoLearning])
                       idle-classes="bg-transparent text-white border-white border">
                <span class="text-left">@lang('assessment.colearning_score_tooltip')</span>
            </x-tooltip>
        </div>
        <div @class(['flex py-1.5 px-4 items-center justify-between', 'opacity-25 pointer-events-none' => $this->skippedCoLearning])>
            <span>@lang('assessment.Antwoorden zonder discrepanties overslaan')</span>
            <div class="flex items-center gap-4">
                <x-input.toggle wire:model="skipCoLearningDiscrepancies" />
                <x-tooltip idle-classes="bg-transparent text-white border-white border">
                    <span class="text-left">@lang('assessment.discrepancies_toggle_tooltip')</span>
                </x-tooltip>
            </div>
        </div>
    </div>
@endsection
