<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

class Relation extends TypeProvider
{
    public $requiresAnswer = false;
    public $questionOptions = ['decimal_score' => true];
    public function getTranslationKey(): string
    {
        return __('question.relationquestion');
    }

    public function getTemplate(): string
    {
        return 'relation-question';
    }

    public function hasScoringDisabled(): bool
    {
        return true;
    }

    public function questionSectionTitle(): string
    {
        return __('cms.Begeleidende tekst');
    }

    public function isSettingDisabled(string $property): bool
    {
        return $property === 'decimalScore';
    }
}