@props(['label' => __('student.fill_in_test_code_from_teacher') ])

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
                      x-on:input="testCodeInput($refs.testCode_1)"

        />
        <x-input.text class="w-10 text-center test-code" type="number"
                      max="9" maxlength="1" wire:model.defer="testTakeCode.1"
                      x-ref="testCode_2"
                      x-on:input="testCodeInput($refs.testCode_2)"
                      x-on:keydown.backspace.prevent="testCodeBackspace($refs.testCode_2)"
        />
        <x-input.text class="w-10 text-center test-code" type="number"
                      max="9" maxlength="1" wire:model.defer="testTakeCode.2"
                      x-ref="testCode_3"
                      x-on:input="testCodeInput($refs.testCode_3)"
                      x-on:keydown.backspace.prevent="testCodeBackspace($refs.testCode_3)"
        />
        <div class="h-5 w-px bg-blue-grey"></div>
        <x-input.text class="w-10 text-center test-code" type="number"
                      max="9" maxlength="1" wire:model.defer="testTakeCode.3"
                      x-ref="testCode_4"
                      x-on:input="testCodeInput($refs.testCode_4)"
                      x-on:keydown.backspace.prevent="testCodeBackspace($refs.testCode_4)"
        />
        <x-input.text class="w-10 text-center test-code" type="number"
                      max="9" maxlength="1" wire:model.defer="testTakeCode.4"
                      x-ref="testCode_5"
                      x-on:input="testCodeInput($refs.testCode_5)"
                      x-on:keydown.backspace.prevent="testCodeBackspace($refs.testCode_5)"
        />
        <x-input.text class="w-10 text-center test-code" type="number"
                      max="9" maxlength="1" wire:model="testTakeCode.5"
                      x-ref="testCode_6"
                      x-on:input="testCodeInput($refs.testCode_6)"
                      x-on:keydown.backspace.prevent="testCodeBackspace($refs.testCode_6);"
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
        </script>
    @endpush
</div>