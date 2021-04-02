<?php
/**
 * Created by PhpStorm.
 * User: frits
 * Date: 26/02/2021
 * Time: 09:05
 */

namespace tcCore\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\BeforeExport;
use tcCore\LocationReport;

class LocationExport implements WithEvents, FromCollection, WithHeadings,WithMapping
{

    private $field_names = [
        'id',	
        'location_id',	
        'location_name',
        'nr_licenses',	
        'nr_activated_licenses',	
        'nr_browsealoud_licenses',
        'nr_approved_test_files_7'	,
        'nr_approved_test_files_30',	
        'nr_approved_test_files_60',	
        'nr_approved_test_files_90',	
        'total_approved_test_files',
        'nr_added_question_items_7',	
        'nr_added_question_items_30',	
        'nr_added_question_items_60',	
        'nr_added_question_items_90',	
        'total_added_question_items_files',	
        'nr_approved_classes_7',	
        'nr_approved_classes_30',
        'nr_approved_classes_60',	
        'nr_approved_classes_90',
        'total_approved_classes',	
        'nr_tests_taken_7',	
        'nr_tests_taken_30',	
        'nr_tests_taken_60',	
        'nr_tests_taken_90',	
        'total_tests_taken',	
        'nr_tests_checked_7',
        'nr_tests_checked_30',	
        'nr_tests_checked_60',	
        'nr_tests_checked_90',	
        'total_tests_checked',	
        'nr_tests_rated_7',	
        'nr_tests_rated_30',	
        'nr_tests_rated_60',	
        'nr_tests_rated_90',	
        'total_tests_rated',	
        'nr_colearning_sessions_7',	
        'nr_colearning_sessions_30',	
        'nr_colearning_sessions_60',	
        'nr_colearning_sessions_90',	
        'total_colearning_sessions',	
        'in_browser_tests_allowed	',
        'nr_active_teachers'
    ];
    
    public function headings(): array
    {
        return array_keys(LocationReport::first()->toArray());
    }

    public function collection()
    {

       $all_fields = LocationReport::all();

        return collect($all_fields);

    }
    
    public function map($location_report): array
    {
        
        $report_row = [];
        
        foreach($this->field_names as $field_name) {
            
            $report_row[$field_name] = $location_report->{$field_name};

        }

        $report_row['created_at'] = substr($location_report->created_at,0,19);
        $report_row['updated_at'] = substr($location_report->created_at,0,19);
        
        return $report_row;
        
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