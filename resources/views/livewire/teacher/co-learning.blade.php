<div id="co-learning-teacher-page"
     class="flex flex-col w-full">
    <x-co-learning-teacher.header testName="{{ $testTake->test->name ?? '' }}"
                                  discussionType="{{ $testTake->discussion_type }}"
    />
    <x-co-learning-teacher.drawer
    />
    {{-- Success is as dangerous as failure. --}}
</div>
