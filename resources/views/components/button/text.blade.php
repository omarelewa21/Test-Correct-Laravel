@extends('components.button.default')
@props([
    'componentName' => 'text-button',
    'size' => 'md',
    'withHover' => false,
    'withBackgroundGradient' => false,
    'white' => false,
])
<?php
$attributes = $attributes->merge(['class' => $withHover ? 'text-button-hover-bg' : '']);
$componentName = $white ? 'text-button-white' : 'text-button'
?>