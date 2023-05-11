@props(['id', 'maxWidth', 'showCancelButton'=> true, 'url' => 'https://support.test-correct.nl'])

@php
    $id = $id ?? md5($attributes->wire('model'));

    $maxWidth = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '6xl' =>  'sm:max-w-6xl',
        '7xl' =>  'sm:max-w-7xl',
    ][$maxWidth ?? '2xl'];
@endphp

<div
        x-data="{
        show: @entangle($attributes->wire('model')),
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input, textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'

            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
        showCancelButton: true
    }"
        x-init="$watch('show', value => {
            if (value) {
                document.body.classList.add('overflow-y-hidden');
            } else {
                document.body.classList.remove('overflow-y-hidden');
            }
        });
        showCancelButton = {{ $showCancelButton }}
                "
        x-on:close.stop="showCancelButton ? show = false : ''"
        x-on:keydown.escape.window="showCancelButton ? show = false : ''"
        x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
        x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
        x-show="show"
        id="{{ $id }}"
        class="jetstream-modal fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50 disable-swipe-navigation"
        style="display: none;"
>
    <div x-show="show" class="fixed inset-0 transform transition-all" x-on:click="showCancelButton ? show = false : '';"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div x-show="show"
         class="flex flex-col py-5 px-7 bg-white rounded-10 overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto h-[90vh]"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        <div class="px-2.5 flex justify-between items-center">
            <h2>Kennisbank</h2>
            <x-icon.close @click="show = false;"/>
        </div>
        <div class="divider mb-5 mt-2.5"></div>
        <div class="px-2.5 body1 mb-5 flex-1">
            <iframe src="{{ $url }}" frameborder="0" class="w-full flex flex-1 h-full"></iframe>
        </div>
        <div class="flex justify-end px-2.5">
            <div class="space-x-3">
                <x-button.primary @click="show = false;"><span>{{ __('drawing-modal.Sluiten') }}</span></x-button.primary>
            </div>
        </div>
    </div>
</div>
