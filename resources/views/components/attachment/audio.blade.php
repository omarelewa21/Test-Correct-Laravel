<div class="flex flex-col w-full justify-center items-center bg-white space-y-3 rounded-10" x-data>
    <div class="text-center">
        @if($attachment->audioOnlyPlayOnce())
            <h5>{{__('test_take.only_playable_once')}}</h5>
        @elseif(!$attachment->audioIsPausable())
            <h5>{{__('test_take.cannot_pause_sound_clip')}}</h5>
        @else
            <h5>{{__('test_take.sound_clip')}}</h5>
        @endif
    </div>
    <div>
        <audio src="{{ route('student.question-attachment-show', $attachment->getKey()) }}" x-ref="player"></audio>
        <div class="flex justify-center">
            <button class="button primary-button" x-on:click.prevent="$refs.player.play()"> {{__('test_take.play')}}</button>
            @if($attachment->audioIsPausable() && !$attachment->audioOnlyPlayOnce())
                <button class="button secondary-button ml-2" x-on:click.prevent="$refs.player.pause()">{{__('test_take.pause')}}</button>
            @endif
        </div>
    </div>
</div>