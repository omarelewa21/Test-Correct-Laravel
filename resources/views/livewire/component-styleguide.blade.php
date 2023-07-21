<div class="flex flex-col w-full min-h-full h-full mx-4 ">

    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
    <h1 class="mt-4 mb-2">Default buttons sm (small)</h1>
    <div class="justify-between grid grid-cols-6 gap-2 w-fit">
        <x-button.primary><span>Primary</span></x-button.primary>
        <x-button.secondary><span>Secondary</span></x-button.secondary>
        <x-button.cta><span>Cta</span></x-button.cta>
        <x-button.student><span>Student</span></x-button.student>
        <x-button.text-button size="sm"><span>Text-button</span></x-button.text-button>
        <h5>default buttons</h5>

        <x-button.primary disabled><span>Primary</span></x-button.primary>
        <x-button.secondary disabled><span>Secondary</span></x-button.secondary>
        <x-button.cta disabled><span>Cta</span></x-button.cta>
        <x-button.student disabled><span>Student</span></x-button.student>
        <x-button.text-button size="sm" disabled><span>Text-button</span></x-button.text-button>
        <h5>disabled buttons</h5>

        <x-button.primary type="link" href="#" ><span>Primary</span></x-button.primary>
        <x-button.secondary type="link" href="#"><span>Secondary</span></x-button.secondary>
        <x-button.cta type="link" href="#"><span>Cta</span></x-button.cta>
        <x-button.student type="link" href="#"><span>Student</span></x-button.student>
        <x-button.text-button size="sm" type="link" href="#"><span>Text-button</span></x-button.text-button>
        <h5>link / anchor tag</h5>

    </div>
    <h1 class="mt-4 mb-2">Default buttons md (medium)</h1>
    <div class="justify-between grid grid-cols-6 gap-2 w-fit">
        <x-button.primary size="md"><span>Primary</span></x-button.primary>
        <x-button.secondary size="md"><span>Secondary</span></x-button.secondary>
        <x-button.cta size="md"><span>Cta</span></x-button.cta>
        <x-button.student size="md"><span>Student</span></x-button.student>
        <x-button.text-button size="md"><span>Text-button</span></x-button.text-button>
        <h5>default buttons</h5>

        <x-button.primary size="md" disabled><span>Primary</span></x-button.primary>
        <x-button.secondary size="md" disabled><span>Secondary</span></x-button.secondary>
        <x-button.cta size="md" disabled><span>Cta</span></x-button.cta>
        <x-button.student size="md" disabled><span>Student</span></x-button.student>
        <x-button.text-button size="md" disabled><span>Text-button</span></x-button.text-button>
        <h5>disabled buttons</h5>

        <x-button.primary size="md" type="link" href="#" ><span>Primary</span></x-button.primary>
        <x-button.secondary size="md" type="link" href="#"><span>Secondary</span></x-button.secondary>
        <x-button.cta size="md" type="link" href="#"><span>Cta</span></x-button.cta>
        <x-button.student size="md" type="link" href="#"><span>Student</span></x-button.student>
        <x-button.text-button size="md" type="link" href="#"><span>Text-button</span></x-button.text-button>
        <h5>link / anchor tag</h5>

    </div>



    <h1 class="mt-4 mb-2">Icon buttons sm (small)</h1>
    <div class="justify-between grid grid-cols-6 gap-2 w-fit">
        <x-button.icon color="primary"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon color="secondary"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon color="cta"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon color="student"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon color="text-button"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <h5>Icon buttons default</h5>

        <x-button.icon disabled color="primary"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon disabled color="secondary"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon disabled color="cta"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon disabled color="student"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon disabled color="text-button"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <h5>Icon buttons default</h5>

        <x-button.icon type="link" color="primary" href="#"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon type="link" color="secondary" href="#"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon type="link" color="cta" href="#"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon type="link" color="student" href="#"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon type="link" color="text-button" href="#"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <h5>Icon buttons anchor tag</h5>


    </div>

    <h1 class="mt-4 mb-2">Icon button vs primary with extra styles</h1>
    <div class="justify-between grid grid-cols-6 gap-2 w-fit">
        <x-button.icon><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.primary class="w-10 h-10 p-0 "><x-icon.checkmark></x-icon.checkmark></x-button.primary> {{-- primary button edited to be an icon button --}}

    </div>

    <h1 class="mt-4 mb-2">refactoring buttons to one default parent</h1>
    <div class="justify-between grid grid-cols-6 gap-2 w-fit">
        <x-button.icon rotate-icon="45"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon rotate-icon="90"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon rotate-icon="180"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon rotate-icon="270" selid="selidtest"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon type="link" href="#" selid="selidtest"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
        <x-button.icon disabled="true"><x-icon.checkmark></x-icon.checkmark></x-button.icon>  {{-- icon button --}}
    </div>

    <h1 class="mt-4 mb-2">refactoring text button</h1>
    <div class="justify-between grid grid-cols-6 gap-2 w-fit">
        <x-button.text-button :with-hover="true" size="sm" >yes yes</x-button.text-button>
    </div>

</div>
