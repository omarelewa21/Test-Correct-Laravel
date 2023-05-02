<div id="co-learning-teacher-page"
     class="flex w-full relative" style="z-index: 1; min-height: 100vh"
     x-data="{
     showStudentAnswer: false,
     showAnswerModel: false,
     showQuestion: true,
     resetToggles() {
        this.showStudentAnswer = false;
        this.showAnswerModel = false;
        this.showQuestion = true;
     },
     async openStudentAnswer(id) {
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
                             {{--wire:poll.keep-alive.5000ms="render()"--}}
        >
            <div class="flex flex-col w-full space-y-4 pt-10 px-[60px] pb-14"
                 wire:key="container-{{$this->testTake->discussing_question_id}}"
            >
                <x-accordion.container :active-container-key="'question'"
                                       :wire:key="'question-section-'.$this->discussingQuestion->id"
                >
                    <x-accordion.block key="question"
                                       :wire:key="'question-section-block-'.$this->discussingQuestion->id"
                    >
                        <x-slot:title>
                            <div class="question-indicator items-center flex">
                                <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                                    <span class="align-middle cursor-default">{{ $this->questionIndex }}</span>
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
                            <div class="flex flex-col gap-2"
                                 wire:key="question-block-{{  $this->discussingQuestion->uuid }}">
                                <div class="flex flex-wrap">
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
                                    {!! $this->discussingQuestion->getDisplayableQuestionText()  !!}
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
                            <div class="w-full" wire:key="answer-model-{{  $this->discussingQuestion->uuid }}">
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
                                    <div class="ml-auto mr-6 relative top-0.5 flex gap-2 items-center">
                                        <x-dynamic-component :component="$this->activeAnswerAnsweredStatus" />
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
