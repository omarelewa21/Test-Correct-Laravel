<?php
/**
 * Created by PhpStorm.
 * User: frits
 * Date: 18/08/2020
 * Time: 09:05
 */

namespace tcCore\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\BeforeExport;
use tcCore\LocationReport;

class LocationExport implements WithEvents, FromCollection, WithHeadings
{

    public function headings(): array
    {
        return array_keys(LocationReport::first()->toArray());
    }

    public function collection()
    {
        return LocationReport::all();
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

}