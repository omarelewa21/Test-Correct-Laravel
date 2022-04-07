<div id="question-bank"
     class="flex flex-col relative w-full min-h-full bg-lightGrey border-t border-secondary overflow-auto"
     x-data="{openTab: 1, checkedCount: 0, loading: false}"
     @checked="$event.detail ? checkedCount += 1 : checkedCount -= 1"

>
    <div class="flex w-full border-b border-secondary">
        <div class="w-full max-w-5xl mx-auto">
            <div class="flex w-full space-x-4">
                <div>
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 1">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 1 ? 'primary' : '' ">Menu knopje</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 1 ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 2">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 2 ? 'primary' : '' ">Menu knopje</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 2 ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 3">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 3 ? 'primary' : '' ">Menu knopje</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 3 ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 4">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 4 ? 'primary' : '' ">Menu knopje</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 4 ? 'bg-primary' : 'bg-transparent' "></span>
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
                                      wire:model="filters.search"
                        />
                        <x-icon.search class="absolute right-0 -top-2"/>
                    </div>
                </div>
                <div class="flex w-full space-x-2">
                    <x-input.choices-select :multiple="false" :options="$this->nameFilter"/>
                </div>

            </div>

            {{-- Content --}}
            <div class="flex flex-col py-4" style="min-height: 500px">
                <div class="flex">
                    <span class="note text-sm">167 resultaten</span>
                </div>
                <x-grid x-show="loading" class="mt-4">
                    <x-grid.loading-card/>
                    <x-grid.loading-card/>
                    <x-grid.loading-card/>
                    <x-grid.loading-card/>
                    <x-grid.loading-card/>
                </x-grid>
                <x-grid class="mt-4" x-show="!loading">

                    @foreach($this->questions as $question)
                        <x-grid.question-card :question="$question"/>
                    @endforeach
                </x-grid>
            </div>
        </div>
    </div>
    <div class="sticky h-0 bottom-20 ml-auto mr-4">
        <div class="flex justify-end mb-2">
            <span class="relative text-sm text-white rounded-full flex items-center justify-center main-shadow"
                  :class="checkedCount > 0 ? 'bg-primary' : 'bg-midgrey'"
                  style="min-width: 30px; height: 30px"
            >
                <span class="inline-flex -ml-px mt-px" x-text="checkedCount">0</span>
            </span>
        </div>
        <x-button.cta class="main-shadow" @click="loading = !loading; $root.classList.toggle('loading')">
            <x-icon.checkmark/>
            <span>{{ __('cms.Toevoegen') }} toggle loading state voor testing</span>
        </x-button.cta>
    </div>
</div>