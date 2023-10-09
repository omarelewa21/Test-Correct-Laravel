<?php

namespace tcCore\Http\Livewire\Teacher\Cms\Providers;

class InfoScreen extends TypeProvider
{
    public $requiresAnswer = false;

    public function showQuestionScore()
    {
        return false;
    }

    public function preparePropertyBag()
    {
        $questionOptions = [
            'add_to_database'        => 0,
            'decimal_score'          => 0,
            'discuss'                => 0,
            "is_open_source_content" => 0,
            'note_type'              => 'NONE',
            'score'                  => 0,
            'all_or_nothing'         => false,
        ];
        foreach ($questionOptions as $key => $value) {
            $this->instance->question[$key] = $value;
        }
    }

    public function getTranslationKey(): string
    {
        return __('cms.infoscreen-question');
    }

    public function showSettingsTaxonomy()
    {
        return false;
    }

    public function showSettingsAttainments()
    {
        return false;
    }

    public function showSettingsTags()
    {
        return false;
    }

    public function showStatistics()
    {
        return false;
    }

    public function getTemplate(): string
    {
        return 'infoscreen-question';
    }

    public function questionSectionTitle(): string
    {
        return  __('cms.Informatietekst');
    }

    public function isSettingDisabled($property): bool
    {
        return in_array($property, [
            'allowNotes',
            'addToDatabase',
            'discuss',
            'decimalScore',
        ]);
    }
}
