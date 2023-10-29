<?php

namespace tcCore\Rules\NistPassword;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

/**
 * Class ContextSpecificWords.
 *
 * Implements the 'Context-specific words' recommendation
 * from NIST SP 800-63B section 5.1.1.2.
 */
class ContextSpecificWords implements Rule
{
    protected $words = [];
    private $detectedWord = null;

    /**
     * ContextSpecificWords constructor.
     */
    public function __construct($username)
    {
        $emailDomain = Str::before(Str::after($username, '@'), '.');
        $emailName = Str::before($username, '@');

        $text = config('app.name', 'test-correct');
        $text .= ' ';
        $text .= str_replace(
            ['http://', 'https://', '-', '_', '.com', '.org', '.biz', '.net', '.nl', '.'],
            ' ',
            config('app.url')
        );
        $text .= ' ' . $username;
        $text .= ' ' . $emailDomain;
        $text .= ' ' . $emailName;

        $words = explode(' ', strtolower($text));

        foreach ($words as $key => $word) {
            if (strlen($word) < 3) {
                unset($words[$key]);
            }
        }

        $this->words = $words;
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
        $value = strtolower($value);

        foreach ($this->words as $word) {
            if (stripos($value, $word) !== false) {
                $this->detectedWord = $word;

                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.can-not-contain-word', ['word' => $this->detectedWord]);
    }
}