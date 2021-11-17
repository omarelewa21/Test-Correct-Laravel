<?php
/**
 * Created by PhpStorm.
 * User: frits
 * Date: 26/02/2021
 * Time: 09:05
 */

namespace tcCore\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\BeforeExport;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use tcCore\SchoolLocationReport;

class SchoolLocationExport implements WithEvents, FromCollection, WithHeadings,WithColumnFormatting
{

    public function headings(): array
    {
        $search = 'company_id';
        $replace = 'Company ID';
        return array_map(function($v) use ($search, $replace) {
            return $v == $search ? $replace : $v;
        },array_keys(SchoolLocationReport::first()->toArray()));
    }

    public function collection()
    {

       $all_fields = SchoolLocationReport::all();

        return collect($all_fields);

    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            // Handle by a closure.
            BeforeExport::class => function(BeforeExport $event) {
                $event->writer->getProperties()->setCreator('TLC')
                    ->setCompany('TLC');
            },

        ];
    }

    /**
     * @return array
     */

    public function columnFormats(): array
    {
        return [
            'BI' => NumberFormat::FORMAT_DATE_DATETIME,
            'BJ' => NumberFormat::FORMAT_DATE_DATETIME,
        ];
    }

}