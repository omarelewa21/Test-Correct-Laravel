<div class="flex w-full h-full min-h-full relative my-16">
    <div class="flex flex-col min-h-full h-full mx-8 space-y-8 w-full relative">

        <x-partials.styleguide-card title="Standard buttons sm (small)" items-per-row="7">
            <x-button.primary wire:click="count"><span>Primary</span></x-button.primary>
            <x-button.secondary wire:click="count"><span>Secondary</span></x-button.secondary>
            <x-button.cta wire:click="count"><span>Cta</span></x-button.cta>
            <x-button.student wire:click="count"><span>Student</span></x-button.student>
            <x-button.text size="sm" wire:click="count"><span>Text-button</span></x-button.text>
            <x-button.default size="sm" wire:click="count"><span>Default</span></x-button.default>
            <h5>default &lt;button&gt;</h5>

            <x-button.primary type="link" href="javascript: window.Livewire.emit('count');"><span>Primary</span></x-button.primary>
            <x-button.secondary type="link" href="javascript: window.Livewire.emit('count');"><span>Secondary</span>
            </x-button.secondary>
            <x-button.cta type="link" href="javascript: window.Livewire.emit('count');"><span>Cta</span></x-button.cta>
            <x-button.student type="link" href="javascript: window.Livewire.emit('count');"><span>Student</span></x-button.student>
            <x-button.text size="sm" type="link" href="javascript: window.Livewire.emit('count');"><span>Text-button</span></x-button.text>
            <x-button.default size="sm" type="link" href="javascript: window.Livewire.emit('count');"><span>Default</span></x-button.default>

            <h5>link &lt;a&gt;</h5>

            <x-button.primary disabled wire:click="count"><span>Primary</span></x-button.primary>
            <x-button.secondary disabled wire:click="count"><span>Secondary</span></x-button.secondary>
            <x-button.cta disabled wire:click="count"><span>Cta</span></x-button.cta>
            <x-button.student disabled wire:click="count"><span>Student</span></x-button.student>
            <x-button.text size="sm" disabled wire:click="count"><span>Text-button</span></x-button.text>
            <x-button.default size="sm" disabled wire:click="count"><span>Default</span></x-button.default>

            <h5>disabled buttons</h5>
        </x-partials.styleguide-card>

        <x-partials.styleguide-card title="Standard buttons md (medium)" items-per-row="7">
            <x-button.primary size="md" wire:click="count"><span>Primary</span></x-button.primary>
            <x-button.secondary size="md" wire:click="count"><span>Secondary</span></x-button.secondary>
            <x-button.cta size="md" wire:click="count"><span>Cta</span></x-button.cta>
            <x-button.student size="md" wire:click="count"><span>Student</span></x-button.student>
            <x-button.text size="md" wire:click="count"><span>Text-button</span></x-button.text> {{-- text-button is medium by default --}}
            <x-button.default size="md" wire:click="count"><span>Default</span></x-button.default>
            <h5>default &lt;button&gt;</h5>

            <x-button.primary size="md" type="link" href="javascript: window.Livewire.emit('count');"><span>Primary</span>
            </x-button.primary>
            <x-button.secondary size="md" type="link" href="javascript: window.Livewire.emit('count');"><span>Secondary</span>
            </x-button.secondary>
            <x-button.cta size="md" type="link" href="javascript: window.Livewire.emit('count');"><span>Cta</span></x-button.cta>
            <x-button.student size="md" type="link" href="javascript: window.Livewire.emit('count');"><span>Student</span>
            </x-button.student>
            <x-button.text size="md" type="link" href="javascript: window.Livewire.emit('count');"><span>Text-button</span></x-button.text> {{-- text-button is medium by default --}}
            <x-button.default size="md" type="link" href="javascript: window.Livewire.emit('count');"><span>Default</span></x-button.default> {{-- text-button is medium by default --}}
            <h5>link &lt;a&gt;</h5>

            <x-button.primary size="md" disabled wire:click="count"><span>Primary</span></x-button.primary>
            <x-button.secondary size="md" disabled wire:click="count"><span>Secondary</span></x-button.secondary>
            <x-button.cta size="md" disabled wire:click="count"><span>Cta</span></x-button.cta>
            <x-button.student size="md" disabled wire:click="count"><span>Student</span></x-button.student>
            <x-button.text size="md" disabled wire:click="count"><span>Text-button</span></x-button.text> {{-- text-button is medium by default --}}
            <x-button.default size="md" disabled wire:click="count"><span>Default</span></x-button.default> {{-- text-button is medium by default --}}
            <h5>disabled buttons</h5>
        </x-partials.styleguide-card>

        <x-partials.styleguide-card title="Standard buttons lg (large)" items-per-row="7">


            <x-button.primary size="lg" wire:click="count"><span>Primary</span></x-button.primary>
            <x-button.secondary size="lg" wire:click="count"><span>Secondary</span></x-button.secondary>
            <x-button.cta size="lg" wire:click="count"><span>Cta</span></x-button.cta>
            <x-button.student size="lg" wire:click="count"><span>Student</span></x-button.student>
            <x-button.text size="lg" wire:click="count"><span>Text-button</span></x-button.text>
            <x-button.default size="lg" wire:click="count"><span>Default</span></x-button.default>
            <h5>default &lt;button&gt;</h5>

            <x-button.primary size="lg" type="link" href="javascript: window.Livewire.emit('count');"><span>Primary</span>
            </x-button.primary>
            <x-button.secondary size="lg" type="link" href="javascript: window.Livewire.emit('count');"><span>Secondary</span>
            </x-button.secondary>
            <x-button.cta size="lg" type="link" href="javascript: window.Livewire.emit('count');"><span>Cta</span></x-button.cta>
            <x-button.student size="lg" type="link" href="javascript: window.Livewire.emit('count');"><span>Student</span>
            </x-button.student>
            <x-button.text size="lg" type="link" href="javascript: window.Livewire.emit('count');"><span>Text-button</span></x-button.text>
            <x-button.default size="lg" type="link" href="javascript: window.Livewire.emit('count');"><span>Default</span></x-button.default>
            <h5>link &lt;a&gt;</h5>

            <x-button.primary size="lg" disabled wire:click="count"><span>Primary</span></x-button.primary>
            <x-button.secondary size="lg" disabled wire:click="count"><span>Secondary</span></x-button.secondary>
            <x-button.cta size="lg" disabled wire:click="count"><span>Cta</span></x-button.cta>
            <x-button.student size="lg" disabled wire:click="count"><span>Student</span></x-button.student>
            <x-button.text size="lg" disabled wire:click="count"><span>Text-button</span></x-button.text>
            <x-button.default size="lg" disabled wire:click="count"><span>Default</span></x-button.default>
            <h5>disabled buttons</h5>

        </x-partials.styleguide-card>

        <x-partials.styleguide-card title="Icon buttons sm (small)" items-per-row="6">

            <x-button.icon color="primary" wire:click="count">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon color="secondary" wire:click="count">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon color="cta" wire:click="count">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon color="student" wire:click="count">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon color="text" wire:click="count" size="sm">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon> {{-- text-button is medium by default --}}
            <h5>Icon &lt;button&gt;</h5>

            <x-button.icon type="link" color="primary" href="javascript: window.Livewire.emit('count');">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon type="link" color="secondary" href="javascript: window.Livewire.emit('count');">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon type="link" color="cta" href="javascript: window.Livewire.emit('count');">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon type="link" color="student" href="javascript: window.Livewire.emit('count');">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon type="link" color="text" href="javascript: window.Livewire.emit('count');" size="sm">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon> {{-- text-button is medium by default --}}
            <h5>Icon &lt;a&gt;</h5>

            <x-button.icon disabled color="primary" wire:click="count">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon disabled color="secondary" wire:click="count">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon disabled color="cta" wire:click="count">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon disabled color="student" wire:click="count">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon disabled color="text" wire:click="count" size="sm">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon> {{-- text-button is medium by default --}}
            <h5>Icon &lt;button&gt; disabled</h5>
        </x-partials.styleguide-card>

        <x-partials.styleguide-card title="Icon buttons md (medium)" items-per-row="6">

            <x-button.icon color="primary" wire:click="count" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon color="secondary" wire:click="count" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon color="cta" wire:click="count" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon color="student" wire:click="count" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon color="text" wire:click="count" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <h5>Icon &lt;button&gt;</h5>

            <x-button.icon type="link" color="primary" href="javascript: window.Livewire.emit('count');" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon type="link" color="secondary" href="javascript: window.Livewire.emit('count');" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon type="link" color="cta" href="javascript: window.Livewire.emit('count');" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon type="link" color="student" href="javascript: window.Livewire.emit('count');" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon type="link" color="text" href="javascript: window.Livewire.emit('count');" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <h5>Icon &lt;a&gt;</h5>

            <x-button.icon disabled color="primary" wire:click="count" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon disabled color="secondary" wire:click="count" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon disabled color="cta" wire:click="count" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon disabled color="student" wire:click="count" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon disabled color="text" wire:click="count" size="md">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <h5>Icon &lt;button&gt; disabled</h5>
        </x-partials.styleguide-card>

        <x-partials.styleguide-card title="Icon buttons lg (large)" items-per-row="6">

            <x-button.icon color="primary" wire:click="count" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon color="secondary" wire:click="count" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon color="cta" wire:click="count" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon color="student" wire:click="count" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon color="text" wire:click="count" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <h5>Icon buttons default</h5>

            <x-button.icon type="link" color="primary" href="javascript: window.Livewire.emit('count');" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon type="link" color="secondary" href="javascript: window.Livewire.emit('count');" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon type="link" color="cta" href="javascript: window.Livewire.emit('count');" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon type="link" color="student" href="javascript: window.Livewire.emit('count');" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon type="link" color="text" href="javascript: window.Livewire.emit('count');" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <h5>Icon &lt;a&gt;</h5>

            <x-button.icon disabled color="primary" wire:click="count" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon disabled color="secondary" wire:click="count" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon disabled color="cta" wire:click="count" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon disabled color="student" wire:click="count" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <x-button.icon disabled color="text" wire:click="count" size="lg">
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.icon>
            <h5>Icon &lt;button&gt; disabled</h5>
        </x-partials.styleguide-card>

        <x-partials.styleguide-card title="buttons sm (small) icon left/right" items-per-row="6">
            <x-button.primary wire:click="count"><x-icon.copy/><span>Primary</span></x-button.primary>
            <x-button.secondary wire:click="count"><x-icon.copy/><span>Secondary</span></x-button.secondary>
            <x-button.cta wire:click="count"><x-icon.copy/><span>Cta</span></x-button.cta>
            <x-button.student wire:click="count"><x-icon.copy/><span>Student</span></x-button.student>
            <x-button.text size="sm" wire:click="count"><x-icon.copy/><span>Text-button</span></x-button.text>
            <h5>default &lt;button&gt;</h5>

            <x-button.primary type="link" href="javascript: window.Livewire.emit('count');"><x-icon.copy/><span>Primary</span></x-button.primary>
            <x-button.secondary type="link" href="javascript: window.Livewire.emit('count');"><x-icon.copy/><span>Secondary</span>
            </x-button.secondary>
            <x-button.cta type="link" href="javascript: window.Livewire.emit('count');"><x-icon.copy/><span>Cta</span></x-button.cta>
            <x-button.student type="link" href="javascript: window.Livewire.emit('count');"><x-icon.copy/><span>Student</span></x-button.student>
            <x-button.text size="sm" type="link" href="javascript: window.Livewire.emit('count');"><x-icon.copy/><span>Text-button</span>
            </x-button.text>
            <h5>link &lt;a&gt;</h5>

            <x-button.primary disabled wire:click="count"><x-icon.copy/><span>Primary</span></x-button.primary>
            <x-button.secondary disabled wire:click="count"><x-icon.copy/><span>Secondary</span></x-button.secondary>
            <x-button.cta disabled wire:click="count"><x-icon.copy/><span>Cta</span></x-button.cta>
            <x-button.student disabled wire:click="count"><x-icon.copy/><span>Student</span></x-button.student>
            <x-button.text size="sm" disabled wire:click="count"><x-icon.copy/><span>Text-button</span></x-button.text>
            <h5>disabled &lt;button&gt;</h5>


            <x-button.primary wire:click="count"><span>Primary</span><x-icon.copy/></x-button.primary>
            <x-button.secondary wire:click="count"><span>Secondary</span><x-icon.copy/></x-button.secondary>
            <x-button.cta wire:click="count"><span>Cta</span><x-icon.copy/></x-button.cta>
            <x-button.student wire:click="count"><span>Student</span><x-icon.copy/></x-button.student>
            <x-button.text size="sm" wire:click="count"><span>Text-button</span><x-icon.copy/></x-button.text>
            <h5>default &lt;button&gt;</h5>

            <x-button.primary type="link" href="javascript: window.Livewire.emit('count');"><span>Primary</span><x-icon.copy/></x-button.primary>
            <x-button.secondary type="link" href="javascript: window.Livewire.emit('count');"><span>Secondary</span><x-icon.copy/>
            </x-button.secondary>
            <x-button.cta type="link" href="javascript: window.Livewire.emit('count');"><span>Cta</span><x-icon.copy/></x-button.cta>
            <x-button.student type="link" href="javascript: window.Livewire.emit('count');"><span>Student</span><x-icon.copy/></x-button.student>
            <x-button.text size="sm" type="link" href="javascript: window.Livewire.emit('count');"><span>Text-button</span><x-icon.copy/>
            </x-button.text>
            <h5>link &lt;a&gt;</h5>

            <x-button.primary disabled wire:click="count"><span>Primary</span><x-icon.copy/></x-button.primary>
            <x-button.secondary disabled wire:click="count"><span>Secondary</span><x-icon.copy/></x-button.secondary>
            <x-button.cta disabled wire:click="count"><span>Cta</span><x-icon.copy/></x-button.cta>
            <x-button.student disabled wire:click="count"><span>Student</span><x-icon.copy/></x-button.student>
            <x-button.text size="sm" disabled wire:click="count"><span>Text-button</span><x-icon.copy/></x-button.text>
            <h5>disabled buttons</h5>
        </x-partials.styleguide-card>


        <x-partials.styleguide-card title="Rotated icon buttons" items-per-row="9">

            <x-button.icon rotate-icon="0" selid="rotate-0" wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="45" selid="rotate-45" wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="90" selid="rotate-90" wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="135" selid="rotate-135" wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="180" selid="rotate-180" wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="225" selid="rotate-225" wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="270" selid="rotate-270" wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="315" selid="rotate-315" wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <h5>Responsive/currentColor icon &lt;button&gt;</h5>

            {{-- link/anchor buttons don't have a working focus-state without a valid href attribute --}}
            <x-button.icon rotate-icon="0" selid="rotate-link-0" type="link" href="javascript: window.Livewire.emit('count');">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="45" selid="rotate-link-45" type="link" href="javascript: window.Livewire.emit('count');">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="90" selid="rotate-link-90" type="link" href="javascript: window.Livewire.emit('count');">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="135" selid="rotate-link-135" type="link" href="javascript: window.Livewire.emit('count');">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="180" selid="rotate-link-180" type="link" href="javascript: window.Livewire.emit('count');">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="225" selid="rotate-link-225" type="link" href="javascript: window.Livewire.emit('count');">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="270" selid="rotate-link-270" type="link" href="javascript: window.Livewire.emit('count');">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="315" selid="rotate-link-315" type="link" href="javascript: window.Livewire.emit('count');">
                <x-icon.arrow/>
            </x-button.icon>
            <h5>Responsive/currentColor icon &lt;a&gt;</h5>

            <x-button.icon rotate-icon="0" selid="rotate-disabled-0" disabled wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="45" selid="rotate-disabled-45" disabled wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="90" selid="rotate-disabled-90" disabled wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="135" selid="rotate-disabled-135" disabled wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="180" selid="rotate-disabled-180" disabled wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="225" selid="rotate-disabled-225" disabled wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="270" selid="rotate-disabled-270" disabled wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <x-button.icon rotate-icon="315" selid="rotate-disabled-315" disabled wire:click="count">
                <x-icon.arrow/>
            </x-button.icon>
            <h5>Responsive/currentColor icon disabled </h5>

        </x-partials.styleguide-card>

        <x-partials.styleguide-card title="text buttons (default)" items-per-row="5">
            <x-button.text wire:click="count" size="sm"><span>Txt btn</span></x-button.text>
            <x-button.text wire:click="count" size="sm"><x-icon.attachment/><span>Txt btn</span></x-button.text>
            <x-button.text wire:click="count" size="sm"><span>Txt btn</span><x-icon.attachment/></x-button.text>
            <x-button.text wire:click="count" size="sm"><x-icon.attachment/></x-button.text>
            <h5>sm text &lt;button&gt;</h5>
            <x-button.text size="sm" type="link" href="javascript: window.Livewire.emit('count');"><span>Txt btn</span></x-button.text>
            <x-button.text size="sm" type="link" href="javascript: window.Livewire.emit('count');"><x-icon.attachment/><span>Txt btn</span></x-button.text>
            <x-button.text size="sm" type="link" href="javascript: window.Livewire.emit('count');"><span>Txt btn</span><x-icon.attachment/></x-button.text>
            <x-button.text size="sm" type="link" href="javascript: window.Livewire.emit('count');"><x-icon.attachment/></x-button.text>
            <h5>sm text &lt;a&gt;</h5>
            <x-button.text wire:click="count" size="sm" disabled><span>Txt btn</span></x-button.text>
            <x-button.text wire:click="count" size="sm" disabled><x-icon.attachment/><span>Txt btn</span></x-button.text>
            <x-button.text wire:click="count" size="sm" disabled><span>Txt btn</span><x-icon.attachment/></x-button.text>
            <x-button.text wire:click="count" size="sm" disabled><x-icon.attachment/></x-button.text>
            <h5>sm text &lt;button&gt; disabled</h5>

            <x-button.text wire:click="count" size="md"><span>Txt btn</span></x-button.text>
            <x-button.text wire:click="count" size="md"><x-icon.attachment/><span>Txt btn</span></x-button.text>
            <x-button.text wire:click="count" size="md"><span>Txt btn</span><x-icon.attachment/></x-button.text>
            <x-button.text wire:click="count" size="md"><x-icon.attachment/></x-button.text>
            <h5>md text &lt;button&gt;</h5>
            <x-button.text size="md" type="link" href="javascript: window.Livewire.emit('count');"><span>Txt btn</span></x-button.text>
            <x-button.text size="md" type="link" href="javascript: window.Livewire.emit('count');"><x-icon.attachment/><span>Txt btn</span></x-button.text>
            <x-button.text size="md" type="link" href="javascript: window.Livewire.emit('count');"><span>Txt btn</span><x-icon.attachment/></x-button.text>
            <x-button.text size="md" type="link" href="javascript: window.Livewire.emit('count');"><x-icon.attachment/></x-button.text>
            <h5>md text &lt;a&gt;</h5>
            <x-button.text wire:click="count" size="md" disabled><span>Txt btn</span></x-button.text>
            <x-button.text wire:click="count" size="md" disabled><x-icon.attachment/><span>Txt btn</span></x-button.text>
            <x-button.text wire:click="count" size="md" disabled><span>Txt btn</span><x-icon.attachment/></x-button.text>
            <x-button.text wire:click="count" size="md" disabled><x-icon.attachment/></x-button.text>
            <h5>md text &lt;button&gt; disabled</h5>

        </x-partials.styleguide-card>

        <x-partials.styleguide-card title="text buttons (white variant)" items-per-row="5" accordionClass="!bg-primary">
            <x-button.text :white="true" size="sm" wire:click="count"><span>Txt btn</span></x-button.text>
            <x-button.text :white="true" size="sm" wire:click="count"><x-icon.attachment/><span>Txt btn</span></x-button.text>
            <x-button.text :white="true" size="sm" wire:click="count"><span>Txt btn</span><x-icon.attachment/></x-button.text>
            <x-button.text :white="true" size="sm" wire:click="count"><x-icon.attachment/></x-button.text>
            <h5 class="text-white">sm text &lt;button&gt;</h5>
            <x-button.text :white="true" size="sm" type="link" href="javascript: window.Livewire.emit('count');"><span>Txt btn</span></x-button.text>
            <x-button.text :white="true" size="sm" type="link" href="javascript: window.Livewire.emit('count');"><x-icon.attachment/><span>Txt btn</span></x-button.text>
            <x-button.text :white="true" size="sm" type="link" href="javascript: window.Livewire.emit('count');"><span>Txt btn</span><x-icon.attachment/></x-button.text>
            <x-button.text :white="true" size="sm" type="link" href="javascript: window.Livewire.emit('count');"><x-icon.attachment/></x-button.text>
            <h5 class="text-white">sm text &lt;a&gt;</h5>

            <x-button.text :white="true" size="sm" wire:click="count" disabled><span>Txt btn</span></x-button.text>
            <x-button.text :white="true" size="sm" wire:click="count" disabled><x-icon.attachment/><span>Txt btn</span></x-button.text>
            <x-button.text :white="true" size="sm" wire:click="count" disabled><span>Txt btn</span><x-icon.attachment/></x-button.text>
            <x-button.text :white="true" size="sm" wire:click="count" disabled><x-icon.attachment/></x-button.text>
            <h5 class="text-white">sm text &lt;button&gt; disabled</h5>

            <x-button.text :white="true" size="md" wire:click="count"><span>Txt btn</span></x-button.text>
            <x-button.text :white="true" size="md" wire:click="count"><x-icon.attachment/><span>Txt btn</span></x-button.text>
            <x-button.text :white="true" size="md" wire:click="count"><span>Txt btn</span><x-icon.attachment/></x-button.text>
            <x-button.text :white="true" size="md" wire:click="count"><x-icon.attachment/></x-button.text>
            <h5 class="text-white">md text &lt;button&gt;</h5>

            <x-button.text :white="true" size="md" type="link" href="javascript: window.Livewire.emit('count');"><span>Txt btn</span></x-button.text>
            <x-button.text :white="true" size="md" type="link" href="javascript: window.Livewire.emit('count');"><x-icon.attachment/><span>Txt btn</span></x-button.text>
            <x-button.text :white="true" size="md" type="link" href="javascript: window.Livewire.emit('count');"><span>Txt btn</span><x-icon.attachment/></x-button.text>
            <x-button.text :white="true" size="md" type="link" href="javascript: window.Livewire.emit('count');"><x-icon.attachment/></x-button.text>
            <h5 class="text-white">md text &lt;a&gt;</h5>
            <x-button.text :white="true" size="md" wire:click="count" disabled><span>Txt btn</span></x-button.text>
            <x-button.text :white="true" size="md" wire:click="count" disabled><x-icon.attachment/><span>Txt btn</span></x-button.text>
            <x-button.text :white="true" size="md" wire:click="count" disabled><span>Txt btn</span><x-icon.attachment/></x-button.text>
            <x-button.text :white="true" size="md" wire:click="count" disabled><x-icon.attachment/></x-button.text>
            <h5 class="text-white">md text &lt;button&gt; disabled</h5>
        </x-partials.styleguide-card>

        <x-partials.styleguide-card title="text button unique hover state" items-per-row="3">
            <x-button.text :with-hover="true" size="sm"><span>Text button</span></x-button.text>
            <x-button.text :with-hover="true" size="sm" disabled><span>Text button</span></x-button.text>
            <h5>unique hover implementation for text button</h5>
        </x-partials.styleguide-card>

    </div>

    <div class="fixed bottom-0 right-0 h-12 border-2 rounded-10 border-primary bg-student flex place-content-center space-x-2 px-4">
            <span class="h-full flex flex-wrap place-content-center space-x-1">
                <span>&lt;button&gt;</span>
                <span class="inline text-fuchsia-500"> Wire:click </span>
                <span>counter:</span>
            </span>
        <span class="h-full flex flex-wrap place-content-center">
            {{ $counter }}
            </span>
    </div>
</div>
