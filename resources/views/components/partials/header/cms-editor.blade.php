<div class="question-editor-header disabled z-50">
    <div class="flex items-center space-x-4 flex-1 relative mr-4">
        <button class="flex items-center justify-center rounded-full bg-white/20 w-10 h-10 rotate-svg-180 hover:scale-105 transition-transform"
                wire:click="saveAndRedirect"
                @click="$dispatch('store-current-question');"
                selid="back-btn"
        >
            <x-icon.arrow/>
        </button>
        <div class="flex flex-1" x-data="{testName: @entangle('testName')}">
            <h4 class="text-white truncate"
                :style="{'max-width': $el.parentElement.offsetWidth + 'px'}"
                x-text="testName"
                :title="testName"
            >

            </h4>
        </div>
    </div>

    <div class="flex space-x-6 items-center">
        @if($this->withDrawer)
            <div class="flex min-w-max space-x-2">
                {{--                <span class="text-sm">{{ trans_choice('cms.vraag', $questionCount['regular']) }}, {{ trans_choice('cms.group-question-count', $questionCount['group']) }}</span>--}}
            </div>

            <div class="flex space-x-2" x-data="{
                    toPdf: () => {
                        $wire.emit('openModal', 'teacher.pdf-download-modal', {uuid: '{{ \tcCore\Test::whereUuid($this->testId)->first()->uuid}}'});
                    }
                }"
                 @click="forceSyncEditors();$wire.saveIfDirty()"
            >
                <button @if($this->canDeleteTest)
                            @click="$dispatch('delete-modal', ['test', '{{ $this->testId }}'])"
                        @else
                            disabled
                        @endif
                        class="new-button button-primary w-10"
                        title="{{ __('cms.Verwijderen') }}"
                >
                    <x-icon.trash/>
                </button>
                <button wire:click="$emit('openModal', 'teacher.test-edit-modal', {testUuid: '{{ $this->testId }}'})"
                        class="new-button button-primary w-10"
                        title="{{ __('cms.Instellingen') }}"
                >
                    <x-icon.settings/>
                </button>
                <button @click="setTimeout(() => {window.open('{{ route('teacher.test-preview', ['test'=> $this->testId]) }}', '_blank')}, 500)"
                        class="new-button button-primary w-10"
                        title="{{ __('cms.voorbeeld') }}"
                >
                    <x-icon.preview/>
                </button>
                <button @click="setTimeout(() => {toPdf()}, 500)"
                        class="new-button button-primary w-10"
                        title="{{ __('cms.PDF maken') }}"
                >
                    <x-icon.pdf-file color="currentColor"/>
                </button>
                <livewire:actions.test-quick-take variant="icon-button" :uuid="$this->testId"/>
                <livewire:actions.test-plan-test variant="icon-button" :uuid="$this->testId"/>
            </div>
        @endif
    </div>
    <div class="absolute inset-0 z-50"
         x-data="{headerLoadingOverlay: false}"
         x-show="headerLoadingOverlay"
         @filepond-start.window="headerLoadingOverlay = true;"
         @filepond-finished.window="headerLoadingOverlay = false;"
    ></div>
</div>