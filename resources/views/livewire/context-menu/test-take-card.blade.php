<x-menu.context-menu.base context="test-take-card" class="w-60">

    @if($this->hasArchiveOption())
        <x-menu.context-menu.button wire:click="copyTestTakeLink">
            <x-slot name="icon"><x-icon.copy/></x-slot>
            <x-slot name="text">{{ __('test-take.copy-link') }}</x-slot>
        </x-menu.context-menu.button>
        <x-menu.context-menu.button wire:click="goToCoLearning">
            <x-slot name="icon"><x-icon.chevron/></x-slot>
            <x-slot name="text">{{ __('test-take.CO-Learning') }}</x-slot>
        </x-menu.context-menu.button>
        @if($this->hasAnswerPdfOption())
            <x-menu.context-menu.button wire:click="studentAnswersPdf">
                <x-slot name="icon"><x-icon.pdf-file/></x-slot>
                <x-slot name="text">{{ __('test-take.Antwoord PDF') }}</x-slot>
            </x-menu.context-menu.button>
            <x-menu.context-menu.button wire:click="updateStatusToTaken">
                <x-slot name="icon"><x-icon.arrow/></x-slot>
                <x-slot name="text">{{ __('test_take.update_to_taken') }}</x-slot>
            </x-menu.context-menu.button>
        @endif
        @if($this->hasSkipDiscussing())
            <x-menu.context-menu.button wire:click="skipDiscussing">
                <x-slot name="icon"><x-icon.grading/></x-slot>
                <x-slot name="text">{{ __('test-take.Direct nakijken') }}</x-slot>
            </x-menu.context-menu.button>
        @endif
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