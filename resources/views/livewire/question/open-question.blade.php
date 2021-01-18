<div class="w-full">
    {{ get_class($question) }}
    {!!   $question->getQuestionHtml() !!} open question.blade
    <x-input.group  for="me" label="" class="w-full"><x-input.textarea name="name"></x-input.textarea></x-input.group>
</div>
