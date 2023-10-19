<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

class Relation extends TypeProvider
{
    public $requiresAnswer = true;
    public $questionOptions = [
        'decimal_score' => true,
        'shuffle' => true,
        'selection_count' => 5,
    ];

    public function preparePropertyBag()
    {
        parent::preparePropertyBag();

        $this->instance->cmsPropertyBag['words'] = array_map(function ($value) {
            return [
                'main' => $value,
                'translation' => 'translation translation',
                'definition' => 'definition',
                'synonym' => 'synonym',
                'fredje' => '',
            ];
        }, range(1, 18));
    }

    public function initializePropertyBag($q)
    {
//        $this->instance->question['random_per_student'] = $q->random_per_student;
//        $this->instance->question['random_per_student_amount'] = $q->random_per_student_amount;
    }

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

    public function answerSectionTitle(): string
    {
        return __('cms.Vraagstelling met antwoordmodel');
    }

    public function isSettingDisabled(string $property): bool
    {
        return $property === 'decimalScore';
    }
}