<div id="question-bank"
     class="flex flex-col relative w-full min-h-full bg-lightGrey border-t border-secondary overflow-auto"
     x-data="{openTab: 1, checkedCount: 0, loading: false, inGroup: @entangle('inGroup')}"
     x-init="$watch('$store.questionBank.inGroup', value => inGroup = value);"
     @checked="$event.detail ? checkedCount += 1 : checkedCount -= 1"
     @question-added.window="Notify.notify('Vraag toegevoegd!')"
     @question-removed.window="Notify.notify('Vraag verwijderd!')"

>
    <div class="flex w-full border-b border-secondary">
        <div class="w-full max-w-5xl mx-auto">
            <div class="flex w-full space-x-4">
                <div>
                    <div class="flex relative hover:text-primary cursor-pointer"
                         @click="openTab = 1"
                         wire:click="setSource('personal')"
                    >
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 1 ? 'primary' : '' ">Persoonlijk</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 1 ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    {{--                    <div class="flex relative text-midgrey cursor-default">--}}
                    <div class="flex relative hover:text-primary cursor-pointer"
                         @click="openTab = 2"
                         wire:click="setSource('school')"
                    >
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]"
                              :class="openTab === 2 ? 'primary' : '' ">School</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 2 ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    <div class="flex relative text-midgrey cursor-default">
                        {{--                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 3">--}}
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 3 ? 'primary' : '' ">Nationaal</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 3 ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    <div class="flex relative text-midgrey cursor-default">
                        {{--                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 4">--}}
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 4 ? 'primary' : '' ">Examens</span>
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
                <div class="flex w-full my-2">
                    <div class="relative w-full">
                        <x-input.text class="w-full"
                                      placeholder="Zoek..."
                                      wire:model.300ms="filters.search"
                        />
                        <x-icon.search class="absolute right-0 -top-2"/>
                    </div>
                </div>
                <div class="flex flex-wrap w-full space-x-2">
                    <x-input.choices-select :multiple="true"
                                            :options="$this->subjects"
                                            :withSearch="true"
                                            placeholderText="Vak"
                                            wire:model="filters.subject_id"
                                            filterContainer="questionbank-active-filters"
                    />
                    <x-input.choices-select :multiple="true"
                                            :options="$this->educationLevelYear"
                                            :withSearch="true"
                                            placeholderText="Leerjaar"
                                            wire:model="filters.education_level_year"
                                            filterContainer="questionbank-active-filters"
                    />
                    <x-input.choices-select :multiple="true"
                                            :options="$this->educationLevel"
                                            :withSearch="true"
                                            placeholderText="Niveau"
                                            wire:model="filters.education_level_id"
                                            filterContainer="questionbank-active-filters"
                    />
                </div>

                <div id="questionbank-active-filters"
                     wire:ignore
                     :class="($el.innerHTML !== '') ? 'mt-2' : ''"
                >
                    <template id="filter-pill-template" class="hidden">
                        <div class="space-x-2">
                            <span class="flex"></span>
                            <x-icon.close-small @click="removeFilterItem($el)"/>
                        </div>
                    </template>

                </div>
            </div>

            {{-- Content --}}
            <div class="flex flex-col py-4" style="min-height: 500px">
                <div class="flex">
                    <span class="note text-sm">{{ $this->resultCount }} resultaten</span>
                </div>
                <x-grid class="mt-4" x-show="!loading" wire:key="grid-{{ $this->resultCount }}">
                    @foreach($this->questions as $question)
                        <x-grid.question-card :question="$question"/>
                    @endforeach
                    @if($this->questions->count() && $this->questions->count() != $this->resultCount)
                        @foreach([1,2,3,4,5] as $loader)
                            <x-grid.loading-card :delay="$loader">
                                @if($loader === 3)
                                    <span x-intersect="$wire.showMore()"></span>
                                @endif
                            </x-grid.loading-card>
                        @endforeach
                    @else
                        @if(!$this->questions->count())
                            @if($this->filters['source'] === $this::SOURCE_PERSONAL)
                                <span class="col-span-2 text-center">U heeft nog geen eigen gemaakte vragen voor deze zoekfilters.</span>
                            @else
                                <span class="col-span-2 text-center">Er is nog geen openbare content voor uw school.</span>
                            @endif
                        @else
                                <span class="col-span-2 text-center">Er zijn geen items meer voor deze zoekfilters</span>
                        @endif
                    @endif
                </x-grid>
            </div>
        </div>
    </div>
    <div class="hidden sticky h-0 bottom-20 ml-auto mr-4">
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
            <span>{{ __('cms.Toevoegen') }}</span>
        </x-button.cta>
    </div>
</div>