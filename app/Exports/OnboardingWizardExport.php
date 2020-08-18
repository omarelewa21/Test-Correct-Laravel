<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 18/08/2020
 * Time: 09:05
 */

namespace tcCore\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use tcCore\OnboardingWizardReport;

class OnboardingWizardExport implements WithEvents, FromCollection
{

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