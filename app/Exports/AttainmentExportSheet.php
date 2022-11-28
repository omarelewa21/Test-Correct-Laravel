<?php

namespace tcCore\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use tcCore\Attainment;
use tcCore\BaseSubject;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AttainmentExportSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $baseSubject;

    public function __construct(BaseSubject $baseSubject)
    {
        $this->baseSubject = $baseSubject;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Attainment::where('base_subject_id',$this->baseSubject->id)->get();
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->baseSubject->name;
    }

    public function headings(): array
    {
        return [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
            'base_subject_id',
            'education_level_id',
            'attainment_id',
            'code',
            'subcode',
            'subsubcode',
            'description',
            'status',
            'uuid'
        ];
    }
}
