<x-partials.modal.preview>
    <x-slot:icon>
        <x-icon.feedback-text />
    </x-slot:icon>
    <x-slot:title>Inline feedback schrijven in antwoord</x-slot:title>

    <div class="inline-feedback-modal | flex h-full w-full p-5">
        <x-input.rich-textarea wire:model="feedback"
                               type="cms"
                               :editor-id="$editorId"
                               :disabled="$disabled"
        />
        <div id="word-count-{{ $editorId }}" wire:ignore class="word-count note text-sm mt-2"></div>
    </div>
</x-partials.modal.preview>