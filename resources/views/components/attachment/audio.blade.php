
<div class="flex flex-col w-full justify-center items-center bg-white space-y-3 rounded-10"
     x-data="{attachment: null}"
     x-init="
        attachment = '{{ $attachment->uuid }}'
             $refs.player.currentTime = {{ $this->getCurrentTime() }}"
>
    <div class="text-center">
        @if(!$attachment->audioCanBePlayedAgain())
            <h5>{{__('test_take.sound_clip_played')}}</h5>
        @elseif($attachment->audioOnlyPlayOnce() && !$attachment->audioIsPausable())
            <h5>{{__('test_take.only_playable_once_not_pausable')}}</h5>
        @elseif($attachment->audioOnlyPlayOnce())
            <h5>{{__('test_take.only_playable_once')}}</h5>
        @elseif(!$attachment->audioIsPausable())
            <h5>{{__('test_take.cannot_pause_sound_clip')}}</h5>
        @else
            <h5>{{__('test_take.sound_clip')}}</h5>
        @endif
        @if($this->timeout)
            <h5>{{ __('test_take.time_left_to_answer_after_closing_attachment', ['timeout' => $this->timeout]) }}</h5>
        @endif
    </div>
    <div>
        <audio id="player" src="{{ route('student.question-attachment-show', ['attachment' => $attachment, 'answer' => $this->answerId], false) }}"
               x-ref="player"
               x-on:play="@this.registerPlayStart()"
               @if($attachment->audioOnlyPlayOnce())
                    x-on:ended="@this.registerEndOfAudio($refs.player.currentTime,$refs.player.duration),@this.audioIsPlayedOnce(attachment);@this.closeAttachmentModal()"
               @elseif($attachment->hasAudioTimeout())
                    x-on:ended="@this.registerEndOfAudio($refs.player.currentTime,$refs.player.duration),@this.closeAttachmentModal()"
               @endif
        ></audio>
        <div class="flex justify-center">
            <button class="button primary-button
                    @if(!$attachment->audioCanBePlayedAgain()) cursor-default disabled @endif "
                    @if(!$attachment->audioCanBePlayedAgain()) disabled @endif
                    x-on:click.prevent="$refs.player.play()"
            >
                {{__('test_take.play')}}
            </button>
            @if($attachment->audioIsPausable())
                <button class="button secondary-button ml-2 pause_button"
                        x-on:click.prevent="$refs.player.pause(); $wire.audioStoreCurrentTime($refs.player.currentTime)"
                        @if(!$attachment->audioCanBePlayedAgain()) disabled @endif
                >
                    {{__('test_take.pause')}}
                </button>
            @endif
        </div>
    </div>
</div>

