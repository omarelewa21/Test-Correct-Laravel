<?php

namespace tcCore\Exports;
use Maatwebsite\Excel\Concerns\FromArray;


class TestTakesExport implements FromArray
{
    public function __construct(array $sheet)
    {
        $this->exporting = $sheet;
    }

    public function array(): array
    {
        return $this->exporting;
    }

