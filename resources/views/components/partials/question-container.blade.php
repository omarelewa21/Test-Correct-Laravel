@props([
'question',
'q',
'number'
])
<div x-data="{ showMe: {!! $number === $q ? 'true' : 'false'  !!} }"
     x-on:current-updated.window="showMe = ({{ $number }} == $event.detail.current)" x-show="showMe">
    <div>
        <div class="flex justify-end space-x-4">
            @if(!$question->attachments->isEmpty())
                @foreach($question->attachments as $attachment)
                    <x-button.text-button class="mb-4" @click="alert('{{$attachment->getCurrentPath()}}')">
                        <x-icon.attachment/>
                        <span>Bijlage {{$loop->iteration}}</span>
                    </x-button.text-button>
                @endforeach
            @endif
            @if($question->note_type == 'TEXT')
                <x-button.secondary size="sm" class="mb-4" @click="alert('Open Notitieblok')">
                    <span>Open notitiblok</span>
                </x-button.secondary>
            @endif
        </div>
    </div>
    <div class="flex flex-col p-8 sm:p-10 content-section">
        <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
            <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
                <span class="align-middle">{{ $number }}</span>
            </div>
            <h1 class="inline-block ml-2 mr-6">{!! __($question->caption) !!}</h1>
            @if ($question->score > 0)
                <h4 class="inline-block">{{ $question->score }} pt</h4>
            @endif
        </div>
        <div class="flex flex-1">
            {{ $slot }}
        </div>
    </div>
</div>
