<div x-data="{}"
     class="space-y-4"
     x-on:answer-feedback-focus-feedback-editor.window="toggleFeedbackAccordion('add-feedback', true)"
     x-on:answer-feedback-show-comments.window="toggleFeedbackAccordion('given-feedback', true)"
>

{{$accordionOneContent}}

{{$accordionTwoContent}}

</div>