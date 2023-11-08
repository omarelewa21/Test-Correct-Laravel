<div>
    @if ($this->group)
        <h4 wire:ignore class="inline-flex font-bold mb-4"> {{ __('cms.group-question') }} : {{ $this->group->name }}</h4>
        <div class="flex flex-wrap">
            <x-attachment.student-buttons-container :group="$this->group" :blockAttachments="false" />
        </div>
        <div class="mb-5 questionContainer" questionHtml wire:ignore>{!! $this->group->question->converted_question_html !!}</div>
    @endif
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
</div>
