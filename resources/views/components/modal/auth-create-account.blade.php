@props(['id', 'maxWidth'])

@php
    $id = $id ?? md5($attributes->wire('model'));

    $maxWidth = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
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
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 }
    }"
        x-init="$watch('show', value => {
            if (value) {
                document.body.classList.add('overflow-y-hidden');
            } else {
                document.body.classList.remove('overflow-y-hidden');
            }
        });
        "
        x-on:close.stop="show = false"
        x-on:keydown.escape.window="show = false"
        x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
        x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
        x-show="show"
        id="{{ $id }}"
        class="jetstream-modal fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
        style="display: none;"
>
    <div x-show="show" class="fixed inset-0 transform transition-all" x-on:click="show = false"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div x-show="show"
         class="flex flex-col py-5 px-7 bg-white rounded-10 overflow-hidden shadow-xl transform translate-y-1/2 transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        <div class="px-2.5">
            <h2>{{ __('auth.who_are_you') }}</h2>
        </div>
        <div class="divider mb-5 mt-2.5"></div>
        <div class="flex flex-1 h-full w-full px-2.5 body1 mb-5 space-x-2.5 text-center">
            <div class="flex flex-col p-8 items-center flex-1 border-2 h-full rounded-10 cursor-pointer transition-all relative
                @if($this->authModalRoleType === 'teacher') primary border-primary bg-off-white @else mid-grey border-blue-grey @endif"
                 wire:click="setAuthModalRoleType('teacher')"
            >
                <x-icon.man/>
                <span class="body2 mt-4 mb-2 @if($this->authModalRoleType === 'teacher') primary bold @else base @endif">{{ __('student.teacher') }}</span>
                <span class="mid-grey text-sm">{{ __('auth.modal_teacher_text') }}</span>
                @if($this->authModalRoleType === 'teacher')
                    <x-icon.checkmark-circle class="absolute top-2 right-2 overflow-visible"/>
                @endif
            </div>
            <div class="flex flex-col p-8 items-center flex-1 border-2 h-full rounded-10 cursor-pointer transition-all relative
                 @if($this->authModalRoleType === 'student') primary border-primary bg-off-white @else mid-grey border-blue-grey @endif"
                 wire:click="setAuthModalRoleType('student')"
            >
                <x-icon.gender-neutral/>
                <span class="body2 mt-4 mb-2 @if($this->authModalRoleType === 'student') primary bold @else base @endif">{{ __('auth.student_pupil') }}</span>
                <span class="mid-grey text-sm">{{ __('auth.modal_student_text') }}</span>
                @if($this->authModalRoleType === 'student')
                    <x-icon.checkmark-circle class="absolute top-2 right-2 overflow-visible"/>
                @endif
            </div>
        </div>
        <div class="flex justify-end px-2.5">
            <div class="space-x-3">
                <x-button.text @click="show = false" class="rotate-svg-180">
                    <span class="text-base">{{ __('auth.cancel') }}</span>
                </x-button.text>
                @if(filled($this->authModalRoleType))
                    <x-button.primary size="md" wire:click="createAccountRedirect">
                        <span>{{ __('auth.continue') }}</span>
                    </x-button.primary>
                @else
                    <x-button.primary size="md" disabled  wire:click="createAccountRedirect">
                        <span>{{ __('auth.continue') }}</span>
                    </x-button.primary>
                @endif
            </div>
        </div>
    </div>
</div>