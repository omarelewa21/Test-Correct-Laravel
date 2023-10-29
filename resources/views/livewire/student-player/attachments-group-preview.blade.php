<div>
    @if ($this->group)
        <h6 wire:ignore class="inline-flex"> {{ __('cms.group-question') }} : {{ $this->group->name }}</h6>
        <div class="flex flex-wrap">
            <x-attachment.student-buttons-container :group="$this->group" :blockAttachments="false" />
        </div>
        <div class="mb-5 questionContainer" questionHtml wire:ignore>{!! $this->group->question->converted_question_html !!}</div>
    @endif
</div>
