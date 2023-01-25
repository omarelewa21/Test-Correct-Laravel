@props([
'question',
])
@php
    $editorId = 'test'.$this->questionIndex;
@endphp

<div class="flex flex-col pt-[14px] pb-[33px] px-10 content-section rs_readable relative"
     x-data="{collapsed: false}"
>
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-2 justify-between">
        <div class="flex items-center">

            <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                <span class="align-middle cursor-default">{{ $this->questionIndex }}</span>
            </div>

            {{--@if($question->closeable && !$this->closed)
                <x-icon.unlocked class="ml-2"/>
            @elseif($this->closed)
                <x-icon.locked class="ml-2"/>
            @endif--}}

            <h4 class="inline-block ml-2 mr-6"
                selid="questiontitle">{{ __('co-learning.question') }}
                : {!! __('co-learning.'.$question->type.($question->subtype ? '-'.$question->subtype : '')) !!}
            </h4>
            <h7 class="inline-block">{{ $question->score }} pt</h7>

            {{-- todo: get group question --}}
            {{--@if($this->question->group)
                <h1 class="inline-flex ml-2">{{ $this->group->name }}</h1>
            @endif--}}
            {{--@if ($this->answered)
                @if($this->isQuestionFullyAnswered())
                    <x-answered/>
                @else
                    <x-partly-answered/>
                @endif
            @else
                <x-not-answered/>
            @endif--}}

        </div>


        <div class="absolute right-[-14px] group" @click="collapsed = ! collapsed">
            <div class="w-10 h-10 rounded-full flex items-center justify-center group-hover:bg-primary group-hover:opacity-[0.05]"></div>
            <template x-if="true">
                <x-icon.chevron class="absolute top-[14px] left-4 text-sysbase transition"
                                x-bind:class="collapsed ? '' : 'rotate-90'" x-cloak/>
            </template>
        </div>

    </div>


    {{--    @if($this->group)--}}
    {{--        <div class="mb-5">{!! $this->group->question->converted_question_html !!}</div>--}}
    {{--    @endif--}}
    <div {{-- class="flex flex-1 overview"--}} x-show="!collapsed" x-collapse.duration.500ms x-cloak>
        {{--@if($question->closeable || ( !is_null($question->groupQuestion) && $question->groupQuestion->closeable) )
            @if($this->closed)
                <span>{{__('test_take.question_closed_text')}}</span>
            @else
                <span>{{__('test_take.question_closeable_text')}}</span>
            @endif
        @else--}}
        <div class="flex flex-wrap">
            @foreach($question->attachments as $attachment)
                <x-attachment.badge-view :upload="false"
                                         :attachment="$attachment"
                                         :title="$attachment->title"
                                         wire:key="a-badge-{{ $attachment->id.$this->testTake->discussing_question_id }}"
                                         :question-id="$question->getKey()"
                        {{--  --}}
                />
            @endforeach
        </div>
        @if($question->type !== 'CompletionQuestion')
            <div>
                {!! $question->getQuestionInstance()->question !!}
            </div>
        @endif

        <div class="questionContainer w-full" wire:key="{{ $editorId }}">
            <div class="w-full">
                <div class="relative">
                    @if($question->type === 'OpenQuestion')
                        @if($question->subtype === 'medium' || $question->subtype === 'long')

                            <div wire:ignore>
                                <span>{!! __('test_take.instruction_open_question') !!}</span>
                                <x-input.group class="w-full" label="" style="position: relative;">
                                    <textarea id="{{ $editorId }}" name="{{ $editorId }}"
                                              x-init="
                                                editor = ClassicEditors['{{ $editorId }}'];
                                                if (editor) {
                                                    editor.destroy(true);
                                                }
                                                RichTextEditor.initClassicEditorForStudentplayer('{{  $editorId }}', '{{ $question->getKey() }}');
                                                setTimeout(() => {
                                                    RichTextEditor.setReadOnly(ClassicEditors.{{  $editorId }});
                                                }, 100)
                                              "
                                    ></textarea>
                                </x-input.group>
                            </div>
                            <div id="word-count-{{ $editorId }}" wire:ignore class="word-count"></div>

                        @elseif($question->subtype === 'short')

                            <x-input.group for="me" class="w-full disabled mt-4">
                                <div class="border border-light-grey p-4 rounded-10 h-fit text-midgrey">
                                    {{ __('co-learning.write_your_answer') }}
                                    <br><br>
                                </div>
                            </x-input.group>

                        @elseif($question->subtype === 'writing')
                            wip
                        @endif

                    @elseif($question->type === 'CompletionQuestion')
                        {!! $this->convertCompletionQuestionToHtml() !!}
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{--<script>
        document.addEventListener("DOMContentLoaded", () => {
            var editor = ClassicEditors['{{ $editorId }}'];
            if (editor) {
                editor.destroy(true);
            }
            RichTextEditor.initClassicEditorForStudentplayer('{{$editorId}}', '{{ $question->getKey() }}');
            setTimeout(() => {
                RichTextEditor.setReadOnly(ClassicEditors.{{  $editorId }});

            }, 100)
        });
    </script>--}}
    {{--    <div x-on:contextmenu="$event.preventDefault()" class="absolute z-10 w-full h-full left-0 top-0"></div>--}}
</div>

