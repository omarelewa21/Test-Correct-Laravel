
@if(!$question->attachments->isEmpty())
    @foreach($question->attachments as $attachment)
        <x-button.text-button class="mb-4" wire:click="showAttachment({{ $attachment->getKey() }})" wire:key="attachment-{{$attachment->getKey()}}">
            <x-icon.attachment/>
            <span>{{ __('test_take.attachment') }} {{$loop->iteration}}</span>
        </x-button.text-button>
    @endforeach
@elseif($this->group && !$this->group->attachments->isEmpty())
    @foreach($this->group->attachments as $attachment)
        <x-button.text-button class="mb-4" wire:click="showAttachment({{ $attachment->getKey() }})" wire:key="attachment-{{$attachment->getKey()}}">
            <x-icon.attachment/>
            <span>{{ __('test_take.attachment') }} {{$loop->iteration}}</span>
        </x-button.text-button>
    @endforeach
@endif