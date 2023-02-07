<?php

namespace tcCore\Http\Enums\Taxonomy;

interface Taxonomy
{
    public static function columnName(): string;
    public static function displayName(): string;
    public static function values(): array;
}