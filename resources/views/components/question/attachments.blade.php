@if(!$question->attachments->isEmpty())
    @foreach($question->attachments as $attachment)
        <x-button.text-button class="mb-4" wire:click="showAttachment({{ $attachment->getKey() }})">
            <x-icon.attachment/>
            <span>Bijlage {{$loop->iteration}}</span>
        </x-button.text-button>
    @endforeach
@endif