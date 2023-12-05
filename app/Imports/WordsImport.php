<?php

namespace tcCore\Imports;

use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithValidation;
use tcCore\Http\Enums\WordType;
use tcCore\Services\CompileWordListService;

class WordsImport implements WithValidation, ToArray, SkipsEmptyRows
{
    use Importable;

    private array $wordList = [];
    private array $typeOrder = [];

    public function rules(): array
    {
        $rules = [];
        foreach (WordType::cases() as $key => $case) {
            $rules[(string)$key] = [
                Rule::requiredIf($this->getType($key) === WordType::SUBJECT),
                'string',
                'nullable',
            ];
        }

        return $rules;
    }

    public function array(array $array): void
    {
        $rows = $array;
        foreach ($rows as $row) {
            $row = array_filter($row);
            $words = [];
            foreach ($row as $key => $word) {
                if ($type = $this->getType($key)) {
                    $words[$type->value] = CompileWordListService::buildEmptyWordItem(text: $word, type: $type);
                }
            }

            $this->wordList[] = $words;
        }
    }

    public function getWordList(): array
    {
        return $this->wordList;
    }

    public function setTypeOrder(array $order): void
    {
        $this->typeOrder = array_filter($order);
    }

    private function getType(int $key): ?WordType
    {
        if ($this->typeOrder && isset($this->typeOrder[$key])) {
            return WordType::tryFrom($this->typeOrder[$key]);
        }

        return WordType::fromOrder($key + 1);
    }


}
