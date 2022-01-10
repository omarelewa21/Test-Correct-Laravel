<div class="flex items-center relative left-4"
     x-data="{score: @entangle($attributes->wire('model')), showError: false, hideTimer: null}"
     x-init="
     $watch('score', (value) => {
        if(parseInt(value) > 99) {
            score = 99;
            showError = true
        }
     })
     "
>
    <label>{{ __('Punten') }}</label>
    <button @click.prevent="score = parseInt(score) - 1"
            class="h-10 flex items-center bg-blue-grey base rounded-10 relative -right-4 pl-2 pr-6">
        <x-icon.min></x-icon.min>
    </button>

    <input type="number" max="99" class="form-input w-10 z-10 text-center" x-model="score" autocomplete="off">

    <button @click.prevent="score = parseInt(score) +1 " class="h-10 flex items-center bg-blue-grey base rounded-10 relative -left-4 pr-2 pl-6">
        <x-icon.plus></x-icon.plus>
    </button>

    <div x-cloak="" x-show="showError" class="text-allred text-sm absolute -bottom-px right-0">
        Score kan niet hoger zijn dan 99
    </div>
</div>
