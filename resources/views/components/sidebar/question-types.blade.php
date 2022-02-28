<div class="flex flex-col divide-y-2">
    <span></span>
    <span class="note text-sm uppercase text-center py-1">{{ __('cms.open-questions') }}</span>
    @foreach($this->newQuestions['open'] as $question)
        <div class="add-question-card cursor-pointer py-4 px-6 flex space-x-4 items-center text-sm">
            <div>
                @if($question['sticker'] === 'question-open')
                    <x-stickers.question-open/>
                @elseif($question['sticker'] === 'question-completion')
                    <x-stickers.question-completion/>
                @elseif($question['sticker'] === 'question-drawing')
                    <x-stickers.question-drawing/>
                @endif
            </div>
            <div class="content flex flex-col flex-1 relative">
                <span class="bold text-base">{{ $question['name'] }}</span>
                <span class="note">{{ $question['description'] }}</span>
                <button class="absolute top-0 right-0">
                    <x-icon.plus/>
                </button>
            </div>
        </div>
    @endforeach

    <span class="note text-sm uppercase text-center py-1">{{ __('cms.closed-questions') }}</span>
    @foreach($this->newQuestions['closed'] as $question)
        <div class="add-question-card cursor-pointer py-4 px-6 flex space-x-4 items-center text-sm">
            <div>
                @if($question['sticker'] === 'question-multiple-choice')
                    <x-stickers.question-multiple-choice/>
                @elseif($question['sticker'] === 'question-matching')
                    <x-stickers.question-matching/>
                @elseif($question['sticker'] === 'question-classify')
                    <x-stickers.question-classify/>
                @elseif($question['sticker'] === 'question-ranking')
                    <x-stickers.question-ranking/>
                @elseif($question['sticker'] === 'question-true-false')
                    <x-stickers.question-true-false/>
                @elseif($question['sticker'] === 'question-selection')
                    <x-stickers.question-selection/>
                @elseif($question['sticker'] === 'question-arq')
                    <x-stickers.question-arq/>
                @endif
            </div>
            <div class="content flex flex-col flex-1 relative">
                <span class="bold text-base">{{ $question['name'] }}</span>
                <span class="note">{{ $question['description'] }}</span>
                <button class="absolute top-0 right-0">
                    <x-icon.plus/>
                </button>
            </div>
        </div>
    @endforeach

    <span class="note text-sm uppercase text-center py-1">{{ __('cms.extra') }}</span>
    @foreach($this->newQuestions['extra'] as $question)
        <div class="add-question-card cursor-pointer py-4 px-6 flex space-x-4 items-center text-sm">
            <div>
                <x-stickers.question-infoscreen/>
            </div>
            <div class="content flex flex-col flex-1 relative">
                <span class="bold text-base">{{ $question['name'] }}</span>
                <span class="note">{{ $question['description'] }}</span>
                <button class="absolute top-0 right-0">
                    <x-icon.plus/>
                </button>
            </div>
        </div>
    @endforeach
</div>