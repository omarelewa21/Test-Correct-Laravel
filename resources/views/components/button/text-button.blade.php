@extends('components.button.default')
@props([
    'componentName' => 'text-button',
    'size' => 'md',
    'withHover' => false,
    'withBackgroundGradient' => false,
])
<?php
$attributes = $attributes->merge(['class' => $withHover ? 'text-button-hover-bg' : '']);
?>