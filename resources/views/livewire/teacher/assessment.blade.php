<div id="assessment-page"
     class="min-h-screen w-full"
>
    <x-partials.header.assessment :testName="$testName" />
    @if(false)

        <div class="px-15 py-10 gap-6 flex flex-col "
             x-data="{showQuestion: true}"
        >
            {{-- Question section --}}
            <x-accordion.container :active-container-key="1">
                <x-accordion.block :key="1">
                    <x-slot:title>
                        <div class="question-indicator items-center flex">
                            <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                                <span class="align-middle cursor-default">{{ $this->qi }}</span>
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
                        <div class="flex flex-col gap-2">
                            <div class="flex flex-wrap">
                                @foreach($this->currentQuestion->attachments as $attachment)
                                    <x-attachment.badge-view :attachment="$attachment"
                                                             :title="$attachment->title"
                                                             :wire:key="'badge-'.$this->currentQuestion->uuid.$this->qi"
                                                             :question-id="$this->currentQuestion->getKey()"
                                                             :question-uuid="$this->currentQuestion->uuid"
                                    />
                                @endforeach
                            </div>

                            <div>
                                {!! $this->currentQuestion->getQuestionInstance()->question !!}
                            </div>
                        </div>
                    </x-slot:body>
                </x-accordion.block>
            </x-accordion.container>

            {{-- Answer section --}}
            @unless($this->currentQuestion->isType('infoscreen'))
                <x-accordion.container :active-container-key="1">
                    <x-accordion.block :key="1"
                                       :coloredBorderClass="'student'"
                    >
                        <x-slot:title>
                            <div class="question-indicator items-center flex gap-4">
                                <h4 class="flex items-center"
                                    selid="questiontitle">
                                    <span>@lang('co-learning.answer')</span>
                                    <span>:</span>
                                    <span class="ml-2">{{ $this->currentQuestion->type_name }}</span>
                                </h4>
                                <h7 class="inline-block">{{ $this->currentQuestion->score }} pt</h7>
                            </div>
                        </x-slot:title>
                        <x-slot:titleLeft>
                            <div class="ml-auto mr-6">
                                <x-dynamic-component :component="$this->currentAnswer->answeredStatus"/>
                            </div>
                        </x-slot:titleLeft>
                        <x-slot:body>
                            <x-dynamic-component :component="'answer.teacher.'. str($this->currentQuestion->type)->kebab()"
                                                 :question="$this->currentQuestion"
                                                 :answer="$this->currentAnswer"
                            />
                        </x-slot:body>
                    </x-accordion.block>
                </x-accordion.container>

                {{-- Answermodel section --}}
                <x-accordion.container :active-container-key="1">
                    <x-accordion.block :key="1"
                                       :coloredBorderClass="'primary'"
                    >
                        <x-slot:title>
                            <div class="question-indicator items-center flex">
                                <h4 class="inline-block" selid="questiontitle">@lang('co-learning.answer_model')</h4>
                            </div>
                        </x-slot:title>
                        <x-slot:body>
                            <x-dynamic-component :component="'answer.teacher.'. str($this->currentQuestion->type)->kebab()"
                                                 :question="$this->currentQuestion"
                                                 :answer="$this->currentAnswer"
                            />
                        </x-slot:body>
                    </x-accordion.block>
                </x-accordion.container>
            @endif

        </div>

    @endif
</div>