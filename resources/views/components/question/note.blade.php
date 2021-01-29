@if($question->note_type == 'TEXT')
    <x-button.secondary size="sm" class="mb-4" @click="alert('Open Notitieblok')">
        <span>Open notitieblok</span>
    </x-button.secondary>
@endif