<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 09/04/2020
 * Time: 10:59
 */

namespace tcCore\Http\Helpers;


use Artisaninweb\SoapWrapper\SoapWrapper;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use tcCore\Exceptions\UwlrAutoImportException;
use tcCore\Jobs\ProcessUwlrSoapResultJob;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationSchoolYear;
use tcCore\UwlrSoapEntry;
use tcCore\UwlrSoapResult;
use Throwable;


class UwlrImportHelper
{
    const AUTO_UWLR_IMPORT_STATUS_PLANNED = 'PLANNED';
    const AUTO_UWLR_IMPORT_STATUS_PROCESSING = 'PROCESSING';
    const AUTO_UWLR_IMPORT_STATUS_DONE = 'DONE';
    const AUTO_UWLR_IMPORT_STATUS_FAILED = 'FAILED';

    const AUTO_UWLR_IMPORT_STATUS_FAILED_TWICE = 'FAILED_TWICE';

    const CLIENT_CODE = 'OV';

    const CLIENT_NAME = 'Overig';

    public static function pruneRecords($sub = '1 month')
    {
        try {
            $start = microtime(true);
            $pastCarbon = Carbon::now()->sub($sub);
            $uwlrSoapResultQueryBuilder = UwlrSoapResult::where('created_at', '<', $pastCarbon)->select('id');
            $countResult = $uwlrSoapResultQueryBuilder->count();
            $entryQueryBuilder = UwlrSoapEntry::whereIn('uwlr_soap_result_id', $uwlrSoapResultQueryBuilder);
            $countEntries = $entryQueryBuilder->count();
            $entryQueryBuilder->delete();
            $uwlrSoapResultQueryBuilder->delete();
            $duration = microtime(true) - $start;
            return [
                'result records deleted' => $countResult,
                'entry records deleted'  => $countEntries,
                'duration'               => $duration
            ];
        } catch (Throwable $e) {
            $message = sprintf('Could not determine carbapp/Http/Traits/WithQuestionFilteredHelpers.php:430on date based on substraction of the term `%s` and therefor not prune the uwl soap records. Error was: %s', $sub, $e->getMessage());
            Bugsnag::notifyException(new \Exception(
                $message
            ));
            return $message;
        }
    }

    public static function handleIfMoreSchoolLocationsCanBeImported(): void
    {
        $instance = new static();
        if ($instance->canAddNewJobForImport()) {
            $instance->prepareNextSchoolLocationForProcessing();
        }
    }

    /**
     * cleanup school location statusses which are crashed as in are still in status PLANNED or PROCESSING at 13:00 as nothing should be happening by then.
     * @return void
     */
    public static function cleanupCrashedImports()
    {
        SchoolLocation::where('auto_uwlr_import_status', self::AUTO_UWLR_IMPORT_STATUS_PLANNED)
            ->orWhere('auto_uwlr_import_status', self::AUTO_UWLR_IMPORT_STATUS_PROCESSING)
            ->update(['auto_uwlr_import_status' => null]);
    }

    protected function canAddNewJobForImport(): bool
    {
        // max 2 jobs in the queue
        $numberOfJobsInTable = DB::select("SELECT count(*) as number from jobs where payload like '%" . ProcessUwlrSoapResultJob::class . "%'")[0]->number;
        if ($numberOfJobsInTable >= 2) {
            return false;
        }
        // moved to the scheduler
//        // none in the queue after 5 o'clock and before 19 o'clock
//        $now = Carbon::now();
//        if ($now->hour >= 5 && $now->hour <= 19) {
//            return false;
//        }
        return true;
    }

    protected function prepareNextSchoolLocationForProcessing(): bool
    {
        $schoolLocation = SchoolLocation::where('lvs_active', true) // only if lvs is active
        ->where('auto_uwlr_import', true) // only if allowed to be imported automagically
        ->whereNull('import_merge_school_location_id') // only master accounts not the sub records
        ->where(function ($query) {
            $query->where(function ($q) {
                $q->where('auto_uwlr_import_status', '<>', self::AUTO_UWLR_IMPORT_STATUS_PROCESSING) // don't pick up if currently processing
                ->where('auto_uwlr_import_status', '<>', self::AUTO_UWLR_IMPORT_STATUS_PLANNED); // don't pick  up if already planned
            })
                ->orWhereNull('auto_uwlr_import_status');
        })
        ->where(function ($query) {
            $query->where(function ($q) {
                $q->where('auto_uwlr_import_status', self::AUTO_UWLR_IMPORT_STATUS_FAILED_TWICE) // failed twice
                ->where('auto_uwlr_last_import', '<=', Carbon::now()->subDay()); // but last try was more than a day ago
            })
                ->orWhere('auto_uwlr_import_status', '<>', self::AUTO_UWLR_IMPORT_STATUS_FAILED_TWICE) // or status unlike failed twice
                ->orWhereNull('auto_uwlr_import_status');
        })
        ->where(function ($query) {
            $query->where(function ($q) {
                $q->where('auto_uwlr_last_import', '<=', Carbon::now()->subHours(10)->toDateTimeString())// don't handle twice a day
                ->where(function ($t) {
                    $t->where('auto_uwlr_import_status', '<>', self::AUTO_UWLR_IMPORT_STATUS_FAILED)
                        ->
                        orWhereNull('auto_uwlr_import_status');
                });
            })
                ->orwhere('auto_uwlr_import_status', self::AUTO_UWLR_IMPORT_STATUS_FAILED)
                ->orWhereNull('auto_uwlr_last_import');
        })
        ->orderBy('auto_uwlr_last_import', 'asc') // last one first
        ->orderBy('external_main_code', 'asc') // if same then order by brin
        ->orderBy('external_sub_code', 'asc') // and even dependance
        ->first();
        if (!$schoolLocation) {
            return false;
        }

        try {
            $schoolYears = static::getSchoolYearsForUwlrImport($schoolLocation);
            if (count($schoolYears) < 1) {
                throw new \Exception(sprintf('No schoolyears found for school location %s (%d)', $schoolLocation->name, $schoolLocation->getKey()));
            }
            $schoolYear = $schoolYears[0];

            $helper = $this->handleSchoolLocation($schoolLocation, $schoolYear);
            $resultSet = $helper->getResultSet();

            $this->handleSchoolLocationsToBeMergedIfAny($schoolLocation,$resultSet->getKey(),$schoolYear);

            $resultSet->status = 'READYTOPROCESS';
            $resultSet->save();

            dispatch((new ProcessUwlrSoapResultJob($helper->getResultIdentifier(), true)));
        } catch (\Throwable $e) {
            $newStatus = self::AUTO_UWLR_IMPORT_STATUS_FAILED;
            if ($schoolLocation->auto_uwlr_import_status === self::AUTO_UWLR_IMPORT_STATUS_FAILED) {
                $newStatus = self::AUTO_UWLR_IMPORT_STATUS_FAILED_TWICE;
            }
            $schoolLocation->auto_uwlr_import_status = $newStatus;
            $schoolLocation->auto_uwlr_last_import = Carbon::now();
            $schoolLocation->save();
            throw new UwlrAutoImportException($e);
        }
        return true;
    }

    protected function handleSchoolLocation($schoolLocation, $schoolYear): MagisterHelper|SomTodayHelper
    {
        $helper = static::getHelperAndStoreInDB($schoolLocation->lvs_type, $schoolYear, $schoolLocation->external_main_code, $schoolLocation->external_sub_code);
        $schoolLocation->auto_uwlr_import_status = self::AUTO_UWLR_IMPORT_STATUS_PLANNED;
        $schoolLocation->save();
        return $helper;
    }

    protected function handleSchoolLocationsToBeMergedIfAny($parentSchoolLocation,$masterResultSetId,$schoolYear)
    {
        SchoolLocation::where('import_merge_school_location_id', $parentSchoolLocation->getKey())
            ->where('lvs_active', true)
            ->where('auto_uwlr_import', true)  // only if allowed to be imported automagically;
            ->get()
            ->each(function(SchoolLocation $schoolLocation) use ($masterResultSetId, $schoolYear){
                // import data in own resultset
                $helper = $this->handleSchoolLocation($schoolLocation,$schoolYear);
                $resultSet = $helper->getResultSet();
                // move records to masterResultset
                UwlrSoapEntry::where('uwlr_soap_result_id',$resultSet->getKey())->update(['uwlr_soap_result_id' => $masterResultSetId]);
                $resultSet->status = 'MOVEDTOMASTER'.$masterResultSetId;
                $resultSet->save();
            });
    }

    public static function getSchoolYearsForUwlrImport($location): array
    {
        $currentPeriod = PeriodRepository::getCurrentPeriodForSchoolLocation($location, false, false);
        if ($location) {
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
                ->filter(function (SchoolLocationSchoolYear $s) {
                    return null != optional($s->schoolYear)->year;
                })
                ->map(function (SchoolLocationSchoolYear $slsy) {
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
                $helper = MagisterHelper::guzzle($schoolYear, $brinCode, $dependanceCode, true)->parseResult()->storeInDB($brinCode, $dependanceCode);
                break;
            case SchoolLocation::LVS_SOMTODAY:
                $helper = (new SomTodayHelper(new SoapWrapper()))->search(
                    static::CLIENT_CODE,
                    static::CLIENT_NAME,
                    $schoolYear,
                    $brinCode,
                    $dependanceCode,
                    true
                )->storeInDB();
                break;
            default:
                throw new \Exception(sprintf('No valid lvs_type (%s)', $lvsType));
        }
        return $helper;
    }
}