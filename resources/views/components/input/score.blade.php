<div class="flex items-center relative left-4"
     x-data="{score: @entangle($attributes->wire('model'))}"
     x-init="$watch('score', value =>  {if(value < 0) { score = 0 }})"
>
    <label>{{ __('cms.Punten') }}</label>
    <button @click.prevent="score = parseInt(score) - 1"
            class="h-10 flex items-center bg-blue-grey base rounded-10 relative -right-4 pl-2 pr-6">
        <x-icon.min></x-icon.min>
    </button>

    <input :title="score"
           type="number"
           class="form-input z-[1] w-10 min-w-[40px] text-center"
           x-model="score"
           autocomplete="off"
           :style="'width:' + (30 + 10*score.toString().length) +'px'"
           @keypress="[',','.'].includes($event.key) ? $event.preventDefault() : ''"
    >

    <button @click.prevent="score = parseInt(score) +1 " class="h-10 flex items-center bg-blue-grey base rounded-10 relative -left-4 pr-2 pl-6">
        <x-icon.plus></x-icon.plus>
    </button>
</div>
