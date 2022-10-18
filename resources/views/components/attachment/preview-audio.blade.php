<div class="flex flex-col w-full justify-center items-center bg-white space-y-3 rounded-10">

    <div class="text-center w-3/4">
        @if(!$this->questionAttachment->audioCanBePlayedAgain())
            <h5>{{__('test_take.sound_clip_played')}}</h5>
        @elseif($this->questionAttachment->audioOnlyPlayOnce() && !$this->questionAttachment->audioIsPausable())
            <h5>{{__('test_take.only_playable_once_not_pausable')}}</h5>
        @elseif($this->questionAttachment->audioOnlyPlayOnce())
            <h5>{{__('test_take.only_playable_once')}}</h5>
        @elseif(!$this->questionAttachment->audioIsPausable())
            <h5>{{__('test_take.cannot_pause_sound_clip')}}</h5>
        @else
            <h5>{{__('test_take.sound_clip')}}</h5>
        @endif
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