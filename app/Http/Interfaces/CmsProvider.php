<?php

namespace tcCore\Http\Interfaces;

interface CmsProvider
{
    public function getTranslationKey(): string;

    public function getTemplate(): string;
}