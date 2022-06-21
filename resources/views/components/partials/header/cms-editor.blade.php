<div class="question-editor-header disabled z-50">
    <div class="flex items-center space-x-4">
        <button class="flex items-center justify-center rounded-full bg-white/20 w-10 h-10 rotate-svg-180 hover:scale-105 transition-transform"
                wire:click="saveAndRedirect"
        >
            <x-icon.arrow/>
        </button>
        <div>
            <h4 class="text-white">{{ $testName }}</h4>
        </div>
    </div>

    <div class="flex space-x-6 items-center">
        @if($this->withDrawer)
            <div>
                <span class="text-sm">{{ trans_choice('cms.vraag', $questionCount['regular']) }}, {{ trans_choice('cms.group-question-count', $questionCount['group']) }}</span>
            </div>
            <div>
                <span class="primary bg-white px-2 text-sm rounded-sm bold">BETA</span>
            </div>
            <div x-data="{
                    toPdf: async () => {
                        let response = await $wire.getPdfUrl();
                        window.open(response, '_blank');
                    }
                }">
                <button @if($this->canDeleteTest)
                            @click="$dispatch('delete-modal', ['test', '{{ $this->testId }}'])"
                        @else
                            disabled
                        @endif
                        class="new-button button-primary"
                        title="{{ __('teacher.Toets verwijderen') }}"
                >
                    <x-icon.trash/>
                </button>
                <button wire:click="$emit('openModal', 'teacher.test-edit-modal', {testUuid: '{{ $this->testId }}'})"
                        class="new-button button-primary"
                        title="{{ __('teacher.Toets instellingen') }}"
                >
                    <x-icon.settings/>
                </button>
                <button wire:click=""
                        disabled
                        class="new-button button-primary"
                        title="{{ __('teacher.Toets instellingen') }}"
                >
                    <x-icon.edit/>
                </button>
                <button @click="window.open('{{ route('teacher.test-preview', ['test'=> $this->testId]) }}', '_blank')"
                        class="new-button button-primary"
                        title="{{ __('teacher.Toets voorbeeldweergave') }}"
                >
                    <x-icon.preview/>
                </button>
                <button @click="toPdf()"
                        class="new-button button-primary"
                        title="{{ __('teacher.Toets PDF-weergave') }}"
                >
                    <x-icon.pdf color="currentColor"/>
                </button>
                <button wire:click="$emit('openModal','teacher.planning-modal', {{ json_encode(['testUuid' => $this->testId]) }}) "
                        class="new-button button-cta"
                        title="{{ __('teacher.Toets inplannen') }}"
                >
                    <x-icon.schedule/>
                </button>
            </div>
        @endif
    </div>
</div>