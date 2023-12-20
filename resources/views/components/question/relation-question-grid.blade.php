@props(['viewStruct'])

<div class="flex flex-wrap">
    <div class="question-relation-question-grid">
        @foreach($viewStruct[0] as $key => $data)
            <div class="flex">
                <input id="{{'relation-question-' . $data['wordId']}}" type="text"
                       wire:model="answerStruct.{{$data['wordId']}}" class="form-input"/>
                <span for="{{'relation-question-' . $data['wordId']}}" class="relation-question-label">
                @if($data['question_prefix'])
                    <span class="mr-1">{{ $data['question_prefix'] }}:</span>
                @endif
                <b>{{ $data['question'] }}</b>
            </span>
            </div>
        @endforeach
    </div>
    @isset($viewStruct[1])
    <div class="question-relation-question-grid">
        @foreach($viewStruct[1] as $key => $data)
            <div class="flex">
                <input id="{{'relation-question-' . $data['wordId']}}" type="text"
                       wire:model="answerStruct.{{$data['wordId']}}" class="form-input"/>
                <span for="{{'relation-question-' . $data['wordId']}}" class="relation-question-label">
                @if($data['question_prefix'])
                    <span class="mr-1">{{ $data['question_prefix'] }}:</span>
                @endif
                <b>{{ $data['question'] }}</b>
            </span>
            </div>
        @endforeach
    </div>
    @endisset
</div>