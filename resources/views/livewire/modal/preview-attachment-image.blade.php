@extends('livewire.modal.preview-attachment')

@section('content')
    <div class="w-full h-full"
         x-data="{
         zoomOut: () => {
                if(this.percentage <= 12.5) { return; }
                if(this.percentage <= 25) {
                    this.percentage = 12.5;
                } else {
                    this.percentage = this.percentage - 25;
                }
                previewImage.style.width = this.percentage.toString() + '%';
             },
             zoomIn: () => {
                 this.percentage = this.percentage + 25;
                 previewImage.style.width = this.percentage.toString() + '%';
             }
         }"
         x-init="previewImage = $refs.imagePreview;
                 percentage = 100;
                 previewImage.style.width = percentage.toString() + '%';
         ">
        <div class="w-full h-full overflow-auto flex flex-col items-center align-center justify-center">
            <img src="{{ route('teacher.preview.question-attachment-show', ['attachment' => $attachment->uuid, 'question' => $questionUuid]) }}"
                 style="max-width: 300%"
                 class="w-full bg-white" alt="Preview image"
                 x-ref="imagePreview">
        </div>
        <div style="position: absolute;  bottom: 19px; right: 67px;"
             @click="zoomIn()"
        >
            <x-button.icon-circle class="bg-white">
                <x-icon.plus/>
            </x-button.icon-circle>
        </div>
        <div style="position: absolute; bottom: 19px; right: 19px;"
             @click="zoomOut()"
        >
            <x-button.icon-circle class="bg-white">
                <x-icon.min/>
            </x-button.icon-circle>
        </div>
    </div>
@endsection
