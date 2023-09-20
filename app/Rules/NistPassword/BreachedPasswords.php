<?php

namespace tcCore\Rules\NistPassword;

use DivineOmega\LaravelPasswordExposedValidationRule\PasswordExposed;
use DivineOmega\PasswordExposed\PasswordExposedChecker;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class BreachedPasswords.
 *
 * Implements the 'Passwords obtained from previous breach corpuses' recommendation
 * from NIST SP 800-63B section 5.1.1.2.
 */
class BreachedPasswords extends PasswordExposed implements Rule
{
    /**
     * BreachedPasswords constructor.
     *
     * @param PasswordExposedChecker|null $passwordExposedChecker
     */
    public function __construct(PasswordExposedChecker $passwordExposedChecker = null)
    {
        parent::__construct($passwordExposedChecker);

        $this->setMessage(__('validation.found-in-data-breach'));
    }
}