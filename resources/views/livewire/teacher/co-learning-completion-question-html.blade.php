<span x-data="{
    getInputWidth: function(el){
        const minWidth = 120;
        let maxWidth = el.parentNode.closest('div').offsetWidth;
        maxWidth = maxWidth > 1000 ? 1000 : maxWidth;
        if(el.scrollWidth > maxWidth) return maxWidth + 'px'
        if(el.value.length == 0) return minWidth + 'px'
        return el.value.length + 4 + 'ch'
    }
}">
    <input @if ($context === 'student') wire:model.lazy="answer.{{$tag_id}}" @endif
        x-on:contextmenu="$event.preventDefault()"
        spellcheck="false"
        value="{!! $answer !!}"
        autocorrect="off"
        autocapitalize="none"
        class="form-input mb-2 truncate text-center overflow-ellipsis"
        type="text"
        id="{{ 'answer_' . $tag_id . '_' . $question->getKey() }}"
        x-ref="{{ 'comp_answer_' . $tag_id }}"
        {!! $events !!}
        :style="{width: getInputWidth($el)}"
        wire:key="{{'comp_answer_' . $tag_id}}"
        @keyup="$el.style.width = getInputWidth($el)"
        @resize.window="$el.style.width = getInputWidth($el)"
    />
    {!! $rsSpan !!}
</span>