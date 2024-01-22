<?php

namespace tcCore\Rules\WordList;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RowRules implements ValidationRule
{
    public function validate(string $attribute, mixed $row, Closure $fail): void
    {
        if ($this->hasTooFewWords($row)) {
            $fail("At least two words need to be in a row");
        }

        if ($this->hasNoSubjectWord($row)) {
            $fail("At least one word must have the type of subject.");
        }
    }

    private function hasTooFewWords(array $row): bool
    {
        return count($row) < 2;
    }

    private function hasNoSubjectWord(array $row): bool
    {
        $hasSubjectType = false;
        foreach ($row as $item) {
            if (isset($item['type']) && $item['type'] === 'subject') {
                $hasSubjectType = true;
                break;
            }
        }

        return !$hasSubjectType;
    }
}
