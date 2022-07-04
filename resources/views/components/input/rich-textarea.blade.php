@props([
    'type' => 'student',
    'editorId',
    'disabled' => false
])

@php
    switch($type) {
       case 'cms':
           $initFunctionCall = "RichTextEditor.initCMS('".$editorId."')";
           break;
       case 'cms-completion':
           $initFunctionCall = "RichTextEditor.initCompletionCMS('".$editorId."')";
           break;
       case 'cms-selection':
           $initFunctionCall = "RichTextEditor.initSelectionCMS('".$editorId."')";
           break;
       default:
          $initFunctionCall = "RichTextEditor.initStudent('".$editorId."')";
          break;
   }
@endphp

<div class="ckeditor-error rounded-10 @error($attributes->wire('model')->value) border border-allred @enderror">
    <div wire:ignore>
        <textarea
            {{ $attributes->merge(['class' => 'form-input resize-none']) }}
            x-data="{}" x-init="{{ $initFunctionCall }}"
            id="{{ $editorId }}"
            name="{{ $editorId }}"
            @if($disabled) disabled @endif
        ></textarea>
    </div>
</div>