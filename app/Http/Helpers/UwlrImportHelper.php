<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 09/04/2020
 * Time: 10:59
 */

namespace tcCore\Http\Helpers;


use Artisaninweb\SoapWrapper\SoapWrapper;
use Carbon\Carbon;
use tcCore\Exceptions\UwlrAutoImportException;
use tcCore\Jobs\ProcessUwlrSoapResultJob;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationSchoolYear;


class UwlrImportHelper
{
    const AUTO_UWLR_IMPORT_STATUS_PLANNED = 'PLANNED';
    const AUTO_UWLR_IMPORT_STATUS_PROCESSING = 'PROCESSING';
    const AUTO_UWLR_IMPORT_STATUS_DONE = 'DONE';
    const AUTO_UWLR_IMPORT_STATUS_FAILED = 'FAILED';

    const CLIENT_CODE = 'OV';

    const CLIENT_NAME = 'Overig';

    public static function handleIfMoreSchoolLocationsCanBeImported()
    {
        $instance = new static();
        if($instance->canAddNewJobForImport()){
            $instance->prepareNextSchoolLocationForProcessing();
        }
    }

    protected function canAddNewJobForImport() :boolean
    {
        // max 2 jobs in the queue
        $numberOfJobsInTable = DB::select("SELECT count(*) as number from jobs where payload like '%".ProcessUwlrSoapResultJob::class."%'")[0]->number;
        if($numberOfJobsInTable >= 2){
            return false;
        }
        // none in the queue after 5 o'clock and before 19 o'clock
        $now = Carbon::now();
        if($now->hour >= 5 && $now->hour <= 19){
            return false;
        }
        return true;
    }

    protected function prepareNextSchoolLocationForProcessing()
    {
        $schoolLocation = SchoolLocation::where('lvs_active',true) // only if lvs is active
            ->where('auto_uwlr_import',true) // only if allowed to be imported automagically
            ->where('auto_uwlr_import_status','<>',self::AUTO_UWLR_IMPORT_STATUS_PROCESSING) // don't pick up if currently processing
            ->where('auto_uwlr_import_status','<>',self::AUTO_UWLR_IMPORT_STATUS_PLANNED) // don't pick  up if already planned
            ->whereRaw('Date(auto_uwlr_last_import) <> CURDATE()') // don't handle twice a day
            ->orderBy('auto_uwlr_last_import','asc') // last one first
            ->orderBy('external_main_code','asc') // if same then order by brin
            ->orderBy('external_sub_code','asc') // and even dependance
            ->first();

        try {
            $schoolYears = static::getSchoolYearsForUwlrImport($schoolLocation);
            if($schoolYears->count() < 1){
                throw new \Exception(sprintf('No schoolyears found for school location %s (%d)',$schoolLocation->name, $schoolLocation->getKey()));
            }
            $schoolYear = $schoolYears[0];
            $helper = static::getHelperAndStoreInDB($schoolLocation->lvs_type,$schoolYear, $schoolLocation->external_main_code, $schoolLocation->external_sub_code);
            $schoolLocation->auto_uwlr_import_sattus = self::AUTO_UWLR_IMPORT_STATUS_PLANNED;
            $schoolLocation->save();
            dispatch((new ProcessUwlrSoapResultJob($helper->getResultIdentifier(), true)));
        }
        catch(\Throwable $e){
            throw new UwlrAutoImportException($e);
        }
    }

    public static function getSchoolYearsForUwlrImport($location)
    {
        $currentPeriod = PeriodRepository::getCurrentPeriodForSchoolLocation($location, false, false);
        if($location) {
            $years = $location
                ->schoolLocationSchoolYears
                ->load('schoolYear:id,year')
                ->when(optional($currentPeriod)->schoolYear, function ($slsy) use ($currentPeriod) {
                    return $slsy->where('schoolYear.year', '>=', $currentPeriod->schoolYear->year);
                })
                ->when(!optional($currentPeriod)->schoolYear, function ($slsy) {
                    return $slsy->where('schoolYear.year', '>=', Carbon::now()->subYear()->format('Y'));
                })
                ->sortBy('schoolYear.year', SORT_REGULAR, false)
                ->filter(function(SchoolLocationSchoolYear $s) {
                    return null != optional($s->schoolYear)->year;
                })
                ->map(function(SchoolLocationSchoolYear $slsy){
                    return sprintf('%d-%d', $slsy->schoolYear->year, $slsy->schoolYear->year + 1);
                });

            return array_values($years->toArray());
        }
        return [];
    }

    public static function getHelperAndStoreInDB($lvsType, $schoolYear, $brinCode, $dependanceCode)
    {
        $helper = null;
        switch ($lvsType) {
            case SchoolLocation::LVS_MAGISTER:
                $helper = MagisterHelper::guzzle($schoolYear,$brinCode, $dependanceCode)->parseResult()->storeInDB($brinCode, $dependanceCode);
                break;
            case SchoolLocation::LVS_SOMTODAY:
                $helper = (new SomTodayHelper(new SoapWrapper()))->search(
                    static::CLIENT_CODE,
                    static::CLIENT_NAME,
                    $schoolYear,
                    $brinCode,
                    $dependanceCode
                )->storeInDB();
                break;
            default:
                throw new \Exception(sprintf('No valid lvs_type (%s)',$lvsType));
        }
        return $helper;
    }
}