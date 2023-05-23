@extends('components.partials.header.collapsable')

@section('title')
    <h6 class="text-white">@lang($this->headerCollapsed ? 'assessment.Nakijken' : 'assessment.Start nakijken'): </h6>
    <h4 class="text-white truncate" title="{!!  clean($testName) !!}">{!!  clean($testName) !!}</h4>
@endsection

@section('subtitle')
    @lang('assessment.Kies je nakijkmethode')
@endsection

@section('collapsedLeft')
    <div class="flex items-center gap-6">
        <x-assessment-navigator id="question-navigator"
                                :current="$this->questionNavigationValue"
                                :total="$this->questionCount"
                                methodCall="loadQuestion"
                                :last="$this->lastQuestionForStudent"
                                :first="$this->firstQuestionForStudent"
        />

        <x-assessment-navigator id="answer-navigator"
                                :current="$this->answerNavigationValue"
                                :total="$this->studentCount"
                                methodCall="loadAnswer"
                                iconName="profile"
                                :last="$this->lastAnswerForQuestion"
                                :first="$this->firstAnswerForQuestion"
        />
    </div>
@endsection
@section('panels')
    <x-partials.header.panel @class([
                "co-learning-restart" => $this->assessmentContext['assessmentType'],
                "co-learning-previous-discussion-type" => !$this->openOnly,
                ])>
        <x-slot:sticker>
            <x-stickers.questions-all />
        </x-slot:sticker>
        <x-slot:title>{{  str(__('co-learning.all_questions'))->ucfirst() }}</x-slot:title>
        <x-slot:subtitle>
            <div>{{ __('assessment.all_questions_text') }}</div>
        </x-slot:subtitle>
        <x-slot:button>
            <x-button.cta size="md"
                          x-on:click.prevent="handleHeaderCollapse(['ALL', {{ ($this->assessmentContext['assessmentType'] && $this->openOnly) ? 'true' : 'false' }} ])"
            >
                <span>@lang($this->assessmentContext['assessmentType'] && !$this->openOnly ? 'auth.continue' : 'co-learning.start')</span>
                <x-icon.arrow />
            </x-button.cta>
        </x-slot:button>
        <x-slot:additionalInfo>
            @if($this->assessmentContext['assessmentType'])
                @unless($this->openOnly)
                    <div class="text-center text-[14px]">
                        {!!  __('assessment.current_session', [
                        'index' => $this->assessmentContext['assessIndex'],
                        'totalQuestions' => $this->assessmentContext['totalToAssess'],
                        'date' => $this->assessmentContext['assessedAt']
                        ]) !!}
                    </div>
                @endif
            @endif
        </x-slot:additionalInfo>
    </x-partials.header.panel>

    <x-partials.header.panel @class([
                "co-learning-restart" => $this->assessmentContext['assessmentType'],
                "co-learning-previous-discussion-type" => $this->openOnly,
                'disabled' => $this->hasNoOpenQuestion,
                ])>
        <x-slot:sticker>
            <x-stickers.questions-open-only />
        </x-slot:sticker>
        <x-slot:title>{{ str(__('co-learning.open_questions_only'))->ucfirst() }}</x-slot:title>
        <x-slot:subtitle>
            <span>@lang('assessment.open_questions_text')</span>
            @if($this->hasNoOpenQuestion)
                <span class="text-sm text-white/90">@lang('assessment.Er zitten geen open vragen in deze toets.')</span>
            @endif
        </x-slot:subtitle>
        <x-slot:button>
            <x-button.cta size="md"
                          x-on:click.prevent="handleHeaderCollapse(['OPEN_ONLY', {{ ($this->assessmentContext['assessmentType'] && !$this->openOnly) ? 'true' : 'false' }}])"
                          :disabled="$this->hasNoOpenQuestion"
            >
                <span>@lang($this->assessmentContext['assessmentType'] && $this->openOnly ? 'auth.continue' : 'co-learning.start')</span>
                <x-icon.arrow />
            </x-button.cta>
        </x-slot:button>
        <x-slot:additionalInfo>
            @if($this->assessmentContext['assessmentType'])
                @if($this->openOnly)
                    <div class="text-center text-[14px]">
                        {!!  __('assessment.current_session', [
                        'index' => $this->assessmentContext['assessIndex'],
                        'totalQuestions' => $this->assessmentContext['totalToAssess'],
                        'date' => $this->assessmentContext['assessedAt']
                        ]) !!}
                    </div>
                @endif
            @endif
        </x-slot:additionalInfo>
    </x-partials.header.panel>
@endsection()

@section('additionalInfo')
    <div @class(["flex flex-col w-3/4 self-center divide-white divide-y border-t border-b border-white mt-6"])>
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
            <span @class(['opacity-25 pointer-events-none' => $this->assessmentContext['skippedCoLearning']])>@lang('assessment.Score wordt overgenomen uit CO-Learning')</span>
            <x-tooltip @class(['opacity-25 pointer-events-none' => $this->assessmentContext['skippedCoLearning']])
                       idle-classes="bg-transparent text-white border-white border">
                <span class="text-left">@lang('assessment.colearning_score_tooltip')</span>
            </x-tooltip>
        </div>
        <div @class(['flex py-1.5 px-4 items-center justify-between', 'opacity-25 pointer-events-none' => $this->assessmentContext['skippedCoLearning']])>
            <span>@lang('assessment.Antwoorden zonder discrepanties overslaan')</span>
            <div class="flex items-center gap-4">
                <x-input.toggle wire:model="assessmentContext.skipCoLearningNoDiscrepancies" />
                <x-tooltip idle-classes="bg-transparent text-white border-white border">
                    <span class="text-left">@lang('assessment.discrepancies_toggle_tooltip')</span>
                </x-tooltip>
            </div>
        </div>
        <div @class(['flex py-1.5 px-4 items-center justify-between'])>
            <span>@lang('assessment.Toon de naam van studenten')</span>
            <div class="flex items-center gap-4">
                <x-input.toggle wire:model="assessmentContext.showStudentNames" />
                <x-tooltip idle-classes="bg-transparent text-white border-white border">
                    <span class="text-left">@lang('assessment.show_student_tooltip_text')</span>
                </x-tooltip>
            </div>
        </div>
    </div>
@endsection
