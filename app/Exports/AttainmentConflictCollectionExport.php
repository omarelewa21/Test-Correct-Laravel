<?php

namespace tcCore\Exports;

use DOMXPath;
use Maatwebsite\Excel\Concerns\FromCollection;
use tcCore\Attainment;
use tcCore\BaseSubject;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AttainmentConflictCollectionExport extends AttainmentConflictExport implements FromCollection, WithHeadings
{
    protected $questions;
    protected $collection;
    protected $handled = [];

    public function __construct($questions,$weight)
    {
        $lean = $weight=='lean';
        $superLean = $weight=='superLean';
        $this->questions = $questions;
        $this->collection = collect([]);
    }

    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

}
