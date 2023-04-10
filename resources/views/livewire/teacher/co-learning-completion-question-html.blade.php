<span x-data="{
    minWidth: 120,
    maxWidth: 1000,
    getInputWidth: function(el){
        if(el.scrollWidth > this.maxWidth) return this.maxWidth + 'px'
        if(el.value.length == 0) return this.minWidth + 'px'
        return el.value.length + 4 + 'ch'
    }
}">
    <input x-on:contextmenu="$event.preventDefault()" spellcheck="false"
        value="{!! $answer !!}"
        autocorrect="off"
        autocapitalize="none"
        class="form-input mb-2 truncate text-center overflow-ellipsis"
        type="text"
        id="{{ 'answer_' . $tag_id . '_' . $question->getKey() }}"
        x-ref="{{ 'comp_answer_' . $tag_id }}"
        {{ $events }}
        :style="{width: getInputWidth($el)}"
        wire:key=" {{'comp_answer_' . $tag_id}}"
        @input="$el.style.width = getInputWidth($el)"
    />
    {{ $rsSpan }}
</span>