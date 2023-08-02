@props(['label' => __('student.fill_in_test_code_from_teacher'), 'tab'])

<div class="flex flex-col space-y-2 pt-4 test-take-code-component">
    <span x-ref="textCodeLabel">{{ $label }}</span>
    <div class="flex items-center space-x-2">
        <x-input.group>
            <x-input.text class="w-12 text-center disabled" disabled value="AA"/>
        </x-input.group>
        <div class="h-5 w-px bg-blue-grey"></div>
        <x-input.text class="w-10 text-center test-code" type="number"
                      max="9" maxlength="1" wire:model.defer="testTakeCode.0"
                      x-ref="testCode_1"
                      data-focus-tab-error="{{$tab}}-invalid_test_code"
                      x-on:input="testCodeInput($el)"
                      x-on:paste.prevent="handlePaste($event, $el)"
                      x-on:focus="$el.select()"
        />
        <x-input.text class="w-10 text-center test-code" type="number"
                      max="9" maxlength="1" wire:model.defer="testTakeCode.1"
                      x-ref="testCode_2"
                      x-on:input="testCodeInput($el)"
                      x-on:keydown.backspace.prevent="testCodeBackspace($el)"
                      x-on:focus="$el.select()"
        />
        <x-input.text class="w-10 text-center test-code" type="number"
                      max="9" maxlength="1" wire:model.defer="testTakeCode.2"
                      x-ref="testCode_3"
                      x-on:input="testCodeInput($el)"
                      x-on:keydown.backspace.prevent="testCodeBackspace($el)"
                      x-on:focus="$el.select()"
        />
        <div class="h-5 w-px bg-blue-grey"></div>
        <x-input.text class="w-10 text-center test-code" type="number"
                      max="9" maxlength="1" wire:model.defer="testTakeCode.3"
                      x-ref="testCode_4"
                      x-on:input="testCodeInput($el)"
                      x-on:keydown.backspace.prevent="testCodeBackspace($el)"
                      x-on:focus="$el.select()"
        />
        <x-input.text class="w-10 text-center test-code" type="number"
                      max="9" maxlength="1" wire:model.defer="testTakeCode.4"
                      x-ref="testCode_5"
                      x-on:input="testCodeInput($el)"
                      x-on:keydown.backspace.prevent="testCodeBackspace($el)"
                      x-on:focus="$el.select()"
        />
        <x-input.text class="w-10 text-center test-code" type="number"
                      max="9" maxlength="1" wire:model="testTakeCode.5"
                      x-ref="testCode_6"
                      x-on:input="testCodeInput($el)"
                      x-on:keydown.backspace.prevent="testCodeBackspace($el);"
                      x-on:focus="$el.select()"
        />
    </div>
    @push('scripts')
        <script>
            function testCodeInput(element) {
                if (element.value.length > 1) {
                    element.value = element.value.slice(0, 1);
                }
                if (element === element.parentElement.lastElementChild) {
                    return;
                }
                if (!element.nextElementSibling.matches('.test-code') && element.value.length === 1) {
                    element.nextElementSibling.nextElementSibling.focus();
                    return;
                }
                if (element.value.length === 1) {
                    element.nextElementSibling.focus();
                }
            }

            function testCodeBackspace(element) {
                if (element.value.length > 0) {
                    element.value = '';
                    return;
                }

                let previousInput = element.previousElementSibling;
                if (!previousInput.matches('.test-code')) {
                    previousInput = previousInput.previousElementSibling;
                }
                previousInput.select();
            }

            function handlePaste(event, element) {
                if(element !== element.parentElement.querySelector('.test-code')) {
                    return;
                }

                let pasteData = event.clipboardData.getData('text');
                if(pasteData.length === 8 && pasteData.startsWith('AA')) {
                    pasteData = pasteData.slice(2);
                }
                if (pasteData.length === 6 && Number.isInteger(parseInt(pasteData))) {
                    let children = Array.from(element.parentElement.querySelectorAll('.test-code'));
                    let digits = (""+pasteData).split("");

                    children.forEach((child, index) => child.value = digits[index]);
                    children[children.length-1].focus();
                }
            }
        </script>
    @endpush
</div>