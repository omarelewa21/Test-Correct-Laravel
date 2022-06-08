@props([
'label' => 'Label',
'options',
'buttonWidth' => '105px',
])
<div wire:ignore
     class="flex flex-col"
     x-id="['slider-button']"
     x-data="{
         buttonPosition: '0px',
         value: @entangle($attributes->wire('model')),
         sources: @js($options),
         init(){
            this.$el.querySelector('.group').firstElementChild.classList.add('text-primary');

            if(this.value !== '' && Object.keys(this.sources).includes(this.value)){
                this.activateButton(this.$el.querySelector('[data-id=\'' + this.value + '\' ').parentElement);
            } else {
                this.value = this.$el.querySelector('.group').firstElementChild.dataset.id;
            }
         },
         clickButton(target){
            this.activateButton(target);
            this.value = target.firstElementChild.dataset.id;
         },
         hoverButton(target){
            this.activateButton(target)
         },
         activateButton(target){
            this.resetButtons(target)
            this.buttonPosition = target.offsetLeft + 'px';
            target.firstElementChild.classList.add('text-primary');
         },
         resetButtons(target) {
            Array.from(target.parentElement.children).forEach(button => {
                button.firstElementChild.classList.remove('text-primary');
            });
         }
     }"
>
    <label :for="$id('slider-button')">
        {{$label}}
    </label>
    <div class="relative">
        <div :id="$id('slider-button')" class="flex">
            @foreach($options as $id => $button)
                <div style="width: {{$buttonWidth}}"
                     class="group flex items-center justify-center h-10 bg-off-white bold text-sysbase cursor-pointer border-blue-grey border-t border-b first:border-l last:border-r first:rounded-l-lg last:rounded-r-lg  "
                     @click="clickButton($el)"
                >
                    <span data-id="{{$id}}"
                          class="inline-flex justify-center w-full px-3 border-r border-blue-grey group-last:border-r-0 pointer-events-none"
                    >
                        {{$button}}
                    </span>
                </div>
            @endforeach
        </div>
        <div style="width: {{$buttonWidth}};"
             :style="{left: buttonPosition}"
             class="border-2 rounded-lg border-primary absolute h-10 bottom-0 transition-all ease-in-out duration-300 pointer-events-none"
        >
        </div>
    </div>
</div>