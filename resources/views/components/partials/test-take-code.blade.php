<div class="flex flex-col space-y-2 pt-4 test-take-code-component">
    <span x-ref="textCodeLabel">Voer de toetscode in die de docent toont op het scherm</span>
    <div class="flex items-center space-x-2">
        <x-input.group>
            <x-input.text class="w-12 text-center disabled" disabled value="AA"/>
        </x-input.group>
        <div class="h-5 w-px bg-blue-grey"></div>
        <x-input.text class="w-10 text-center text-code" type="number" min="1"
                      max="9" maxlength="1" wire:model="testTakeCode.0"
                      x-ref="testCode_1"
                      x-on:input="if($refs.testCode_1.value.length > 1) $refs.testCode_1.value = $refs.testCode_1.value.slice(0, 1); $refs.testCode_2.focus()"/>
        <x-input.text class="w-10 text-center text-code" type="number" min="1"
                      max="9" maxlength="1" wire:model="testTakeCode.1"
                      x-ref="testCode_2"
                      x-on:input="if($refs.testCode_2.value.length > 1) $refs.testCode_2.value = $refs.testCode_2.value.slice(0, 1); $refs.testCode_3.focus()"/>
        <x-input.text class="w-10 text-center text-code" type="number" min="1"
                      max="9" maxlength="1" wire:model="testTakeCode.2"
                      x-ref="testCode_3"
                      x-on:input="if($refs.testCode_3.value.length > 1) $refs.testCode_3.value = $refs.testCode_3.value.slice(0, 1); $refs.testCode_4.focus()"/>
        <div class="h-5 w-px bg-blue-grey"></div>
        <x-input.text class="w-10 text-center text-code" type="number" min="1"
                      max="9" maxlength="1" wire:model="testTakeCode.3"
                      x-ref="testCode_4"
                      x-on:input="if($refs.testCode_4.value.length > 1) $refs.testCode_4.value = $refs.testCode_4.value.slice(0, 1); $refs.testCode_5.focus()"/>
        <x-input.text class="w-10 text-center text-code" type="number" min="1"
                      max="9" maxlength="1" wire:model="testTakeCode.4"
                      x-ref="testCode_5"
                      x-on:input="if($refs.testCode_5.value.length > 1) $refs.testCode_5.value = $refs.testCode_5.value.slice(0, 1); $refs.testCode_6.focus()"/>
        <x-input.text class="w-10 text-center text-code" type="number" min="1"
                      max="9" maxlength="1" wire:model="testTakeCode.5"
                      x-ref="testCode_6"
                      x-on:input="if($refs.testCode_6.value.length > 1) $refs.testCode_6.value = $refs.testCode_6.value.slice(0, 1)"/>
    </div>
</div>