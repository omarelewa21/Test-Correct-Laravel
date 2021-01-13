<?php
$mainQuestion = $this->content->getQuestionInstance();
?>
<div class="w-full">
    <div class="flex mb-12">
        <x-partials.question-indicator :questions="$testQuestions"></x-partials.question-indicator>

    </div>

    <div class="bg-white rounded-10 p-8 sm:p-10 content-section">
        <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
            <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
                <span class="align-middle">5</span>
            </div>
            <h1 class="inline-block ml-2 mr-6">{{ strip_tags($mainQuestion->question) }}</h1>
            <h4 class="inline-blocke">{{$mainQuestion->score}}pt</h4>
        </div>
        <div class="flex flex-wrap">
            <x-input.group label="Input form" for="input">
                <x-input.text type="text" id="input" name="input"></x-input.text>
            </x-input.group>

            <x-input.group label="TextArea2" class="w-full" for="textarea2">
                <x-input.textarea name="textarea" id="textarea2"></x-input.textarea>
            </x-input.group>

            <x-input.group label="" for="select">
                <x-input.select name="select" placeholder="Selecteer">
                    @foreach($this->content->multipleChoiceQuestionAnswers as $answers)
                        <option value="answer-{{$answers->id}}">{{$answers->answer}}</option>
                    @endforeach
                </x-input.select>
            </x-input.group>

            <x-input.group label="MultipleChoice antwoorden" class="w-full" for="multiple">
                @foreach($this->content->multipleChoiceQuestionAnswers as $answers)
                    <div>
                        <input type="checkbox" value="{{ $answers->id }}" wire:model="selected"
                               wire:key="checkbox-{{ $answers->id}}">
                        <span class="ml-3 text-sm body1 base">{{$answers->answer}}</span>
                    </div>
                @endforeach
            </x-input.group>
        </div>

    </div>
</div>
</div>