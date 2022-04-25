@props([
'blockAttachments' => false,
'question'
])
@if(!$question->attachments->isEmpty())
    @foreach($question->attachments as $attachment)
        <x-button.text-button class="mb-4"
            x-on:click="$wire.closeAttachmentModal();
                setTimeout(function(){
                    $wire.showAttachment('{{ $attachment->uuid }}');
                }, 500);"
            wire:key="key-attachment-{{$attachment->uuid}}" :disabled="$blockAttachments"
        >
            <x-icon.attachment/>
            <span wire:ignore>{{ __('test_take.attachment') }} {{$loop->iteration}}</span>
        </x-button.text-button>
    @endforeach
@elseif($this->group && !$this->group->attachments->isEmpty())
    @foreach($this->group->attachments as $attachment)
        <x-button.text-button class="mb-4"
            x-on:click="$wire.closeAttachmentModal();
                setTimeout(function(){
                    $wire.showAttachment('{{ $attachment->uuid }}');
                }, 500);"
            wire:key="key-attachment-{{$attachment->uuid}}" :disabled="$blockAttachments"
        >
            <x-icon.attachment/>
            <span wire:ignore>{{ __('test_take.attachment') }} {{$loop->iteration}}</span>
        </x-button.text-button>
    @endforeach
@endif