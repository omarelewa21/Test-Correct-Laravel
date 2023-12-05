<?php

namespace tcCore\Imports;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use tcCore\Http\Enums\WordType;
use tcCore\Services\CompileWordListService;

class WordsImport implements WithValidation, ToArray
{
    use Importable;

    protected array $wordList = [];

    public function rules(): array
    {
        return [
            '0' => 'required|string',
            '1' => 'required|string',
        ];
    }

    public function array(array $rows): void
    {
        foreach ($rows as $row) {
            $words = [];
            foreach ($row as $key => $word) {
                $type = WordType::fromOrder($key + 1);
                $words[$type->value] = CompileWordListService::buildEmptyWordItem(text: $word, type: $type);
            }

            $this->wordList[] = $words;
        }
    }

    public function getWordList(): array
    {
        return $this->wordList;
    }

    public function customValidationMessages(): array
    {
        return [
            '0.required' => 'Floepie :attribute',
        ];
    }
}
