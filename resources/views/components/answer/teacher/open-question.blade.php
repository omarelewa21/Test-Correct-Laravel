<div class="flex w-full">
    @if(in_array($question->subtype, ['medium', 'long', 'writing']))
        <div wire:ignore wire:key="answer-editor-{{$editorId}}">
            <x-input.group class="w-full" label="" style="position: relative;">
                                    <textarea id="{{ $editorId }}" name="{{ $editorId }}"
                                              x-init="
                                                editor = ClassicEditors['{{ $editorId }}'];
                                                if (editor) {
                                                    editor.destroy(true);
                                                }
                                                RichTextEditor.initClassicEditorForStudentplayer('{{  $editorId }}', '{{ $question->getKey() }}');
                                                setTimeout(() => {
                                                    RichTextEditor.setReadOnly(ClassicEditors['{{  $editorId }}']);
                                                }, 100)
                                              "
                                    >
                                        {!! $answerValue !!}
                                    </textarea>
                <div class="absolute w-full h-full top-0 left-0 pointer-events-auto"></div>
            </x-input.group>
        </div>
        <div id="word-count-{{ $editorId }}" wire:ignore class="word-count"></div>
    @else
        <x-input.group for="me" class="w-full disabled mt-4">
            <div class="border border-light-grey p-4 rounded-10 h-fit">
                {!! $answerValue !!}
            </div>
        </x-input.group>
    @endif
</div>