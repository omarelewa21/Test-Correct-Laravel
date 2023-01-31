<x-menu.context-menu.base context="test-take-card" class="w-60">

    @if($this->hasArchiveOption())

        <div x-show="openTab == 'taken'">
            <x-menu.context-menu.button wire:click="copyTestTakeLink">
                <x-slot name="icon"><x-icon.copy/></x-slot>
                <x-slot name="text">{{ __('test-take.copy-link') }}</x-slot>
            </x-menu.context-menu.button>
            <x-menu.context-menu.button wire:click="goToCoLearningPage">
                <x-slot name="icon"><x-icon.filled-arrow/></x-slot>
                <x-slot name="text">{{ __('test-take.CO-Learning') }}</x-slot>
            </x-menu.context-menu.button>
            <x-menu.context-menu.button wire:click="goToScheduleMakeUpPage">
                <x-slot name="icon"><x-icon.schedule/></x-slot>
                <x-slot name="text">{{ __('test-take.schedule-make-up-test') }}</x-slot>
            </x-menu.context-menu.button>
            @if($this->hasSkipDiscussing())
                <x-menu.context-menu.button wire:click="skipDiscussing">
                    <x-slot name="icon"><x-icon.grading/></x-slot>
                    <x-slot name="text">{{ __('test-take.Direct nakijken') }}</x-slot>
                </x-menu.context-menu.button>
            @endif
        </div>

        <div x-show="openTab == 'norm'">
            <x-menu.context-menu.button wire:click="studentAnswersPdf">
                <x-slot name="icon"><x-icon.pdf-file/></x-slot>
                <x-slot name="text">{{ __('test-take.Antwoord PDF') }}</x-slot>
            </x-menu.context-menu.button>
            @if ($this->normButtonsShow['allow-access'])
                <x-menu.context-menu.button wire:click="openAllowAccessInNormPage">
                    <x-slot name="icon"><x-icon.preview/></x-slot>
                    <x-slot name="text">{{ __('test-take.allow-access') }}</x-slot>
                </x-menu.context-menu.button>
            @else
                <x-menu.context-menu.button wire:click="closePreviewAccess">
                    <x-slot name="icon"><x-icon.preview-off/></x-slot>
                    <x-slot name="text">{{ __('test-take.close-preview') }}</x-slot>
                </x-menu.context-menu.button>
            @endif
            <x-menu.context-menu.button wire:click="openAssessInNormPage">
                <x-slot name="icon"><x-icon.grading/></x-slot>
                <x-slot name="text">{{ __('test-take.assess') }}</x-slot>
            </x-menu.context-menu.button>
            @if ($this->normButtonsShow['normalize'])
                <x-menu.context-menu.button wire:click="goToNormalizePage">
                    <x-slot name="icon"><x-icon.autocheck/></x-slot>
                    <x-slot name="text">{{ __('test-take.normalize') }}</x-slot>
                </x-menu.context-menu.button>
                @if ($this->normButtonsShow['marking'])
                    <x-menu.context-menu.button wire:click="goToMarkingPage">
                        <x-slot name="icon"><x-icon.grade/></x-slot>
                        <x-slot name="text">{{ __('test-take.marking') }}</x-slot>
                    </x-menu.context-menu.button>
                @endif
                <x-menu.context-menu.button wire:click="rttiExport">
                    <x-slot name="icon"><x-icon.download/></x-slot>
                    <x-slot name="text">{{ __('test-take.rtti-export') }}</x-slot>
                </x-menu.context-menu.button>
            @endif
            <x-menu.context-menu.button wire:click="updateStatusToTaken">
                <x-slot name="icon"><x-icon.arrow/></x-slot>
                <x-slot name="text">{{ __('test_take.update_to_taken') }}</x-slot>
            </x-menu.context-menu.button>
            <x-menu.context-menu.button wire:click="goToScheduleMakeUpPage">
                <x-slot name="icon"><x-icon.schedule/></x-slot>
                <x-slot name="text">{{ __('test-take.schedule-make-up-test') }}</x-slot>
            </x-menu.context-menu.button>
        </div>


        <x-menu.context-menu.button wire:click="archive">
            <x-slot name="icon"><x-icon.archive/></x-slot>
            <x-slot name="text">{{ __('test-take.Archiveren') }}</x-slot>
        </x-menu.context-menu.button>

        <x-copy-to-clipboard/>
    @endif

    @if($this->hasUnarchiveOption())
        <x-menu.context-menu.button wire:click="unarchive">
            <x-slot name="icon"><x-icon.archive/></x-slot>
            <x-slot name="text">{{ __('test-take.Dearchiveren') }}</x-slot>
        </x-menu.context-menu.button>
    @endif

</x-menu.context-menu.base>