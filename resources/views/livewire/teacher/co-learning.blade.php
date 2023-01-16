<div id="co-learning-teacher-page"
     class="flex flex-col w-full">
    <x-partials.header.co-learning-teacher testName="{{ $testTake->test->name ?? '' }}"
                                  discussionType="{{ $testTake->discussion_type }}"
                                  :coLearningAtLastQuestion="$this->atLastQuestion"
    />
    <x-partials.sidebar.co-learning-teacher
    />
    {{-- Success is as dangerous as failure. --}}
</div>
