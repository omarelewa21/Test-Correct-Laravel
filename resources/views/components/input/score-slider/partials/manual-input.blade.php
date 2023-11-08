<input @class(['h-10 score-slider-number-input items-center justify-center js-allow-for-wasd-navigation', $classes])
       x-model.number="score"
       type="number"
       max="{{$maxScore}}"
       min="0"
       onclick="this.select()"
       :step="halfPoints ? 0.5 : 1"
       x-ref="scoreInput"
       x-on:focusout="syncInput($el.value)"
       x-on:input="handleInvalidNumberInput(); setThumbOffset(document.querySelector('.score-slider-input'), score, maxScore)"
       x-on:keydown="if (!$event.target.value.match(/^[0-9]$/)) { $event.preventDefault();}"
       @if($focusInput) autofocus @endif
       @disabled($disabled)
>