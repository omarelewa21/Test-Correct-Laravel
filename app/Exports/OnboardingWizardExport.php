<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 18/08/2020
 * Time: 09:05
 */

namespace tcCore\Exports;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\BeforeExport;
use tcCore\OnboardingWizardReport;

class OnboardingWizardExport implements WithEvents, FromCollection, WithHeadings
{

    public function headings(): array
    {
        return array_keys(OnboardingWizardReport::first()->toArray());
    }

 
    public function collection()
    {
        return OnboardingWizardReport::all();
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