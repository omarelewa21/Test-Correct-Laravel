<div @class(['px-15 py-10 gap-6 flex flex-col flex-1 relative' , $attributes->get('class')])>
    {{ $subHeader ?? '' }}

    {{-- Group section --}}
    @if($group)
        <x-accordion.container :active-container-key="$groupPanel ? 'group' : ''"
                               :wire:key="'group-section-'. $uniqueKey"
        >
            <x-accordion.block key="group"
                               :emitWhenSet="true"
                               :wire:key="'group-section-block-'. $uniqueKey"
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
    @endif

    {{-- Question section --}}
        <x-accordion.container :active-container-key="$questionPanel ? 'question' : ''"
                               :wire:key="'question-section-'. $uniqueKey"
        >
            <x-accordion.block key="question"
                               :emitWhenSet="true"
                               :wire:key="'question-section-block-'. $uniqueKey"
            >
                <x-slot:title>
                    <div class="question-indicator items-center flex">
                        <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                            <span class="align-middle cursor-default">{{ $navigationValue }}</span>
                        </div>
                        <div class="flex gap-4 items-center relative top-0.5">
                            <h4 class="inline-flex"
                                selid="questiontitle">
                                <span>@lang('co-learning.question')</span>
                                <span>:</span>
                                <span class="ml-2">{{ $question->type_name }}</span>
                            </h4>
                            <h7 class="inline-block">{{ $question->score }} pt</h7>
                        </div>
                    </div>
                </x-slot:title>
                <x-slot:body>
                    <div class="flex flex-col gap-2 questionContainer w-full"
                         wire:key="question-block-{{  $question->uuid }}">
                        <div class="flex flex-wrap" wire:key="attachment-container-{{ $uniqueKey }}">
                            @foreach($question->attachments as $attachment)
                                <x-attachment.badge-view :attachment="$attachment"
                                                         :title="$attachment->title"
                                                         :wire:key="'badge-'.$question->uuid. $uniqueKey"
                                                         :question-id="$question->getKey()"
                                                         :question-uuid="$question->uuid"
                                />
                            @endforeach
                        </div>

                        <div class="max-w-full">
                            {!! $questionText !!}
                        </div>
                    </div>
                </x-slot:body>
            </x-accordion.block>
        </x-accordion.container>
    {{-- Answer section --}}
    @unless($question->isType('infoscreen'))
        {{ $answerBlock }}

        {{-- Answermodel section --}}
        @if($showCorrectionModel)
            <x-accordion.container :active-container-key="$answerModelPanel ? 'answer-model' : ''"
                                   :wire:key="'answer-model-section-'. $uniqueKey"
            >
                <x-accordion.block key="answer-model"
                                   :coloredBorderClass="'primary'"
                                   :emitWhenSet="true"
                                   :wire:key="'answer-model-section-block'. $uniqueKey"
                >
                    <x-slot:title>
                        <div class="question-indicator items-center flex">
                            <h4 class="inline-block"
                                selid="questiontitle">@lang('co-learning.answer_model')</h4>
                        </div>
                    </x-slot:title>
                    <x-slot:body>
                        <div class="w-full questionContainer" wire:key="answer-model-{{$question->uuid}}">
                            <x-dynamic-component
                                    :component="'answer.teacher.'. str($question->type)->kebab()"
                                    :question="$question"
                                    :editorId="'editor-'.$question->uuid"
                                    :testTake="$testTake"
                                    :answer="$this->currentAnswer"
                            />
                        </div>
                    </x-slot:body>
                </x-accordion.block>
            </x-accordion.container>
        @endif
    @endif

</div>