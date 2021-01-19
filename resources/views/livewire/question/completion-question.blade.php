<div>
<<<<<<< HEAD
    {!! $question !!}
=======
    {{ get_class($question) }}
    {!! $question->getQuestionHtml() !!}

    <span>Text vraag</span>
    <x-input.group label="" for="gatentekst">
        <x-input.select class="max-w-max">
{{--            @foreach($options as $key => $option)--}}
{{--                <option wire:key="{{ $option.'-'.$key }}" id="{{ $key }}">{{ $option }}</option>--}}
{{--            @endforeach--}}

            <option>Hallo</option>
            <option>Hallo</option>
            <option>HalloHalloHallo</option>
            <option>Hallo</option>
        </x-input.select>
    </x-input.group>
    <span>vervolg van de vraag</span>

    <x-input.group label="" for="gatentekst">
        <x-input.select class="max-w-max">
            {{--            @foreach($options as $key => $option)--}}
            {{--                <option wire:key="{{ $option.'-'.$key }}" id="{{ $key }}">{{ $option }}</option>--}}
            {{--            @endforeach--}}

            <option>Hallo</option>
            <option>Hallo</option>
            <option>HalloHalloHallo</option>
            <option>Hallo</option>
        </x-input.select>
    </x-input.group>
>>>>>>> 1222d83a893a9bf099e922362f3e6869e73037d5
</div>
