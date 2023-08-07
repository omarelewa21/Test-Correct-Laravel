<?php

namespace tcCore\Rules;

use Illuminate\Contracts\Validation\Rule;

class EmailImproved implements Rule
{
    public readonly string $regexp;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        /**
         * @ and . are required
         * @ must be before . and after at least 1 character
         * between @ en . must be at least 1 character
         * after . must be at least 2 characters
         */
        $this->regexp = '/^.+@.+[\.]+[\w-]{2,}$/';
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $trimmedValue = trim($value);
        return validator([$attribute => $trimmedValue], [$attribute => 'email'])->passes()
            && preg_match($this->regexp, $trimmedValue);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('auth.email_incorrect');
    }
}
