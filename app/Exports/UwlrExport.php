<?php

namespace tcCore\Exports;
use Maatwebsite\Excel\Concerns\FromArray;


class UwlrExport implements FromArray
{
    public function __construct(array $data)
    {
        $this->exporting = $data;
    }

    public function array(): array
    {
        return $this->exporting;
    }
}
