<div id="co-learning-teacher-page"
     class="flex w-full relative" style="z-index: 1; min-height: 100vh"
     x-data="{
     showStudentAnswer: false,
     showAnswerModel: false,
     showQuestion: true,
     activeStudentAnswer: null,
     resetToggles() {
        this.showStudentAnswer = false;
        this.showAnswerModel = false;
        this.showQuestion = true;
     },
     async openStudentAnswer(id) {
        this.activeStudentAnswer = id;
        result = await $wire.call('showStudentAnswer',id);
        this.showStudentAnswer = result === true;
        $dispatch('accordion-toggled')
     },
     }"
>
    <x-partials.header.co-learning-teacher testName="{{ $this->testTake->test->name ?? '' }}"
                                           discussionType="{{ $this->testTake->discussion_type }}"
                                           :atLastQuestion="$this->atLastQuestion"
    />
    @if($coLearningHasBeenStarted)
        <x-partials.sidebar.co-learning-teacher.drawer
                wire:key="drawer--{{ now()->timestamp }}"
                :activeAnswerRating="$this->activeAnswerRating"
        />

        <div id="main-content-container"
             class="flex border-2 relative w-full justify-between overflow-auto "
                             wire:poll.keep-alive.5000ms="render()"
        >

            <div class="flex flex-col w-full space-y-4 pt-10 px-[60px] pb-14"
                 wire:key="container-{{$this->testTake->discussing_question_id}}"
            >
                @if($group) {{-- start group question container --}}
                <x-accordion.container :active-container-key="true ? 'group' : ''"
                                       :wire:key="'group-section-'. $this->discussingQuestion->id"
                >
                    <x-accordion.block key="group"
                                       :emitWhenSet="true"
                                       :wire:key="'group-section-block-'. $this->discussingQuestion->id"
                                       mode="transparent"
                    >
                        <x-slot:title>
                            <h4 class="flex items-center pr-4"
                                selid="questiontitle"
                            >
                                <span>@lang('question.Vraaggroep')</span>
                                <span>:</span>
                                <span x-cloak class="ml-2 text-left flex line-clamp-1"
                                      title="{!! $group->name !!}">
                                            {!! $group->name !!}
                                        </span>
                                @if($group->isCarouselQuestion())
                                    <span class="ml-2 lowercase text-base"
                                          title="@lang('assessment.carousel_explainer')"
                                    >@lang('cms.carrousel')</span>
                                @endif
                            </h4>
                        </x-slot:title>
                        <x-slot:body>
                            <div class="flex flex-col gap-2"
                                 wire:key="group-block-{{  $group->uuid }}">
                                <div class="flex flex-wrap">
                                    @foreach($group->attachments as $attachment)
                                        <x-attachment.badge-view :attachment="$attachment"
                                                                 :title="$attachment->title"
                                                                 :wire:key="'badge-'.$group->uuid"
                                                                 :question-id="$group->getKey()"
                                                                 :question-uuid="$group->uuid"
                                        />
                                    @endforeach
                                </div>
                                <div class="">
                                    {!! $group->converted_question_html !!}
                                </div>
                            </div>
                        </x-slot:body>
                    </x-accordion.block>
                </x-accordion.container>
                {{-- end group question container --}}
                @endif

                <x-accordion.container :active-container-key="'question'"
                                       :wire:key="'question-section-'.$this->discussingQuestion->id"
                >
                    <x-accordion.block key="question"
                                       :wire:key="'question-section-block-'.$this->discussingQuestion->id"
                    >
                        <x-slot:title>
                            <div class="question-indicator items-center flex">
                                <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                                    <span class="align-middle cursor-default">{{ $this->questionIndexAsInTest }}</span>
                                </div>
                                <div class="flex gap-4 items-center relative top-0.5">
                                    <h4 class="inline-flex"
                                        selid="questiontitle">
                                        <span>@lang('co-learning.question')</span>
                                        <span>:</span>
                                        <span class="ml-2">{{ $this->discussingQuestion->type_name }}</span>
                                    </h4>
                                    <h7 class="inline-block">{{ $this->discussingQuestion->score }} pt</h7>
                                </div>
                            </div>
                        </x-slot:title>
                        <x-slot:body>
                            <div class="flex flex-col questionContainer"
                                 wire:key="question-block-{{  $this->discussingQuestion->uuid }}"
                                 x-init="
                                     elements = $el.querySelectorAll('img[src]').forEach((img) => {
                                        if(img.naturalWidth <= 0) {
                                           img.style.minHeight = '50px';
                                           img.style.minWidth = '50px';
                                            }
                                        img.addEventListener('load', (event) => {
                                            event.target.style.minHeight = 'unset';
                                            event.target.style.minWidth = 'unset';
                                        })
                                     })
                                 ">
                                <div class="flex flex-wrap pb-2 -mt-2">
                                    @foreach($this->discussingQuestion->attachments as $attachment)
                                        <x-attachment.badge-view :attachment="$attachment"
                                                                 :title="$attachment->title"
                                                                 :wire:key="'badge-'.$this->discussingQuestion->uuid.$loop->iteration"
                                                                 :question-id="$this->discussingQuestion->getKey()"
                                                                 :question-uuid="$this->discussingQuestion->uuid"
                                        />
                                    @endforeach
                                </div>

                                <div class="max-w-full">
                                    {!! $this->getDisplayableQuestionText()  !!}
                                </div>
                            </div>
                        </x-slot:body>
                    </x-accordion.block>
                </x-accordion.container>

                <x-accordion.container active-container-key=""
                                       :wire:key="'answer-model-section-'.$this->discussingQuestion->uuid"
                >
                    <x-accordion.block key="answer-model"
                                       :coloredBorderClass="'primary'"
                                       :wire:key="'answer-model-section-block'.$this->discussingQuestion->uuid"
                    >
                        <x-slot:title>
                            <div class="question-indicator items-center flex">
                                <h4 class="inline-block"
                                    selid="questiontitle">@lang('co-learning.answer_model')</h4>
                            </div>
                        </x-slot:title>
                        <x-slot:body>
                            <div class="w-full questionContainer" wire:key="answer-model-{{  $this->discussingQuestion->uuid }}">
                                <x-dynamic-component
                                        :component="'answer.teacher.'. str($this->discussingQuestion->type)->kebab()"
                                        :question="$this->discussingQuestion"
                                        :editorId="'editor-'.$this->discussingQuestion->uuid"
                                />
                            </div>
                        </x-slot:body>
                    </x-accordion.block>
                </x-accordion.container>

                @if($this->activeAnswerRating)
                    <div x-transition x-show="showStudentAnswer"> {{-- Not sure if this is the right way, but it's less sudden --}}
                        <x-accordion.container :active-container-key="'answer'"
                                               :wire:key="'answer-section-'.$this->discussingQuestion->uuid . $this->activeAnswerRating->id"
                        >
                            <x-accordion.block key="answer"
                                               :coloredBorderClass="'student'"
                                               :wire:key="'answer-section-block-'.$this->discussingQuestion->uuid . $this->activeAnswerRating->id"
                            >
                                <x-slot:title>
                                    <div class="question-indicator items-center flex gap-4">
                                        <h4 class="flex items-center flex-wrap" selid="questiontitle">
                                            <span>@lang('co-learning.answer')</span>
                                            <span>:</span>
                                            <span class="ml-2">{{ $this->discussingQuestion->type_name }}</span>
                                        </h4>
                                        <h7 class="inline-block min-w-fit">{{ $this->discussingQuestion->score }}pt
                                        </h7>
                                    </div>
                                </x-slot:title>
                                <x-slot:titleLeft>
                                    <div class="ml-auto relative top-0.5 flex gap-2 items-center">
                                        <x-dynamic-component :component="$this->activeAnswerAnsweredStatus" />
                                        <div class="relative w-[40px] h-[40px] flex items-center justify-center rounded-full hover:bg-primary/5 hover:text-primary active:bg-primary/10"
                                             @click="activeStudentAnswer = null"
                                             wire:click.stop="resetActiveAnswer()"
                                             x-on:mouseenter="$el.closest('button').classList.remove('group')"
                                             x-on:mouseleave="$el.closest('button').classList.add('group')"
                                        >
                                            <x-icon.on-smartboard-hide title="{{ __('co-learning.hide-from-smartboard') }}"/>
                                        </div>
                                    </div>
                                </x-slot:titleLeft>
                                <x-slot:body>
                                    <div class="student-answer | w-full"
                                         wire:key="student-answer-{{  $this->discussingQuestion->uuid . $this->activeAnswerRating->id }}"
                                    >
                                        <x-dynamic-component
                                                :component="'answer.student.'. str($this->discussingQuestion->type)->kebab()"
                                                :question="$this->discussingQuestion"
                                                :answer="$this->activeAnswerRating->answer"
                                                :editorId="'editor-'.$this->discussingQuestion->uuid.$this->activeAnswerRating->id"
                                                :show-toggles="false"
                                                :webSpellChecker="$this->testTake->enable_spellcheck_colearning"
                                                :answerRating="$this->activeAnswerRating"
                                                :inCoLearning="true"
                                                :disabled-toggle="true"
                                        />
                                    </div>
                                </x-slot:body>
                            </x-accordion.block>
                        </x-accordion.container>
                    </div>
                @endif

            </div>


        </div>
    @endif
    {{-- Success is as dangerous as failure. --}}
</div>
