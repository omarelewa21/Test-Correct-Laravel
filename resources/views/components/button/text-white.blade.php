@extends('components.button.default')
@props([
    'componentName' => 'text-button-white',
    'size' => 'md',
    'withHover' => false,
    'withBackgroundGradient' => false,
])
<?php
$attributes = $attributes->merge(['class' => $withHover ? 'text-button-hover-bg' : '']);
?>