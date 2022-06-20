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
            <div>
                <button wire:click="$emit('openModal', 'teacher.test-edit-modal', {testUuid: '{{ $this->testId }}'})"
                        class="new-button button-primary">
                    <x-icon.settings/>
                </button>
            </div>
        @endif
    </div>
</div>