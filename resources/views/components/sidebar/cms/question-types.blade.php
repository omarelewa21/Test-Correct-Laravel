<div class="flex flex-col divide-y-2">
    <span></span>

    @foreach($questionGroups as $group => $title)
        <span class="note text-sm uppercase text-center py-1">{{ $title }}</span>

        @foreach($questionTypes[$group] as $question)
            <div class="add-question-card cursor-pointer py-4 px-6 flex space-x-4 items-center text-sm"
                 x-data="cmsQuestionTypeButton(@js($question['type']),@js($question['subtype']),@js($confirmRelationQuestion && $question['type'] === 'RelationQuestion'))"
                 x-on:confirmed-modal.window="confirmed($event.detail?.key)"
                 x-on:click="clickAction"
                 selid="add-{{$question['type']}}-{{$question['subtype']}}-question-btn"
            >
                <div>
                    <x-dynamic-component :component="'stickers.'.$question['sticker']" />
                </div>
                <div class="content flex flex-col flex-1 relative">
                    <span class="bold text-base">{{ $question['name'] }}</span>
                    <span class="note">{{ $question['description'] }}</span>
                    <button class="absolute top-0 right-0">
                        <x-icon.plus />
                    </button>
                </div>
            </div>
        @endforeach
    @endforeach
</div>
