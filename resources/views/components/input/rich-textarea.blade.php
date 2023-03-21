@props([
    'type' => 'student',
    'editorId',
    'disabled' => false,
    'lang' => 'nl_NL',
    'allowWsc' => false,
])
@php
    $temp = $allowWsc ? 'true':'false';
        switch($type) {
           case 'cms':
               $initFunctionCall = "RichTextEditor.initForTeacher('".$editorId."','".$lang."', ".$temp.")";
               break;
           case 'cms-completion':
               $initFunctionCall = "RichTextEditor.initCompletionCMS('".$editorId."','".$lang."',".$temp.")";
               break;
           case 'cms-selection':
               $initFunctionCall = "RichTextEditor.initSelectionCMS('".$editorId."','".$lang."',".$temp.")";
               break;
           case 'student-co-learning':
               $initFunctionCall = "RichTextEditor.initStudentCoLearning('".$editorId."','".$lang."',".$temp.")";
               break;
           default:
               $initFunctionCall = "RichTextEditor.initClassicEditorForStudentplayer('".$editorId."','".$lang."',".$temp.")";
              break;
       }
@endphp

<div class="ckeditor-error rounded-10 @error($attributes->wire('model')->value) border border-allred @enderror"
     selid="ckeditor">
    <div wire:ignore @class(['ckeditor-disabled' => $disabled])>
        <textarea
            {{ $attributes->merge(['class' => 'form-input resize-none']) }}
            x-data="{}" x-init="{{ $initFunctionCall }}"
            id="{{ $editorId }}"
            name="{{ $editorId }}"
            @if($disabled) disabled @endif
        > {{ $value ?? '' }} </textarea>
    </div>
</div>