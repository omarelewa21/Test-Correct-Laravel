<x-layouts.pdf>
    <div class="w-full flex flex-col mb-5 pdf-answers">
        <div>
            @foreach($testParticipants as  $key => $testParticipant)
                <livewire:test-take-preview.test-participant
                        :testParticipant="$testParticipant"
                        :testTake="$testTake"
                        :showQuestionText="$showQuestionText"
                />
            @endforeach
        </div>
    </div>
</x-layouts.pdf>