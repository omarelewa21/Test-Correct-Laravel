<div class="ckeditor-error relative comment-editor"
     @error($attributes->wire('model')->value)
        style="--ck-color-toolbar-border: var(--all-red);--ck-color-base-border: var(--all-red);"
     @enderror
     selid="ckeditor">
    <style>
        :root {
            --active-comment-color: #ff00aa;
            --ck-color-comment-marker-active: var(--active-comment-color);
        }

    </style>
    <style id="hoveringCommentMarkerStyle">{{-- filled with javascript --}}</style>
    <style id="commentMarkerStyles">
        {!!  $commentMarkerStyles !!}
    </style>

    <div >
        @foreach($commentThreads as $thread)
            <div class="absolute z-10 cursor-pointer" id="icon-{{ $thread['threadId'] }}" x-init="
            initCommentIcon($el, @js($thread));
        ">

                @if($thread['iconName'] !== null)
                    <span class="inline-block" style="scale: calc(2 / 3);">
                        <x-dynamic-component :component="$thread['iconName']"> </x-dynamic-component>
                    </span>
                @else
                    <x-icon.feedback x-bind:class="{'text-primary': hoveringComment?.uuid === '{{ $thread['uuid'] }}' }" />
                @endif
            </div>
        @endforeach
    </div>


    {{--<template x-for="thread in @js($commentThreads)">
        <div class="absolute z-10 cursor-pointer" x-bind:id="'icon-' + thread.threadId" x-init="
            initCommentIcon($el, thread);
        ">
            <template x-if="thread.iconName !== null">
                <x-dynamic-component :component=thread.iconName></x-dynamic-component>
            </template>
            <template x-if="thread.iconName === null">
                <x-icon.feedback-text/>
            </template>
        </div>
    </template>--}}
    <div wire:ignore @class(['ckeditor-disabled' => $disabled, 'relative'])>
        <textarea
                {{ $attributes->merge(['class' => 'form-input resize-none']) }}
                x-data=""
                x-init="{{ $initFunctionCall }}"
                id="{{ $editorId }}"
                name="{{ $editorId }}"
                @disabled($disabled)
                x-on:reinitialize-editor-{{ $editorId }}.window="{{ $initFunctionCall }}"
        > {{ $slot ?? '' }} </textarea>
    </div>
</div>