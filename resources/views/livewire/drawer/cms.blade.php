<div class="drawer flex z-[1]"
     x-data="{collapse: false}"
     x-init="collapse = window.innerWidth < 1000"
     :class="{'collapsed': collapse}"
>
    <div id="sidebar-content" class="flex flex-col">
        <div class="collapse-toggle vertical white absolute -right-4 top-10 z-10 cursor-pointer"
             @click="collapse = !collapse"
        >
            <button class="relative"
                    :class="{'rotate-svg-180 -left-px': !collapse}"
            >
                <x-icon.chevron class="-top-px relative"/>
            </button>
        </div>

        <div id="sidebar-carousel-container" x-ref="slidercont">
            <div class="p-2.5 flex flex-col border border-allred">
                <x-button.text-button @click="$refs.slidercont.scrollTo({left: 300, behavior: 'smooth'})">
                    <span>Nieuwe vraag</span>
                    <x-icon.plus/>
                </x-button.text-button>

                @foreach($this->testQuestionUuids as $uuid)
                    <div class="@if($this->testQuestionId === $uuid) border border-primary @endif">
                    <x-button.text-button wire:click="showQuestion('{{ $uuid }}')"
                                          @click="$dispatch('question-change', {old: '{{ $this->testQuestionId }}', new: '{{ $uuid }}' })"
                                          class=""
                    >
                        <span>Vraag {{ $loop->iteration }}</span>
                    </x-button.text-button>
                    </div>
                @endforeach()
            </div>

            <div class="flex flex-col border border-cta">
                <x-button.text-button class="rotate-svg-180" @click="$refs.slidercont.scrollTo({left: 0, behavior: 'smooth'})">
                    <x-icon.arrow/>
                    <span>Terug</span>
                </x-button.text-button>

                <x-sidebar.question-types/>
            </div>
        </div>
    </div>
</div>