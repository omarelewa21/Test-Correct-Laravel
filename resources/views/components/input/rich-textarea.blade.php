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
           case 'student-preview':
               $initFunctionCall = "RichTextEditor.initClassicEditorForStudentPreviewplayer('".$editorId."','".$lang."',".$temp.")";
               break;
           case 'assessment-feedback':
               $initFunctionCall = "RichTextEditor.initAssessmentFeedback('".$editorId."','".$lang."',".$temp.")";
               break;
           default:
               $initFunctionCall = "RichTextEditor.initClassicEditorForStudentplayer('".$editorId."','".$lang."',".$temp.")";
              break;
       }
@endphp

<div class="ckeditor-error"
     @error($attributes->wire('model')->value)
        style="--ck-color-toolbar-border: var(--all-red);--ck-color-base-border: var(--all-red);"
     @enderror
     selid="ckeditor">
    <div wire:ignore @class(['ckeditor-disabled' => $disabled])>
        <textarea
                {{ $attributes->merge(['class' => 'form-input resize-none']) }}
                x-data="{}" x-init="{{ $initFunctionCall }}"
                id="{{ $editorId }}"
                name="{{ $editorId }}"
                @if($disabled) disabled @endif
        > {{ $slot ?? '' }} </textarea>
    </div>
</div>