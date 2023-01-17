<div class="drawer flex z-[20]"
     selid="co-learning-teacher-drawer"
     x-init="
        collapse = window.innerWidth < 1000;
     "
     x-data="{collapse: false}"
>
    <div class="flex flex-col w-full justify-between h-[calc(100vh-70px)] drawer-width">
        <div class="flex flex-col ">

            <div class="flex justify-between drawer-content-head border-b border-bluegrey">
                <div><span class="bold">aanwezig 24</span>/30</div>
                <div><span class="bold">vraag 1</span>/5</div>
            </div>

            <div class="drawer-content divide-y divide-bluegrey overflow-auto" >

                @foreach($this->testTake->testParticipants as $testParticipant)
                    <x-partials.sidebar.co-learning-teacher.student-info-container
                            :testParticipant="$testParticipant"
                    ></x-partials.sidebar.co-learning-teacher.student-info-container>
                @endforeach
            </div>
        </div>

        <div class="fixed bottom-0 drawer-footer flex justify-between items-center footer-shadow">
            {{-- todo disabled when at the first question --}}
            <x-button.text-button wire:click.prevent="goToPreviousQuestion" :disabled="$this->atFirstQuestion">
                <x-icon.arrow-left/>
                <span class="ml-2">{{ __('co-learning.previous') }}</span>
            </x-button.text-button>

            <x-button.primary class="px-4 flex-0" wire:click.prevent="goToNextQuestion">
                <span class="mr-2">{{ __('co-learning.next') }}</span>
                <x-icon.arrow/>
            </x-button.primary>
        </div>
    </div>
    <!-- Well begun is half done. - Aristotle -->
</div>