@extends('components.partials.header.collapsable')

@section('title')
    <h6 class="text-white">@lang($this->headerCollapsed ? 'co-learning.co_learning' : 'co-learning.start_co_learning_session')
        : </h6>
    <h4 class="text-white truncate" title="{!!  clean($testName) !!}">{!!  clean($testName) !!}</h4>
@endsection

@section('subtitle')
    @lang('co-learning.choose_method_subtitle')
@endsection
@section('collapsedLeft')
    <div class="text-right text-[14px] mr-4">
        {{ __('co-learning.questions_being_discussed') }}<br>
        {{ $discussionTypeTranslation }}
    </div>
    <div class="flex items-center">
        <x-button.cta :disabled="!$atLastQuestion"
                      @class(['opacity-40' => !$atLastQuestion])
                      wire:click.prevent="finishCoLearning"
        >
            <span>{{ __('co-learning.complete') }}</span>
            <x-icon.checkmark class="ml-2" />
        </x-button.cta>
    </div>
@endsection

@section('panels')
    <x-partials.header.panel @class([
                "co-learning-restart" => $this->coLearningRestart,
                "co-learning-previous-discussion-type" => !$this->openOnly,
                ])>
        <x-slot:sticker>
            <x-stickers.questions-all />
        </x-slot:sticker>
        <x-slot:title>{{  str(__('co-learning.all_questions'))->ucfirst() }}</x-slot:title>
        <x-slot:subtitle>
            <div>{{ __('co-learning.all_questions_text') }}</div>
            <div class="text-[14px]">{{ __('co-learning.all_questions_note') }}</div>
        </x-slot:subtitle>
        <x-slot:button>
            <x-button.cta size="md"
                          x-on:click.prevent="handleHeaderCollapse({discussionType:'ALL'})">
                <span>
                    @lang($this->coLearningRestart && !$this->openOnly ? 'auth.continue' : 'co-learning.start')
                </span>
                <x-icon.arrow />
            </x-button.cta>
        </x-slot:button>
        <x-slot:additionalInfo>
            @if($this->coLearningRestart)
                @if($this->openOnly)
                    <div class="text-center text-[14px]">
                        {!!  __('co-learning.restart_session') !!}
                    </div>
                @else
                    <div class="text-center text-[14px]">
                        {!!  __('co-learning.current_session', [
                        'index' => $this->questionIndex,
                        'totalQuestions' => $this->questionCount,
                        'date' => $this->testTake->updated_at->format('d/m/Y')
                        ]) !!}
                    </div>
                @endif
            @endif
        </x-slot:additionalInfo>
    </x-partials.header.panel>

    <x-partials.header.panel @class([
                        "co-learning-restart" => $this->coLearningRestart,
                        "co-learning-previous-discussion-type" => $this->openOnly,
                        ])>
        <x-slot:sticker>
            <x-stickers.questions-open-only />
        </x-slot:sticker>
        <x-slot:title>{{ str(__('co-learning.open_questions_only'))->ucfirst() }}</x-slot:title>
        <x-slot:subtitle>
            <div>{{ __('co-learning.open_questions_text') }}</div>
            <div class="text-[14px]">{{ __('co-learning.open_questions_note') }}</div>
        </x-slot:subtitle>
        <x-slot:button>
            <x-button.cta size="md"
                          x-on:click.prevent="handleHeaderCollapse({discussionType:'OPEN_ONLY'})">
                <span>
                    @lang($this->coLearningRestart && $this->openOnly ? 'auth.continue' : 'co-learning.start')
                </span>
                <x-icon.arrow />
            </x-button.cta>
        </x-slot:button>
        <x-slot:additionalInfo>
            @if($this->coLearningRestart)
                @if($this->openOnly)
                    <div class="text-center text-[14px]">
                        {!!  __('co-learning.current_session', [
                        'index' => $this->questionIndexOpenOnly,
                        'totalQuestions' => $this->questionCountFiltered,
                        'date' => $this->testTake->updated_at->format('d/m/Y')
                        ]) !!}
                    </div>
                @else
                    <div class="text-center text-[14px]">
                        {!!  __('co-learning.restart_session') !!}
                    </div>
                @endif
            @endif
        </x-slot:additionalInfo>
    </x-partials.header.panel>
@endsection


    @section('additionalInfo')

        <div @class(['flex justify-center items-center uppercase text-[14px] margin-[0_0_5px] mt-6'])>
            @lang('co-learning.open-question-options')
        </div>
        @if(auth()->user()->schoolLocation->allow_wsc)
        <div @class(["flex flex-col w-3/4 self-center divide-white divide-y border-t border-b border-white ", 'border-b-white/25' => true])>
            <div class="flex py-2 px-4 items-center justify-between">
                <span>@lang('co-learning.spellchecker-for-students')</span>
                <div class="flex items-center gap-4">
                    @if($this->testTake->enable_spellcheck_colearning)
                        <x-input.toggle wire:click="toggleStudentSpellcheck($event.target.checked)" checked />
                    @else
                        <x-input.toggle wire:click="toggleStudentSpellcheck($event.target.checked)"/>
                    @endif

                    <x-tooltip idle-classes="bg-transparent text-white border-white border">
                        <span class="text-left">@lang('co-learning.spellchecker-for-students-tt')</span>
                    </x-tooltip>
                </div>
            </div>
        </div>
        @endif
        <div @class(["flex flex-col w-3/4 self-center divide-white divide-y border-b border-white ", 'border-b-white/25' => true, 'border-t' => !auth()->user()->schoolLocation->allow_wsc])>
            <div class="flex py-2 px-4 items-center justify-between">
                <span>@lang('co-learning.comments-for-students')</span>
                <div class="flex items-center gap-4">
                    @if($this->testTake->enable_comments_colearning)
                        <x-input.toggle wire:click="toggleStudentEnableComments($event.target.checked)" checked />
                    @else
                        <x-input.toggle wire:click="toggleStudentEnableComments($event.target.checked)"/>
                    @endif

                    <x-tooltip idle-classes="bg-transparent text-white border-white border">
                        <span class="text-left">@lang('co-learning.comments-for-students-tt')</span>
                    </x-tooltip>
                </div>
            </div>
        </div>
        <div @class(["flex flex-col w-3/4 self-center divide-white divide-y border-b border-white ", 'border-b-white/25' => true, 'border-t' => !auth()->user()->schoolLocation->allow_wsc])>
            <div class="flex py-2 px-4 items-center justify-between">
                <span>@lang('co-learning.question-text-for-students')</span>
                <div class="flex items-center gap-4">
                    @if($this->testTake->enable_question_text_colearning)
                        <x-input.toggle wire:click="toggleStudentEnableQuestionText($event.target.checked)" checked />
                    @else
                        <x-input.toggle wire:click="toggleStudentEnableQuestionText($event.target.checked)"/>
                    @endif

                    <x-tooltip idle-classes="bg-transparent text-white border-white border">
                        <span class="text-left">@lang('co-learning.question-text-for-students-tt')</span>
                    </x-tooltip>
                </div>
            </div>
        </div>
        <div @class(["flex flex-col w-3/4 self-center divide-white divide-y border-b border-white ", 'border-b-white/25' => false, 'border-t' => !auth()->user()->schoolLocation->allow_wsc])>
            <div class="flex py-2 px-4 items-center justify-between">
                <span>@lang('co-learning.answer-model-for-students')</span>
                <div class="flex items-center gap-4">
                    @if($this->testTake->enable_answer_model_colearning)
                        <x-input.toggle wire:click="toggleStudentEnableAnswerModel($event.target.checked)" checked />
                    @else
                        <x-input.toggle wire:click="toggleStudentEnableAnswerModel($event.target.checked)"/>
                    @endif

                    <x-tooltip idle-classes="bg-transparent text-white border-white border">
                        <span class="text-left">@lang('co-learning.answer-model-for-students-tt')</span>
                    </x-tooltip>
                </div>
            </div>
        </div>
        {{-- TODO: Re-enable this after deployment 28-9-23, also swap border-b-white/25 above back to true --}}
{{--        <div @class(["flex flex-col w-3/4 self-center divide-white divide-y border-b border-white ", 'border-b-white/25' => false, 'border-t' => !auth()->user()->schoolLocation->allow_wsc])>--}}
{{--            <div class="flex py-2 px-4 items-center justify-between">--}}
{{--                <span>@lang('co-learning.navigation-for-students')</span>--}}
{{--                <div class="flex items-center gap-4">--}}
{{--                    @if($this->testTake->enable_student_navigation_colearning)--}}
{{--                        <x-input.toggle wire:click="toggleStudentEnableNavigation($event.target.checked)" checked />--}}
{{--                    @else--}}
{{--                        <x-input.toggle wire:click="toggleStudentEnableNavigation($event.target.checked)"/>--}}
{{--                    @endif--}}

{{--                    <x-tooltip idle-classes="bg-transparent text-white border-white border">--}}
{{--                        <span class="text-left">@lang('co-learning.navigation-for-students-tt')</span>--}}
{{--                    </x-tooltip>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
    @endsection
