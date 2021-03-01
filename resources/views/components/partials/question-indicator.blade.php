<div class="question-indicator w-full">
    <div class="flex flex-wrap" x-data=""
         x-init="setTimeout( function() { $dispatch('current-updated', {'current': {{ $this->q }} })}, 1)">
        @foreach($nav as $key => $q)
            <div class="flex flex-col mb-3 relative @if(!$loop->last) number-divider @endif {!! $q['answered'] ? 'complete' : ''!!}">
                <section wire:key="nav_{{$key}}"
                         class="question-number rounded-full text-center cursor-pointer
                        {!! $key === ($this->q - 1) ? 'active' : ''!!}
                         {!! $q['answered'] ? 'complete' : ''!!}
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
        @endforeach
        <div class="flex space-x-6 ml-auto min-w-max justify-end items-center">
            <x-button.text-button href="#" wire:click="">
                <x-icon.audio/>
                <span>{{ __('test_take.speak') }}</span>
            </x-button.text-button>
            <x-button.text-button wire:click="toOverview({{ $this->q }})">
                <x-icon.preview/>
                <span>{{ __('test_take.overview') }}</span>
            </x-button.text-button>

        </div>
    </div>
</div>
