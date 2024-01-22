<input @class(['h-10 score-slider-number-input items-center justify-center js-allow-for-wasd-navigation', $classes])
       x-model.number="score"
       type="number"
       max="{{$maxScore}}"
       min="0"
       onclick="this.select()"
       :step="halfPoints ? (decimalScore ? 0.1 : 0.5) : 1"
       x-ref="scoreInput"
       x-on:focusout="syncInput($el.value)"
       x-on:input="handleInvalidNumberInput(); setThumbOffset(document.querySelector('.score-slider-input'), score, maxScore)"
       x-on:keydown="if ([65,83,87,68].includes($event.keyCode)) { $event.preventDefault(); }"
       @if($focusInput) autofocus @endif
       @disabled($disabled)
>