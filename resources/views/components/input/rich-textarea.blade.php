@props([
    'type' => 'student',
    'editorId',
])

@php
    $initFunctionCall = "RichTextEditor.initStudent('".$editorId."')";
    if ($type == 'cms') {
       $initFunctionCall = "RichTextEditor.initCMS('".$editorId."')";
    }

@endphp


<div wire:ignore>
    <textarea
        {{ $attributes->merge(['class' => 'form-input resize-none']) }}
        x-data="{}" x-init="{{ $initFunctionCall }}"
        id="{{ $editorId }}"
        name="{{ $editorId }}"
    ></textarea>
</div>
