@props([
'blockAttachments' => false,
'question'
])
@php $attachment_count = 1 @endphp

@if($this->group && !$this->group->attachments->isEmpty())
    @foreach($this->group->attachments as $attachment)
        <x-button.text-button class="mb-4"
            x-on:click="$wire.closeAttachmentModal();
                setTimeout(function(){
                    $wire.showAttachment('{{ $attachment->uuid }}');
                }, 500);"
            wire:key="key-attachment-{{$attachment->uuid}}" :disabled="$blockAttachments"
                              selid="open-attachment-btn"
        >
            <x-icon.attachment/>
            <span wire:ignore>{{ __('test_take.attachment') }} {{$attachment_count}}</span>
            @php $attachment_count++ @endphp
        </x-button.text-button>
    @endforeach
@endif

@if(!$question->attachments->isEmpty())
    @foreach($question->attachments as $attachment)
        <x-button.text-button class="mb-4"
            x-on:click="$wire.closeAttachmentModal();
                setTimeout(function(){
                    $wire.showAttachment('{{ $attachment->uuid }}');
                }, 500);"
            wire:key="key-attachment-{{$attachment->uuid}}" :disabled="$blockAttachments"
                                selid="open-attachment-btn"
        >
            <x-icon.attachment/>
            <span wire:ignore>{{ __('test_take.attachment') }} {{$attachment_count}}</span>
            @php $attachment_count++ @endphp
        </x-button.text-button>
    @endforeach
@endif
