<?php

namespace tcCore\Rules\NistPassword;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;
use tcCore\Http\Enums\PasswordStatus;

/**
 * Class DictionaryWords.
 *
 * Implements the 'Dictionary words' recommendation
 * from NIST SP 800-63B section 5.1.1.2.
 */
class BreachedPasswords implements Rule
{

    /**
     * @param $password
     *
     * @return PasswordStatus
     *
     * This function submits the first five characters of the password to the
     * https://haveibeenpwned.com/API/v2#SearchingPwnedPasswordsByRange API.
     * This is according to the https://en.wikipedia.org/wiki/K-anonymity model
     * to prevent leaking the password of the user.
     */
    protected function queryPasswordStatus(string $password): PasswordStatus
    {
        $hash = $this->getHash($password);
        $request = Http::withHeaders([
            'User-Agent' => 'Test-Correct - https://www.test-correct.nl/'
        ])->timeout(3)->get('https://api.pwnedpasswords.com/range/'.substr($hash, 0, 5));

        if (!$request->ok()) {
            return PasswordStatus::UNKNOWN;
        }

        $hashes = $request->body();
        //FIXME: some caching of the hashes would be good to prevent future duplicate requests
        return $this->parsePasswordStatus($hash, $hashes);
    }

    /**
     * @param string $hash
     * @param string $responseBody
     *
     * @return PasswordStatus
     */
    protected function parsePasswordStatus($hash, $responseBody): PasswordStatus
    {
        $hash = strtoupper($hash);
        $hashSuffix = substr($hash, 5);

        $lines = explode("\r\n", $responseBody);

        foreach ($lines as $line) {
            list($exposedHashSuffix, $occurrences) = explode(':', $line);
            if (hash_equals($hashSuffix, $exposedHashSuffix)) {
                return PasswordStatus::EXPOSED;
            }
        }

        return PasswordStatus::NOT_EXPOSED;
    }

    /**
     * @param $string
     *
     * @return string
     */
    protected function getHash(string $string): string
    {
        return sha1($string);
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
        $passwordStatus = $this->queryPasswordStatus($value);

        return $passwordStatus !== PasswordStatus::EXPOSED;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.found-in-data-breach');
    }
}