
<div class="flex flex-col w-full justify-center items-center bg-white space-y-3 rounded-10" selid="audio-attachment">
    <div class="text-center w-3/4">
        <h5>{{__($this->questionAttachment->getAttachmentTitleShortKey())}}</h5>
        @if($this->timeout)
            <h5>{{ __('test_take.time_left_to_answer_after_closing_attachment', ['timeout' => $this->timeout]) }}</h5>
        @endif
    </div>

    <div class="w-3/4">
        <div class="mt-4" wire:ignore>
            <audio id="player-{{ $attachment->uuid }}" src="{{ route('student.question-attachment-show', ['attachment' => $attachment, 'answer' => $this->answerId], false) }}"
                x-ref="player"
            ></audio>
        </div>
    </div>
</div>

<script>
    let player = plyrPlayer.render(
        document.querySelector('#player-{{ $attachment->uuid }}'),
        @this,
        '{{$attachment->uuid}}',
        '{!! $this->questionAttachment->options !!}',
        '{{$this->questionAttachment->audioCanBePlayedAgain()}}'
    );

    player.on('loadeddata', ()=> {
        player.currentTime = parseFloat('{{ $this->getCurrentTime() }}');
    });

    document.addEventListener('pause-audio-player', ()=>{
        player.pause();
    });
</script>