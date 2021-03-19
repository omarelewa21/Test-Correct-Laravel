{{ __("@if(!$question->attachments->isEmpty())
") }}@if(!$question->attachments->isEmpty())
    @foreach($question->attachments as $attachment)
        <x-button.text-button class="mb-4" wire:click="showAttachment({{ $attachment->getKey() }})" wire:key="attachment-{{$attachment->getKey()}}">
            <x-icon.attachment/>
            <span>{{ __("attachments-button.Bijlage") }} {{$loop->iteration}}</span>
        </x-button.text-button>
    @endforeach
@endif