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

    // for as long as the fields are in the database, remove them from the export by hand
    protected $hiddenFields = [
        'finished_demo_tour',
        'finished_demo_steps_percentage',
        'finished_demo_substeps_percentage',
        'current_demo_tour_step',
        'current_demo_tour_step_since_date',
        'current_demo_tour_step_since_hours',
        'average_time_finished_demo_tour_steps_hours',
    ];

    public function headings(): array
    {
        return array_keys(OnboardingWizardReport::first()->makeHidden($this->hiddenFields)->toArray());
    }

 
    public function collection()
    {
        return OnboardingWizardReport::all()->makeHidden($this->hiddenFields);
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