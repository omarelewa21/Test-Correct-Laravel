@foreach($attachments as $attachment)
    <x-attachment.badge-view :attachment="$attachment"
        :title="$attachment->displayTitle"
        :wire:key="'badge-'.$question->uuid.'-'.$loop->iteration"
        :question-id="$question->getKey()"
        :question-uuid="$question->uuid"
        :clickOverride="true"
        x-on:click="await $wire.closeAttachmentModal();$wire.showAttachment('{{ $attachment->uuid }}')"
        class="{{ !$loop->last && $attachment->groupDivider ? 'lead-divider' : '' }}"
    />
@endforeach