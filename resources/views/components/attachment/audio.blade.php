<div class="flex flex-col w-full justify-center items-center bg-white space-y-3 rounded-10"
     x-data="{attachment: null}"
     x-init="
        attachment = {{ $attachment->getKey() }}
             $refs.player.currentTime = {{ $attachment->audioHasCurrentTime() }}"
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
    </div>
    <div>
        <audio id="player" src="{{ route('student.question-attachment-show', $attachment->getKey()) }}"
               x-ref="player"
               @if($attachment->audioOnlyPlayOnce())
                    x-on:ended="@this.audioIsPlayedOnce(attachment);"
               @endif
        ></audio>
        <div class="flex justify-center">
            <button class="button primary-button
                    @if(!$attachment->audioCanBePlayedAgain()) cursor-default disabled @endif "
                    @if(!$attachment->audioCanBePlayedAgain()) disabled @endif
                    x-on:click.prevent="$refs.player.play(), $wire.set('pressedPlay', true)"
            >
                {{__('test_take.play')}}
            </button>
            @if($attachment->audioIsPausable())
                <button class="button secondary-button ml-2"
                        x-on:click.prevent="$refs.player.pause(); $wire.audioStoreCurrentTime(attachment, $refs.player.currentTime)"
                        @if(!$attachment->audioCanBePlayedAgain()) disabled @endif
                >
                    {{__('test_take.pause')}}
                </button>
            @endif
        </div>
    </div>
</div>