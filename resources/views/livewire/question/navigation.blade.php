<div class="flex flex-col pt-4 pb-4 space-y-10" test-take-player wire:key="navigation">
    <x-partials.question-indicator wire:key="nav" :questions="$questions"></x-partials.question-indicator>

{{--    <div x-data="" class="flex justify-end space-x-4">--}}
{{--        @foreach($questions as $question)--}}
{{--            @if($question->attachments && !$question->attachments->isEmpty())--}}
{{--                @foreach($question->attachments as $attachment)--}}
{{--                    <x-button.text-button @click="console.log('{{ $attachment->id}}')">--}}
{{--                        <x-icon.attachment/>--}}
{{--                        <span>Bijlage {{$loop->iteration}}</span>--}}
{{--                    </x-button.text-button>--}}
{{--                @endforeach--}}
{{--            @endif--}}
{{--            @if($question->note_type == 'TEXT')--}}
{{--                <x-button.secondary size="sm" @click="console.log('Notitieblok openen')">--}}
{{--                    <span>Open notitiblok</span>--}}
{{--                </x-button.secondary>--}}
{{--            @endif--}}
{{--        @endforeach--}}
{{--    </div>--}}
</div>
