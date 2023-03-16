<div id="assessment-page"
     class="min-h-screen w-full"
     x-data="assessment"
     x-on:update-navigation.window="dispatchUpdateToNavigator($event.detail.navigator, $event.detail.updates)"
>
    <x-partials.header.assessment :testName="$testName" />
    @if($this->headerCollapsed)
        <div class="px-15 py-10 gap-6 flex flex-col">
            @js($this->currentQuestion->id)
            @js($this->currentAnswer->test_participant_id)
            {{-- Group section --}}
            @if($this->currentGroup)
                <x-accordion.container :active-container-key="$this->groupPanel ? 'group' : ''"
                                       :wire:key="'group-section-'.$this->questionNavigationValue.$this->answerNavigationValue"
                >
                    <x-accordion.block key="group"
                                       :emitWhenSet="true"
                                       :wire:key="'group-section-block-'.$this->questionNavigationValue.$this->answerNavigationValue"
                                       mode="transparent"
                    >
                        <x-slot:title>
                            <div class="question-indicator items-center flex">
                                <div class="flex gap-4 items-center relative top-0.5">
                                    <h4 class="inline-flex"
                                        selid="questiontitle">
                                        <span>@lang('question.Vraaggroep')</span>
                                        <span>:</span>
                                        <span class="ml-2">{{ $this->currentGroup->name }}</span>
                                    </h4>
                                </div>
                            </div>
                        </x-slot:title>
                        <x-slot:body>
                            <div class="flex flex-col gap-2"
                                 wire:key="group-block-{{  $this->currentGroup->uuid }}">
                                <div class="flex flex-wrap">
                                    @foreach($this->currentGroup->attachments as $attachment)
                                        <x-attachment.badge-view :attachment="$attachment"
                                                                 :title="$attachment->title"
                                                                 :wire:key="'badge-'.$this->currentGroup->uuid"
                                                                 :question-id="$this->currentGroup->getKey()"
                                                                 :question-uuid="$this->currentGroup->uuid"
                                        />
                                    @endforeach
                                </div>
                                <div>
                                    {!! $this->currentGroup->converted_question_html !!}
                                </div>
                            </div>
                        </x-slot:body>
                    </x-accordion.block>
                </x-accordion.container>
            @endif

            {{-- Question section --}}
            @if($this->needsQuestionSection)
                <x-accordion.container :active-container-key="$this->questionPanel ? 'question' : ''"
                                       :wire:key="'question-section-'.$this->questionNavigationValue.$this->answerNavigationValue"
                >
                    <x-accordion.block key="question"
                                       :emitWhenSet="true"
                                       :wire:key="'question-section-block-'.$this->questionNavigationValue.$this->answerNavigationValue"
                    >
                        <x-slot:title>
                            <div class="question-indicator items-center flex">
                                <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                                    <span class="align-middle cursor-default">{{ $this->questionNavigationValue }}</span>
                                </div>
                                <div class="flex gap-4 items-center relative top-0.5">
                                    <h4 class="inline-flex"
                                        selid="questiontitle">
                                        <span>@lang('co-learning.question')</span>
                                        <span>:</span>
                                        <span class="ml-2">{{ $this->currentQuestion->type_name }}</span>
                                    </h4>
                                    <h7 class="inline-block">{{ $this->currentQuestion->score }} pt</h7>
                                </div>
                            </div>
                        </x-slot:title>
                        <x-slot:body>
                            <div class="flex flex-col gap-2"
                                 wire:key="question-block-{{  $this->currentQuestion->uuid }}">
                                <div class="flex flex-wrap">
                                    @foreach($this->currentQuestion->attachments as $attachment)
                                        <x-attachment.badge-view :attachment="$attachment"
                                                                 :title="$attachment->title"
                                                                 :wire:key="'badge-'.$this->currentQuestion->uuid.$this->questionNavigationValue"
                                                                 :question-id="$this->currentQuestion->getKey()"
                                                                 :question-uuid="$this->currentQuestion->uuid"
                                        />
                                    @endforeach
                                </div>

                                <div>
                                    {!! $this->currentQuestion->converted_question_html !!}
                                </div>
                            </div>
                        </x-slot:body>
                    </x-accordion.block>
                </x-accordion.container>
            @endif
            {{-- Answer section --}}
            @unless($this->currentQuestion->isType('infoscreen'))
                <x-accordion.container :active-container-key="$this->answerPanel ? 'answer' : ''"
                                       :wire:key="'answer-section-'.$this->questionNavigationValue.$this->answerNavigationValue"
                >
                    <x-accordion.block key="answer"
                                       :coloredBorderClass="'student'"
                                       :emitWhenSet="true"
                                       :wire:key="'answer-section-block-'.$this->questionNavigationValue.$this->answerNavigationValue"
                    >
                        <x-slot:title>
                            <div class="question-indicator items-center flex gap-4">
                                <h4 class="flex items-center" selid="questiontitle">
                                    <span>@lang('co-learning.answer')</span>
                                    <span>:</span>
                                    <span class="ml-2">{{ $this->currentQuestion->type_name }}</span>
                                </h4>
                                <h7 class="inline-block">{{ $this->currentQuestion->score }} pt</h7>
                            </div>
                        </x-slot:title>
                        <x-slot:titleLeft>
                            <div class="ml-auto mr-6 relative top-0.5">
                                <x-dynamic-component :component="$this->currentAnswer->answeredStatus" />
                            </div>
                        </x-slot:titleLeft>
                        <x-slot:body>
                            <div class="w-full"
                                 wire:key="student-answer-{{$this->currentQuestion->uuid.$this->currentAnswer->uuid}}"
                            >
                                <x-dynamic-component
                                        :component="'answer.student.'. str($this->currentQuestion->type)->kebab()"
                                        :question="$this->currentQuestion"
                                        :answer="$this->currentAnswer"
                                        :editorId="'editor-'.$this->currentQuestion->uuid.$this->currentAnswer->uuid"
                                />
                            </div>
                        </x-slot:body>
                    </x-accordion.block>
                </x-accordion.container>

                {{-- Answermodel section --}}
                <x-accordion.container :active-container-key="$this->answerModelPanel ? 'answer-model' : ''"
                                       :wire:key="'answer-model-section-'.$this->questionNavigationValue.$this->answerNavigationValue"
                >
                    <x-accordion.block key="answer-model"
                                       :coloredBorderClass="'primary'"
                                       :emitWhenSet="true"
                                       :wire:key="'answer-model-section-block'.$this->questionNavigationValue.$this->answerNavigationValue"
                    >
                        <x-slot:title>
                            <div class="question-indicator items-center flex">
                                <h4 class="inline-block" selid="questiontitle">@lang('co-learning.answer_model')</h4>
                            </div>
                        </x-slot:title>
                        <x-slot:body>
                            <div class="w-full" wire:key="answer-model-{{$this->currentQuestion->uuid}}">
                                <x-dynamic-component
                                        :component="'answer.teacher.'. str($this->currentQuestion->type)->kebab()"
                                        :question="$this->currentQuestion"
                                        :editorId="'editor-'.$this->currentQuestion->uuid"
                                />
                            </div>
                        </x-slot:body>
                    </x-accordion.block>
                </x-accordion.container>
            @endif

        </div>

    @endif
</div>