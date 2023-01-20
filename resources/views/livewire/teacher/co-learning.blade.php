<div id="co-learning-teacher-page"
     class="flex w-full relative" style="z-index: 1; min-height: 100vh">
    <x-partials.header.co-learning-teacher testName="{{ $testTake->test->name ?? '' }}"
                                  discussionType="{{ $testTake->discussion_type }}"
                                  :atLastQuestion="$this->atLastQuestion"
    />
    <x-partials.sidebar.co-learning-teacher.drawer
    />

    <div id="main-content-container"
         class="flex border border-2 relative w-full justify-between overflow-auto "
    >
        <div>
           Vraag: {!! __('co-learning.'.$testTake->discussingQuestion->type.($testTake->discussingQuestion->subtype ? '-'.$testTake->discussingQuestion->subtype : '')) !!}
        </div>
    </div>

    {{-- Success is as dangerous as failure. --}}
</div>
