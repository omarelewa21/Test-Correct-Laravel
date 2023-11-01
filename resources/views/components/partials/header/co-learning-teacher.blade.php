@extends('components.partials.header.collapsable')

@section('title')
    <h6 class="text-white">@lang($this->headerCollapsed ? 'co-learning.co_learning' : 'co-learning.start_co_learning_session')
        : </h6>
    <h4 class="text-white truncate" title="{!!  clean($testName) !!}">{!!  clean($testName) !!}</h4>
@endsection

@section('subtitle')
    {{-- TODO remove section? --}}
    @lang('co-learning.choose_method_subtitle')
@endsection

@section('collapsedLeft')
{{--    <div class="text-right text-[14px] mr-4">--}}
{{--        {{ __('co-learning.questions_being_discussed') }}<br>--}}
{{--        {{ $discussionTypeTranslation }}--}}
{{--    </div>--}}
    <div class="flex items-center">
        <x-button.cta :disabled="!$atLastQuestion"
                      @class(['opacity-40' => !$atLastQuestion])
                      wire:click.prevent="finishCoLearning"
        >
            <span>{{ __('co-learning.complete') }}</span>
            <x-icon.checkmark class="ml-2"/>
        </x-button.cta>
    </div>
@endsection

@section('panels')
    {{-- TODO remove section? --}}
@endsection

@section('additionalInfo')
    {{-- Todo remove section?  --}}
@endsection

@section('set-up-colearning')
    @unless($this->coLearningHasBeenStarted)
    <div class="set-up-colearning" x-data="coLearningSetup()">
        <div class="set-up-subtitle">
            @if($this->setupStep === 1)
                <h3 class="text-center text-white">
                    @lang('co-learning.set-up-colearning-session')
                </h3>
                <div class="flex space-x-4">
                    <x-tooltip idle-classes="bg-transparent text-white border-white border">
                        {{-- TODO this translation is not correct yet--}}
                        <span class="text-left">@lang('assessment.continuously_saved_tooltip')</span>
                    </x-tooltip>
                </div>
            @else
                <h3 class="text-center text-white">
                    @lang ('co-learning.choose_questions_title')
                </h3>
                <div class="flex space-x-4 items-center"
                     x-on:multi-slider-toggle-value-updated.window=""
                >
                    <x-button.slider initial-status="{{$this->setupQuestionSelector}}"
                                     buttonWidth="auto"
                                     :white="true"
                                     :allowClickingCurrentValue="true"
                                     :options="[ 'all' => ucfirst(__('co-learning.all_questions')), 'open' => __('co-learning.open_questions_only')]"
                    >

                    </x-button.slider>
                    <x-tooltip idle-classes="bg-transparent text-white border-white border">
                        {{-- TODO this translation is not correct yet--}}
                        <span class="text-left">@lang('assessment.continuously_saved_tooltip')</span>
                    </x-tooltip>
                </div>
            @endif
        </div>


        <div class="set-up-colearning-panel" >
            <div>
                @if($this->setupStep === 1)
                    <div class="step-one-illustration">
                        <x-backgrounds.triangles-colored class="w-full"></x-backgrounds.triangles-colored>
                        <x-illustrations.better-learning-no-triangle-bg></x-illustrations.better-learning-no-triangle-bg>
                    </div>
                    <div class="step-one-content ">
                        <div class="step-one-options">

                            <x-input.toggle-row-with-title
                                    :title="__('co-learning.allow-browser-access')"
                                    :toolTip="__('co-learning.allow-browser-access-tt')"
                                    :checked="$this->testTake->allow_inbrowser_colearning"
                                    wire:click="toggleStudentAllowBrowserAccess($event.target.checked)"
                            >
                                <x-icon.web/>
                                <span>@lang('co-learning.allow-browser-access')</span>
                            </x-input.toggle-row-with-title>

                            @if(auth()->user()->schoolLocation->allow_wsc)
                                <x-input.toggle-row-with-title
                                        :title="__('co-learning.spellchecker-for-students')"
                                        :toolTip="__('co-learning.spellchecker-for-students-tt')"
                                        :checked="$this->testTake->enable_spellcheck_colearning"
                                        wire:click="toggleStudentSpellcheck($event.target.checked)"
                                >
                                    <x-icon.spellcheck/>
                                    <span>@lang('co-learning.spellchecker-for-students')</span>
                                </x-input.toggle-row-with-title>
                            @endif
                            <x-input.toggle-row-with-title
                                    :title="__('co-learning.comments-for-students')"
                                    :toolTip="__('co-learning.comments-for-students-tt')"
                                    :checked="$this->testTake->enable_comments_colearning"
                                    wire:click="toggleStudentEnableComments($event.target.checked)"
                            >
                                <x-icon.feedback-text/>
                                <span>@lang('co-learning.comments-for-students')</span>
                            </x-input.toggle-row-with-title>

                            <x-input.toggle-row-with-title
                                    :title="__('co-learning.question-text-for-students')"
                                    :toolTip="__('co-learning.question-text-for-students-tt')"
                                    :checked="$this->testTake->enable_question_text_colearning"
                                    wire:click="toggleStudentEnableQuestionText($event.target.checked)"
                            >
                                <x-icon.preview/>
                                <span>@lang('co-learning.question-text-for-students')</span>
                            </x-input.toggle-row-with-title>


                            <x-input.toggle-row-with-title
                                    :title="__('co-learning.answer-model-for-students')"
                                    :toolTip="__('co-learning.answer-model-for-students-tt')"
                                    :checked="$this->testTake->enable_answer_model_colearning"
                                    wire:click="toggleStudentEnableAnswerModel($event.target.checked)"
                                    indented="true"
                            >
                                {{-- TODO create indent --}}
                                <span>@lang('co-learning.answer-model-for-students')</span>
                            </x-input.toggle-row-with-title>

                            <x-input.toggle-row-with-title
                                    :title="__('co-learning.navigation-for-students')"
                                    :toolTip="__('co-learning.navigation-for-students-tt')"
                                    :checked="$this->testTake->enable_student_navigation_colearning"
                                    wire:click="toggleStudentEnableNavigation($event.target.checked)"
                                    indented="true"
                            >
                                {{-- TODO create indent --}}
                                <span>@lang('co-learning.navigation-for-students')</span>
                            </x-input.toggle-row-with-title>

                            <span class="text-note">
                                @if($this->coLearningRestart)
                                    {{-- TODO add text x/x questions, on date ... --}}
                                    {!!  __('co-learning.current_session', [
                                        'index' => $this->discussedQuestionsCount,
                                        'totalQuestions' => $this->questionCount,
                                        'date' => $this->testTake->updated_at->format('d/m/Y')
                                    ]) !!}
                                @else
                                    @lang('co-learning.real-time-saving')
                                @endif
                            </span>


                                {{--{!!  __('co-learning.current_session', [
                                'index' => $this->questionIndex,
                                'totalQuestions' => $this->questionCount,
                                'date' => $this->testTake->updated_at->format('d/m/Y')
                                ]) !!}

                                {!!  __('co-learning.current_session', [
                                'index' => $this->questionIndexOpenOnly,
                                'index' => $this->questionIndexOpenOnly,
                                'totalQuestions' => $this->questionCountFiltered,
                                'date' => $this->testTake->updated_at->format('d/m/Y')
                                ]) !!}--}}

                        </div>

                    </div>

                @else

                    {{--  TODO make step 2 table / grid --}}

                    <div class="step-two">
                        {{--start step two--}}
                        <div @class([
                                "co-learning-setup-grid px-10 !absolute w-full bg-white z-10",
                                "with-group-questions" => $this->testHasGroupQuestions,
                            ])>
                            {{-- checkbox --}}
                            <div class="grid-title w-6">&nbsp;</div>
                            {{-- # nr --}}
                            <div class="grid-title space-x-1">
                                <x-button.text wire:click.stop="changeSetupQuestionsSorting('test_index')"
                                               size="sm"
                                        @class([
                                            'group/button',
                                            'rotate-svg-270' => $this->getDirectionOfSortField('test_index') === 'desc',
                                            'rotate-svg-90' => $this->getDirectionOfSortField('test_index') !== 'desc',
                                            'active' => !is_null($this->getDirectionOfSortField('test_index'))
                                        ])
                                >
                                    <span>#</span>
                                    <x-icon.chevron-small opacity="1"
                                                          class="transform transition-all ease-in-out duration-100 group-hover/button:opacity-100 "
                                    />
                                </x-button.text>
                            </div>
                            {{-- Vraagtype --}}
                            <div class="grid-title space-x-1">
                                <x-button.text
                                        wire:click.stop="changeSetupQuestionsSorting('question_type_name')"
                                        size="sm"
                                        @class([
                                            'group/button',
                                            'rotate-svg-270' => $this->getDirectionOfSortField('question_type_name') === 'desc',
                                            'rotate-svg-90' => $this->getDirectionOfSortField('question_type_name') !== 'desc',
                                            'active' => !is_null($this->getDirectionOfSortField('question_type_name'))
                                        ])
                                >
                                    <span>@lang('test-take.Vraagtype')</span>
                                    <x-icon.chevron-small opacity="1"
                                                          class="transform transition-all ease-in-out duration-100 group-hover/button:opacity-100 "
                                    />
                                </x-button.text>
                            </div>
                            @if($this->testHasGroupQuestions)
                                {{-- (optional) Group --}}
                                <div class="grid-title justify-center w-[55px]">@lang('question.groupquestion')</div>
                            @endif
                            {{-- Voorbeeld --}}
                            <div class="grid-title">@lang('cms.voorbeeld')</div>
                            {{-- P waarde --}}
                            <div class="grid-title text-right justify-self-end whitespace-nowrap overflow-visible space-x-1"
                                 style="direction: rtl"
                            >
                                <x-button.text
                                        class="group/button {{ $this->getDirectionOfSortField('p_value') === 'desc' ? 'rotate-svg-270' : 'rotate-svg-90'  }}"
                                        wire:click.stop="changeSetupQuestionsSorting('p_value')"
                                        size="sm"
                                        @class([
                                            'group/button',
                                            'rotate-svg-270' => $this->getDirectionOfSortField('p_value') === 'desc',
                                            'rotate-svg-90' => $this->getDirectionOfSortField('p_value') !== 'desc',
                                            'active' => !is_null($this->getDirectionOfSortField('p_value'))
                                        ])
                                >
                                    <x-icon.chevron-small opacity="1"
                                                          class="transform transition-all ml-1 ease-in-out duration-100 group-hover/button:opacity-100 "
                                    />
                                    <span>@lang('cms.p-waarde')</span>
                                </x-button.text>
                            </div>

                            <div class="col-span-6 h-[2px] bg-sysbase"></div>
                        </div>
                        <div class="flex flex-col w-full relative mt-[45px]"
                             x-data="{rowHover: null, shadow: null}"
                             x-init="
                                     shadow = $refs.shadowBox
                                     $watch('rowHover', value => {
                                        if(value !== null) {
                                            shadow.style.top = $root.querySelector(`[data-row='${value}'] .grid-item`)?.offsetTop + 'px'
                                        }
                                     })"
                             wire:key="{{ $this->setUpWireKey }}"
{{--                             wire:key="step2-{{ $this->setupCoLearningSortField .'-'. $this->setupCoLearningSortDirection }}"--}}
                             wire:ignore.self
                        >
                            <div x-ref="shadowBox"
                                 x-bind:class="{'hidden': rowHover === null}"
                                 class="shadow-box "
                                 wire:ignore
                            >
                                <span></span>
                            </div>


                            <div @class([
                                "co-learning-setup-grid mx-10",
                                "with-group-questions" => $this->testHasGroupQuestions,
                            ])>
                                @foreach($this->getSetupData() as $question)
                                    <div @class([
                                             "grid-row contents group/row cursor-default",
                                             "hover:text-primary hover:shadow-lg" => !$question['disabled'],
                                             "disabled note" => $question['disabled'],
                                         ])
                                         x-on:mouseover="rowHover = $el.dataset.row"
                                         x-on:mouseout="rowHover = null"
                                         data-row="{{ $loop->iteration }}"
                                    >
                                        {{-- checkbox --}}
                                        <div @class([
                                                  "grid-item col-start-1 flex items-center pr-4 rounded-r-10",
                                                  "checkbox-disabled" => false
                                              ])
                                        >
                                            @unless($question['discussed'])
                                                @if($question['disabled'])
                                                    <x-input.checkbox :disabled="true"
                                                    />
                                                @else
                                                    <x-input.checkbox :checked="$question['checked']"
                                                                      wire:click="toggleQuestionChecked('{{$question['question_uuid']}}', {{ (string)!$question['checked'] }})"
                                                    />
                                                @endif
                                            @else
                                                <x-icon.checkmark-circle width="24px" color="var(--cta-primary)"/>
                                            @endif
                                        </div>
                                        {{-- # nr --}}
                                        <div class="grid-item flex items-center pr-4 h-15 rounded-l-10">
                                            <span> {{  $question['test_index'] }} </span>
                                        </div>
                                        {{-- Vraagtype --}}
                                        <div class="grid-item flex items-center pr-4 ">
                                            <span> {{  $question['question_type_name'] }} </span>
                                        </div>
                                        @if($this->testHasGroupQuestions)
                                            {{-- (optional) Group --}}
                                            <div class="grid-item flex items-center justify-center pr-4 ">
                                                @if($question['group_question_id'])
                                                    <span @class(["border group-badge", "group-badge-disabled" => $question['disabled']])
                                                          title="{{  $question['group_question_id'] }}"
                                                    >{{ $question['group_number'] }}</span>
                                                @endif
                                            </div>
                                        @endif
                                        {{-- Voorbeeld --}}
                                        <div class="grid-item flex items-center pr-4 truncate justify-start gap-1.5">

                                            <x-button.text
                                                    size="md"
                                                    :disabled="$question['disabled']"
                                                    wire:click="$emit('openModal', 'teacher.question-cms-preview-modal', {{ json_encode(['uuid' => $question['question_uuid']]) }});"
                                            >
                                                <x-icon.preview class="" />
                                            </x-button.text>
                                            <span class="truncate" title="a"> {{  $question['question_title'] }} </span>
                                        </div>
                                        {{-- P waarde --}}
                                        <div class="grid-item flex items-center justify-end">
                                            <span> {{  $question['p_value'] ? ($question['p_value'] * 100) . '%' : '-' }} </span>
                                        </div>

                                    </div>

                                    @if(!$loop->last)
                                        <div class="h-px bg-bluegrey rounded-10 col-span-6 col-start-1"></div>
                                    @endif

                                @endforeach


                            </div>

                            {{--@if($errors->has('all_questions_ignored'))
                                <div class="flex w-full justify-end mt-4">
                                    <div class="notification error stretched">
                                        <div class="body bold">{{  $errors->get('all_questions_ignored')[0] }}</div>
                                    </div>
                                </div>
                            @endif--}}
                        </div>

                        {{--end step two--}}
                    </div>



                @endif

                <div class="set-up-shadow-footer"></div>
            </div>


        </div>

        <div @class([
            "set-up-navigation",
            "justify-end" => $this->setupStep === 1,
            "justify-between" => $this->setupStep === 2,
        ])>


            @if($this->setupStep === 1)
                <x-button.text white="true"
                               wire:click="nextSetupStep()"
                >
                    <span>@lang('auth.next_step')</span>
                    <x-icon.arrow/>
                </x-button.text>
            @else
                <x-button.text white="true"
                               wire:click="previousSetupStep()"
                               rotateIcon="180"
                >
                    <x-icon.arrow/>
                    <span>@lang('co-learning.previous_step')</span>
                </x-button.text>
                <div class="space-x-4">
                    <span class="text-[14px]">
                        <b class="text-[16px]">{{ $this->setupCheckedQuestionsCount }}</b>/{{ $this->setupQuestionTotalCount }} {{__('co-learning.questions_to_discuss')}}
                    </span>
                    <x-button.cta
                            x-on:click.prevent.stop="handleHeaderCollapse({'discussionType': 'all'})"
                            :disabled="$this->setupCheckedQuestionsCount === 0"
                    >
                        <span>@lang('co-learning.start')</span>
                    </x-button.cta>
                </div>

            @endif


            <div @class(["navigation-dots", "flex-row-reverse" => $this->setupStep === 2])>
                <div class="navigation-dot-full"></div>
                <div class="navigation-dot-open"
                     wire:click="{{$this->setupStep === 1 ? 'nextSetupStep' : 'previousSetupStep' }}"
                ></div>
            </div>
        </div>
    </div>
    @endif
@endsection


