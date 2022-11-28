@props([
'label' => null,
'options',
'buttonWidth' => '105px',
'disabled' => false,
'disabledStyling' => false,
'useNamedSlots' => false,
])
<div wire:ignore
     {{ $attributes->merge(['class' => ''])->except('wire:model') }}
     x-id="['slider-button']"
     x-data="sliderToggle(
         @entangle($attributes->wire('model')),
         @js($options),
     )"
>
    @if($label)
        <label :for="$id('slider-button')">
            {{$label}}
        </label>
    @endif
    <div class="relative">
        <div :id="$id('slider-button')" class="flex note">
            @foreach($options as $id => $button)
                <div style="width: {{$buttonWidth}}"
                     class="slider-option hover:text-primary group flex items-center justify-center h-10 bold note border-blue-grey border-t border-b first:border-l last:border-r first:rounded-l-lg last:rounded-r-lg
                     {{ $disabledStyling ? 'bg-white opacity-70' : 'bg-off-white cursor-pointer'}}
                     "
                     @if(!$disabled) @click="clickButton($el)" @endif
                >
                    <span data-id="{{$id}}"
                          class="inline-flex justify-center w-full px-3 border-r border-blue-grey group-last:border-r-0 pointer-events-none"
                    >
                        @if($useNamedSlots) {{$$button}} @else {{ $button }} @endif
                    </span>
                </div>
            @endforeach
        </div>
        <div :id="$id('slider-button')+'-handle'"
             style="width: @js($buttonWidth);"
             :style="{left: buttonPosition, width: buttonWidth}"
             class="border-2 rounded-lg border-primary absolute h-10 bottom-0 transition-all ease-in-out duration-300 pointer-events-none slider-button-handle hidden"
        >
        </div>
    </div>
</div>