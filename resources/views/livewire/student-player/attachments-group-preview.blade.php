<div>
    <h6 wire:ignore class="inline-flex"> {{ __('cms.group-question') }} : {{ $this->group->name }}</h6>
    <div class="flex flex-1 flex-col">
        <div class="flex flex-wrap">
            <x-attachment.student-buttons-container :question="$question" :group="$this->group" :blockAttachments="$this->blockAttachments" />
        </div>
        <div class="mb-5 questionContainer" questionHtml wire:ignore>{!! $this->group->question->converted_question_html !!}</div>
    </div>
</div>
