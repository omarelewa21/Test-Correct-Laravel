<div class="flex flex-col w-full justify-center items-center bg-white space-y-3 rounded-10">

    <div class="text-center w-3/4">
        <h5>{{__($this->questionAttachment->getAttachmentTitleShortKey())}}</h5>
        @if($this->timeout)
            <h5>{{ __('test_take.time_left_to_answer_after_closing_attachment', ['timeout' => $this->timeout]) }}</h5>
        @endif
    </div>

    <div class="w-3/4">
        <div class="mt-4" wire:ignore>
            <audio id="player" src="{{ route('teacher.preview.question-attachment-show', ['attachment' => $attachment->uuid, 'question' => $questionId], false) }}"
                x-ref="player"
            ></audio>
        </div>
    </div>
</div>

<script>
    let player = plyrPlayer.render(
        document.querySelector('#player'),
        @this,
        '{{$attachment->uuid}}',
        '{!! $this->questionAttachment->options !!}',
        true,
        'preview'
    );

    player.on('loadeddata', ()=> {
        player.currentTime = parseFloat('{{ $this->getCurrentTime() }}');
    });

    document.addEventListener('pause-audio-player', ()=>{
        player.pause();
    });
</script>