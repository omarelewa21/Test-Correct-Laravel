<div id="question-bank"
     class="flex flex-col w-full min-h-full bg-lightGrey divide-y divide-secondary border-t border-secondary overflow-auto"
     x-data="{openTab: 1}"
>
    <div class="flex w-full ">
        <div class="w-full max-w-5xl mx-auto">
            <div class="flex w-full space-x-4">
                <div>
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 1">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 1 ? 'primary' : '' ">Menu knopje</span>
                        <span class="absolute w-full bottom-0" style="height: 3px" :class="openTab === 1 ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 2">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 2 ? 'primary' : '' ">Menu knopje</span>
                        <span class="absolute w-full bottom-0" style="height: 3px" :class="openTab === 2 ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 3">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 3 ? 'primary' : '' ">Menu knopje</span>
                        <span class="absolute w-full bottom-0" style="height: 3px" :class="openTab === 3 ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 4">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 4 ? 'primary' : '' ">Menu knopje</span>
                        <span class="absolute w-full bottom-0" style="height: 3px" :class="openTab === 4 ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="flex w-full">
        <div class="w-full max-w-5xl mx-auto divide-y divide-secondary">
            {{-- Filters--}}
            <div class="flex flex-col py-4">
                <div class="flex w-full">
                    <div class="relative w-full">
                        <x-input.text class="w-full"
                                      placeholder="Zoek..."
                        />
                        <x-icon.search class="absolute right-0 -top-2" />
                    </div>
                </div>
                <div class="flex w-full space-x-2">
                    <span class="flex flex-1 p-2 rounded-lg bg-offwhite border border-secondary">Filter</span>
                    <span class="flex flex-1 p-2 rounded-lg bg-offwhite border border-secondary">Filter</span>
                    <span class="flex flex-1 p-2 rounded-lg bg-offwhite border border-secondary">Filter</span>
                    <span class="flex flex-1 p-2 rounded-lg bg-offwhite border border-secondary">Filter</span>
                    <span class="flex flex-1 p-2 rounded-lg bg-offwhite border border-secondary">Filter</span>
                    <span class="flex flex-1 p-2 rounded-lg bg-offwhite border border-secondary">Filter</span>
                    <span class="flex flex-1 p-2 rounded-lg bg-offwhite border border-secondary">Filter</span>
                    <span class="flex flex-1 p-2 rounded-lg bg-offwhite border border-secondary">Filter</span>
                </div>

            </div>

            {{-- Content --}}
            <div class="flex flex-col py-4">
                <div class="flex">
                    <span class="note text-sm">167 resultaten</span>
                </div>
                <x-grid class="mt-4">
                    @foreach([1,2,3,4,5] as $key)
                        <x-grid.card>
                            <x-slot name="title">Chuck brisket flank salami turducken shank bacon drumstick.  Bacon doner shankle cow, ribeye prosciutto andouille tri-tip biltong.  Porch<</x-slot>
                            <x-slot name="baseSubject">Basesubject</x-slot>
                            <x-slot name="subject">Subject</x-slot>
                            <x-slot name="updatedAt">{{ Carbon\Carbon::now()->format('d/m/\'y') }}</x-slot>
                            <x-slot name="author">Auteur 1 Auteur 1 Auteur 1</x-slot>
                        </x-grid.card>
                    @endforeach
                </x-grid>
            </div>
        </div>
    </div>
</div>