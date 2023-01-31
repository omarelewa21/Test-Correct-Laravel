<div id="co-learning-teacher-page"
     class="flex w-full relative" style="z-index: 1; min-height: 100vh"
     x-data="{
     showStudentAnswer: false,
     showAnswerModel: false,
     showQuestion: true,
     resetToggles() {
        this.showStudentAnswer = false;
        this.showAnswerModel = false;
        this.showQuestion = true;
     },
     }"
     wire:poll.keep-alive.5000ms="render()" {{-- getTestParticipantsData() ? --}}
>
    <x-partials.header.co-learning-teacher testName="{{ $testTake->test->name ?? '' }}"
                                           discussionType="{{ $testTake->discussion_type }}"
                                           :atLastQuestion="$this->atLastQuestion"
    />
    <x-partials.sidebar.co-learning-teacher.drawer
    />

    <div id="main-content-container"
         class="flex border border-2 relative w-full justify-between overflow-auto "
    >
        <div class="flex flex-col w-full space-y-4 pt-10 px-[60px] pb-14"
             wire:key="container-{{$this->testTake->discussing_question_id}}"
        >

            <x-co-learning-teacher.question-container
                    :question="$this->testTake->discussingQuestion">
            </x-co-learning-teacher.question-container>

            <x-co-learning-teacher.answer-model-container>
            </x-co-learning-teacher.answer-model-container>

            <x-co-learning-teacher.answer-container
                    :question="$this->testTake->discussingQuestion">
            </x-co-learning-teacher.answer-container>
        </div>

    </div>

    {{-- Success is as dangerous as failure. --}}
</div>
