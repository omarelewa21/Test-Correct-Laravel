<?php

namespace tcCore\Rules\NistPassword;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class DictionaryWords.
 *
 * Implements the 'Dictionary words' recommendation
 * from NIST SP 800-63B section 5.1.1.2.
 */
class DictionaryWords implements Rule
{
    private $words = [];

    /**
     * DictionaryWords constructor.
     */
    public function __construct()
    {
        $dictionaryFileEn = app_path("Rules/NistPassword/resources/words.txt");
        $dictionaryFileNl = app_path("Rules/NistPassword/resources/words_nl.txt");

        $fullWordList = file_get_contents($dictionaryFileEn) . "\n" . file_get_contents($dictionaryFileNl);

        $this->words = explode("\n", $fullWordList);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !in_array(strtolower(trim($value)), $this->words);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.can-not-be-dictionary-word');
    }
}