<?php
$mainQuestion = $this->content->getQuestionInstance();
?>
<div class="w-full">
    <div class="flex mb-12">
        <div class="question-indicator w-full">
            <div class="flex flex-wrap">
                <div class="question-number rounded-full text-center complete">
                    <span class="align-middle">1</span>
                </div>
                <div class="question-number rounded-full text-center complete">
                    <span class="align-middle">2</span>
                </div>
                <div class="question-number rounded-full text-center active">
                    <span class="align-middle">3</span>
                </div>
                <div class="question-number rounded-full text-center pending">
                    <span class="align-middle">4</span>
                </div>
                <div class="question-number rounded-full text-center complete">
                    <span class="align-middle">5</span>
                </div>

                <section class="flex space-x-6 ml-auto min-w-max justify-end">
                    <a href="#" class="text-button">
                        <x-icon.audio/>
                        <span class="ml-1.5">Lees voor</span></a>
                    <a href="#" class="text-button">
                        <x-icon.preview/>
                        <span class="ml-1.5">Bekijk antwoorden</span></a>
                </section>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-10 p-8 sm:p-10 content-section">
        <div class="question-title border-bottom mb-6">

            <h1 class="inline-block mr-6">{{ strip_tags($mainQuestion->question) }}</h1>
            <h4 class="inline-block">{{$mainQuestion->score}}pt</h4>
        </div>
        <div>
            <div class="space-y-3">
                <label for="cars">Choose a car:</label>

                <select class="form-input" name="cars" id="cars">
                    <option value="volvo">Volvo</option>
                    <option value="saab">Saab</option>
                    <option value="mercedes">Mercedes</option>
                    <option value="audi">Audi</option>
                </select>
            </div>
        </div>
        {{--            @foreach($this->content->multipleChoiceQuestionAnswers as $answers)--}}
        {{--                <div>--}}
        {{--                    <label class="inline-flex items-center">--}}
        {{--                        <input type="checkbox" value="{{ $answers->id }}" class="checked:bg-red">--}}
        {{--                        <span class="ml-3 text-sm body1 base">{{$answers->answer}}</span>--}}
        {{--                    </label>--}}
        {{--                </div>--}}
        {{--            @endforeach--}}
    </div>
</div>
</div>