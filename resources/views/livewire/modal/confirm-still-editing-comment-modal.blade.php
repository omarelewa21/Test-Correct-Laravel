<x-modal.base-modal force-close="true">
    <x-slot name="title">
        @unless($creatingNewComment)
            <h2>{{ __('modal.Feedback_in_bewerking_title') }}</h2>
        @else
            <h2>{{ __('modal.New_Feedback_in_bewerking_title') }}</h2>
        @endif
    </x-slot>
    <x-slot name="content">
        @unless($creatingNewComment)
            @lang('modal.Feedback_in_bewerking_text')
        @else
            @lang('modal.New_Feedback_in_bewerking_text')
        @endif
    </x-slot>
    <x-slot name="footer">
        <div class="flex justify-end w-full gap-4 items-center">
            <x-button.text x-on:click="$store.answerFeedback.cancelAction()">
                <span>{{__('modal.Terug')}}</span>
            </x-button.text>
            <x-button.cta x-on:click="$store.answerFeedback.continueAction()">
                <x-icon.checkmark/>
                <span>{{__('test-take.Doorgaan')}}</span>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>
