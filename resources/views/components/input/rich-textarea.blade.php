@props([
    'type' => 'student',
    'editorId',
    'disabled' => false,
    'lang' => 'nl_NL',
    'allowWsc' => false,
])
@php
    $temp = $allowWsc?'true':'false';
        switch($type) {
           case 'cms':
               $initFunctionCall = "RichTextEditor.initCMS('".$editorId."','".$lang."',".$temp.")";
               break;
           case 'cms-completion':
               $initFunctionCall = "RichTextEditor.initCompletionCMS('".$editorId."','".$lang."',".$allowWsc.")";
               break;
           case 'cms-selection':
               $initFunctionCall = "RichTextEditor.initSelectionCMS('".$editorId."','".$lang."',".$allowWsc.")";
               break;
           case 'student-co-learning':
               $initFunctionCall = "RichTextEditor.initStudentCoLearning('".$editorId."','".$lang."',".$allowWsc.")";
               break;
           default:
              $initFunctionCall = "RichTextEditor.initStudent('".$editorId."')";
              break;
       }
@endphp

<div class="ckeditor-error rounded-10 @error($attributes->wire('model')->value) border border-allred @enderror">
    <div wire:ignore>
        <textarea
            selid="ckeditor"
            {{ $attributes->merge(['class' => 'form-input resize-none']) }}
            x-data="{}" x-init="{{ $initFunctionCall }}"
            id="{{ $editorId }}"
            name="{{ $editorId }}"
            @if($disabled) disabled @endif
        ></textarea>
    </div>
</div>