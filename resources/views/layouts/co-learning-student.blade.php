<x-layouts.base>
    <x-slot:bodyClass value="test">co-learning-student-page with-inline-feedback</x-slot:bodyClass>

    <livewire:co-learning.header testName="{{ $testName ?? '' }}"/>

    <main  class="flex flex-1 m-foot-head " x-data="coLearningStudent()">
        {{ $slot  }}
    </main>

    @push('scripts')
        <script>
            addCSRFTokenToEcho('{{ csrf_token() }}');
        </script>
    @endpush
</x-layouts.base>