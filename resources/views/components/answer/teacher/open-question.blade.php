<div class="flex flex-col w-full">
    @if(in_array($question->subtype, ['medium', 'long', 'writing']))
        <div class="w-full" wire:ignore>

            <x-input.group class="w-full ckeditor-disabled" label="" style="position: relative;">
                <x-input.rich-textarea
                        :editor-id="$editorId"
                        :allowWsc="$question->isSubType('writing') ? $allowWsc : false"
                        :disabled="true"
                >
                    {!! $answerValue !!}
                </x-input.rich-textarea>
                <div class="absolute w-full h-full top-0 left-0 pointer-events-auto"></div>
            </x-input.group>
        </div>
        <div id="word-count-{{ $editorId }}" wire:ignore class="word-count note text-sm mt-2"></div>
    @else
        <x-input.group for="me" class="w-full disabled mt-2 border border-bluegrey rounded-10 ">
            <div class="p-4 h-fit">
                {!! $answerValue !!}
            </div>
        </x-input.group>
    @endif
</div>