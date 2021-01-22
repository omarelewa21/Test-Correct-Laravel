
<div class="w-full space-y-3">
    <div>
        <span>Lees de stellingen en selecteer de juiste antwoordoptie in de lijst</span>
    </div>
    <div class="flex flex-row space-x-10">
        <div class="flex flex-1 flex-col space-y-6">
            {!! $question->getQuestionHtml() !!}
        </div>
        <div class="flex flex-1">
            <table>
                <thead>
                    <th>Optie</th>
                    <th>Stelling 1</th>
                    <th>Stelling 2</th>
                    <th>Reden</th>
                </thead>
                <tbody>
                @foreach( $question->multipleChoiceQuestionAnswers as $loopCount => $link)
                    <tr>

                        <td><input
                                wire:model="answer"
                                id="link{{ $link->id }}"
                                name="Question_{{ $question->id }}"
                                type="radio"
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                value="{{ $loopCount }}"
                            >A</td>
                        <td>{{ __($this->arqStructure[$loopCount][0]) }}</td>
                        <td>{{ __($this->arqStructure[$loopCount][1]) }}</td>
                        <td>{{ __($this->arqStructure[$loopCount][2]) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
