<div class="flex flex-col w-full">
    <div class="w-full" wire:ignore>

        <x-input.group class="w-full ckeditor-disabled" label="" style="position: relative;">
            <x-input.rich-textarea
                    :editor-id="$editorId"
                    :allowWsc="$webSpellChecker"
                    :disabled="true"
                    type="cms"
            >
                {!! $answerValue !!}
            </x-input.rich-textarea>
            <div class="absolute w-full h-full top-0 left-0 pointer-events-auto"></div>
        </x-input.group>
    </div>
    <div id="word-count-{{ $editorId }}" wire:ignore class="word-count note text-sm mt-2"></div>
</div>