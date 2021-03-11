<div class="question-indicator w-full">
    <div class="flex flex-wrap" x-data=""
         x-init="setTimeout( function() { $dispatch('current-updated', {'current': {{ $this->q }} })}, 1)">
        @foreach($nav as $key => $q)

            <div class="flex flex-col mb-3 relative
                    @if($q['group']['id'] != 0 && !$loop->last && $nav[$key+1]['group']['id'] != 0 && $nav[$key+1]['group']['id'] === $q['group']['id'])
                        number-divider group
                    @endif
                    @if (!$q['answered'] && ($q['group']['closed'] || $q['closed']))
                            incomplete
                    @elseif($q['answered'])
                                        complete
                    @endif
                    ">
                <section wire:key="nav_{{$key}}"
                         class="question-number rounded-full text-center cursor-pointer
                                {!! $key === ($this->q - 1) ? 'active' : ''!!}
                                @if (!$q['answered'] && ($q['group']['closed'] || $q['closed']))
                                    incomplete
                                @elseif($q['answered'])
                                    complete
                                @endif
                                 "
                         wire:click="goToQuestion({{ 1+$key}})"

                         {{--                 wire:click="$set('q',{{ 1+$key}})"--}}
                         {{--                 x-on:click="$dispatch('current-updated', {'current': {{ 1+$key }} })"--}}

                         x-on:current-question-answered.window="$wire.updateQuestionIndicatorColor()"
                >
                    <span class="align-middle">{{ ++$key }}</span>
                </section>
                <div class="h-max-h-4 ml-2 mt-1 flex">
                    @if($q['closeable'] && !$q['closed'])
                        <x-icon.unlocked/>
                    @elseif($q['closed'])
                        <x-icon.locked/>
                    @endif
                </div>
            </div>

            @if($q['group']['closeable'] && $this->lastQuestionInGroup[$q['group']['id']] === $key)
                <div class="mr-3">
                        @if($q['group']['closed'])
                            <x-icon.locked/>
                        @else
                            <x-icon.unlocked/>
                        @endif
                </div>
            @endif
        @endforeach
        <div class="flex space-x-6 ml-auto min-w-max justify-end items-center">
            @if(Auth::user()->text2speech)
                <x-button.text-button href="#" wire:click="">
                    <x-icon.audio/>
                    <span>{{ __('test_take.speak') }}</span>
                </x-button.text-button>
            @endif
            <x-button.text-button wire:click="toOverview({{ $this->q }})">
                <x-icon.preview/>
                <span>{{ __('test_take.overview') }}</span>
            </x-button.text-button>

        </div>
    </div>
</div>
