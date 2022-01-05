<div class="flex mb-4 flex items-center" x-data="{score: @entangle($attributes->wire('model'))}">
    <label>{{ __('Punten') }}</label>
    <button @click.prevent="score = parseInt(score) - 1"
            class="h-10 flex items-center bg-blue-grey base rounded-10 relative -right-4 pl-2 pr-6">
        <x-icon.min></x-icon.min>
    </button>

    <input class="form-input w-10 z-10 text-center" x-model="score" autocomplete="off">

    <button @click.prevent="score = parseInt(score) +1 " class="h-10 flex items-center bg-blue-grey base rounded-10 relative -left-4 pr-2 pl-6">
        <x-icon.plus></x-icon.plus>

    </button>
</div>
