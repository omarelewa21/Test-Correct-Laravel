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
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\BeforeExport;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use tcCore\CitoExportRow;
use tcCore\OnboardingWizardReport;

class CitoExport implements WithEvents, FromCollection, WithMapping, WithHeadings
{

    public function headings(): array
    {
        return [
            'BRIN',
            'LLnID',
            'Vak',
            'Leerdoeltoets',
            'Timestamp',
            'Itemnr1',
            'Itemnr2',
            'Itemnr3',
            'Itemnr4',
            'Itemnr5',
            'Itemnr6',
            'Itemnr7',
            'Itemnr8',
            'Itemnr9',
            'Itemnr10',
            'Itemnr11',
            'Itemnr12',
            'Itemnr13',
            'Itemnr14',
            'Itemnr15',
            'Itemnr16',
            'Antwoord1',
            'Antwoord2',
            'Antwoord3',
            'Antwoord4',
            'Antwoord5',
            'Antwoord6',
            'Antwoord7',
            'Antwoord8',
            'Antwoord9',
            'Antwoord10',
            'Antwoord11',
            'Antwoord12',
            'Antwoord13',
            'Antwoord14',
            'Antwoord15',
            'Antwoord16',
            'Score1',
            'Score2',
            'Score3',
            'Score4',
            'Score5',
            'Score6',
            'Score7',
            'Score8',
            'Score9',
            'Score10',
            'Score11',
            'Score12',
            'Score13',
            'Score14',
            'Score15',
            'Score16'
        ];
    }

    public function map($row): array
    {
        $data = [
            $row->getBrinHash(), // BRIN
            $row->getUserIdHash(),
            $row->vak,
            $row->leerdoel,
            $row->answered_at,
            ];
        for($i=1;$i<=16;$i++){
            $name = 'item_'.$i;
            $data[] = $row->$name;
        }
        for($i=1;$i<=16;$i++){
            $name = 'answer_'.$i;
            $data[] = $row->$name;
        }
        for($i=1;$i<=16;$i++){
            $name = 'score_'.$i;
            $data[] = $row->$name;
        }
        return $data;
    }

    public function collection()
    {
        return CitoExportRow::where('export',true)->get();
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