<div class="w-full">
    @if($question->subtype == 'short')
        <div
            x-data="{count:0}"
            x-init="count = $refs.countme.value.length"
            x-on:livewire-refresh.window="count = $refs.countme.value.length"
        >
            {!!   $question->getQuestionHtml() !!}

            <x-input.group for="me" label="" class="w-full">
                <x-input.textarea wire:model="answer" name="name" maxlength="280" x-ref="countme"
                                  x-on:keyup="count = $refs.countme.value.length"></x-input.textarea>
            </x-input.group>
            <span x-html="count"></span> / <span x-html="$refs.countme.maxLength"></span>
        </div>
    @else
        <div class="w-full">
            <x-input.group for="me" label="" class="w-full">
                <x-input.textarea wire:model="answer" name="name"></x-input.textarea>
            </x-input.group>

        </div>
    @endif
</div>
