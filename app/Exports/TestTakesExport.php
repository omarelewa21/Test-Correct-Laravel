<?php

namespace tcCore\Exports;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Excel;

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
}
