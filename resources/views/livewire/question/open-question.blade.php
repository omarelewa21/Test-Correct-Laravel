<div class="w-full" x-data="{count:0}" x-init="count = $refs.countme.value.length">
    {!!   $question->getQuestionHtml() !!} open question.blade
    <x-input.group for="me" label="" class="w-full">
        <x-input.textarea wire:model="answer" name="name" maxlength="280" x-ref="countme" x-on:keyup="count = $refs.countme.value.length" ></x-input.textarea>
    </x-input.group>
    <span x-html="count"></span>  / <span x-html="$refs.countme.maxLength"></span>
</div>
