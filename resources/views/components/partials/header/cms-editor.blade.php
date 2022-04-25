<div class="question-editor-header disabled z-50">
    <div class="flex items-center space-x-4">
        <button class="flex items-center justify-center rounded-full bg-white/20 w-10 h-10 rotate-svg-180 hover:scale-105 transition-transform"
                wire:click="saveAndRedirect"
        >
            <x-icon.arrow />
        </button>
        <div>
            <h4 class="text-white">{{ $testName }}</h4>
        </div>
    </div>

    <div class="flex space-x-6 items-center">
        <div>
            <span class="text-sm">{{ trans_choice('cms.vraag', $questionCount['regular']) }}, {{ trans_choice('cms.group-question-count', $questionCount['group']) }}</span>
        </div>
        <div class="flex space-x-2 hidden">
            <button class="new-button button-primary">
                <x-icon.edit/>
            </button>
            <button class="new-button button-primary">
                <x-icon.edit/>
            </button>
            <button class="new-button button-primary">
                <x-icon.edit/>
            </button>
            <button class="new-button button-primary">
                <x-icon.edit/>
            </button>
            <button class="new-button button-primary">
                <x-icon.edit/>
            </button>
            <button class="new-button button-primary">
                <x-icon.edit/>
            </button>

        </div>
    </div>
</div>