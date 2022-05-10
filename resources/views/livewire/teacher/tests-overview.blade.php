<div id="question-bank"
     class="flex flex-col relative w-full min-h-full bg-lightGrey border-t border-secondary overflow-auto"
     x-data="{openTab: @entangle('openTab'), checkedCount: 0, loading: false}"
     @checked="$event.detail ? checkedCount += 1 : checkedCount -= 1"
     @question-added.window="Notify.notify('Vraag toegevoegd!')"
     @question-removed.window="Notify.notify('Vraag verwijderd!')"
>
    <div class="flex w-full border-b border-secondary">
        <div class="w-full max-w-5xl mx-auto">
            <div class="flex w-full space-x-4">
                <div>
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 'personal'">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 'personal' ? 'primary' : '' ">{{ __('general.Persoonlijk') }}</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'personal' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
{{--                    <div class="flex relative text-midgrey cursor-default">--}}
                                            <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 'school'">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 'school' ? 'primary' : '' ">{{ __('general.School') }}</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'school' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    <div class="flex relative text-midgrey cursor-default">
                        {{--                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 3">--}}
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 'national' ? 'primary' : '' ">{{ __('general.Nationaal') }}</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'national' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
{{--                    <div class="flex relative text-midgrey cursor-default">--}}
                                            <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 'exams'">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 'exams' ? 'primary' : '' ">{{ __('general.Examens') }}</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'exams' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    {{--                    <div class="flex relative text-midgrey cursor-default">--}}
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 'cito'">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 'cito' ? 'primary' : '' ">{{ __( 'general.cito-snelstart') }}</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'exams' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="flex w-full">
        <div class="w-full max-w-5xl mx-auto divide-y divide-secondary">
            {{-- Filters--}}
            <div class="flex flex-col py-4">
                <div class="flex w-full mt-2">
                    <div class="relative w-full">
                        <x-input.text class="w-full"
                                      placeholder="Zoek..."
                                      wire:model="filters.name"
                        />
                        <x-icon.search class="absolute right-0 -top-2"/>
                    </div>
                </div>
                <div class="flex flex-wrap w-full space-x-2">
                    <x-input.choices-select :multiple="true"
                                            :options="$this->subjects"
                                            :withSearch="true"
                                            placeholderText="Vak"
                                            wire:model="filters1.subject_id"
                    />
                    <x-input.choices-select :multiple="true"
                                            :options="$this->educationLevelYear"
                                            :withSearch="true"
                                            placeholderText="Leerjaar"
                                            wire:model="filters1.education_level_year"
                    />
                    <x-input.choices-select :multiple="true"
                                            :options="$this->educationLevel"
                                            :withSearch="true"
                                            placeholderText="Niveau"
                                            wire:model="filters1.education_level_id"
                    />
                </div>

            </div>

            {{-- Content --}}
            <div class="flex flex-col py-4" style="min-height: 500px">
                <div class="flex">
                    <span class="note text-sm">{{  trans_choice('general.number-of-tests', $results->total(), ['count' => $results->total()]) }}</span>
                </div>
                <x-grid  class="mt-4">
                    <x-grid.loading-card wire:loading.class.remove="hidden" class="hidden"/>
                    <x-grid.loading-card wire:loading.class.remove="hidden" class="hidden"/>
                    <x-grid.loading-card wire:loading.class.remove="hidden" class="hidden"/>
                    <x-grid.loading-card wire:loading.class.remove="hidden" class="hidden"/>
                    <x-grid.loading-card wire:loading.class.remove="hidden" class="hidden"/>



                    @foreach($results as $test)
                        <x-grid.test-card :test="$test" wire:loading.class="hidden" />
                    @endforeach
                </x-grid>
                {{ $results->links('components.partials.tc-paginator') }}

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
    <livewire:teacher.planning-modal></livewire:teacher.planning-modal>
</div>
