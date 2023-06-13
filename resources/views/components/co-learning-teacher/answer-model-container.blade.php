<div class="flex flex-col pt-[14px]  px-10 content-section rs_readable relative"
>
    <div class="flex flex-wrap items-center question-indicator pb-[13px]">

        <h4 class="inline-block mr-6"
            selid="questiontitle"> {{ __('co-learning.answer_model') }}</h4>
        <div class="absolute right-[-14px] group" @click="showAnswerModel = ! showAnswerModel">
            <div class="w-10 h-10 rounded-full flex items-center justify-center group-hover:bg-primary group-hover:opacity-[0.05]"></div>
            <template x-if="true">
                <x-icon.chevron class="absolute top-[14px] left-4 text-sysbase transition"
                                x-bind:class="showAnswerModel ? 'rotate-90' : ''" x-cloak/>
            </template>
        </div>
    </div>


    <div x-show="showAnswerModel" x-collapse.duration.500ms x-cloak>
        <div class="w-full flex question-bottom-line mb-2"></div>
        <div class="questionContainer w-full pb-[33px]">
            <div class="w-full">
                <div class="relative">
                    @if($this->testTake->discussingQuestion instanceof \tcCore\DrawingQuestion)
                        <img src="{{route('teacher.drawing-question-answer-model', $this->testTake->discussingQuestion->uuid)}}"
                             style="max-width: 100%"
                             class="border border-blue-grey rounded-10 w-full"
                        />
                        <div class="absolute bottom-4 right-4">
                            <x-button.secondary wire:click="$emit(
                            'openModal',
                            'co-learning.drawing-question-preview-modal',
                            {
                                imgSrc: '{{ route('teacher.drawing-question-answer-model', $this->testTake->discussingQuestion->uuid) }}',
                                title: 'answer-model'
                            })">
                                <x-icon.screen-expand/>
                                <span>{{ __('co-learning.view_larger') }}</span>
                            </x-button.secondary>
                        </div>
                    @elseif($this->testTake->discussingQuestion instanceof \tcCore\CompletionQuestion)
                        <div class="mt-4">
                            {!! $this->answerModelHtml !!}
                        </div>
                    @elseif($this->testTake->discussingQuestion->type === 'OpenQuestion')
                        @if($this->testTake->discussingQuestion->subtype === 'medium'
                        || $this->testTake->discussingQuestion->subtype === 'long'
                        || $this->testTake->discussingQuestion->subtype === 'writing'
                        )
                            @php
                                $editorId = $this->testTake->discussingQuestion->type . $this->testTake->discussingQuestion->getKey();
                            @endphp

                            <div wire:ignore wire:key="{{$editorId}}">
                                <x-input.group class="w-full" label="" style="position: relative;">
                                    <textarea id="{{ $editorId }}" name="{{ $editorId }}"
                                              x-init="
                                                editor = ClassicEditors['{{ $editorId }}'];
                                                if (editor) {
                                                    editor.destroy(true);
                                                }
                                                RichTextEditor.initClassicEditorForStudentPlayer('{{  $editorId }}', '{{ $this->testTake->discussingQuestion->getKey() }}');
                                                setTimeout(() => {
                                                    RichTextEditor.setReadOnly(ClassicEditors.{{  $editorId }});
                                                }, 100)
                                              "
                                    >
                                        {!! $this->answerModelHtml !!}
                                    </textarea>
                                    <div class="absolute w-full h-full top-0 left-0 pointer-events-auto"></div>
                                </x-input.group>
                            </div>
                            <div id="word-count-{{ $editorId }}" wire:ignore class="word-count"></div>

                        @else
                            <x-input.group for="me" class="w-full disabled mt-4">
                                <div class="border border-light-grey p-4 rounded-10 h-fit">
                                    {!! $this->answerModelHtml !!}
                                </div>
                            </x-input.group>
                        @endif
                    @endif
                </div>
            </div>
        </div>

    </div>
    <div class="container-border-left primary"></div>
</div>

