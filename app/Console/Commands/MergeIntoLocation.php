<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use tcCore\FeatureSetting;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Period;
use tcCore\PValue;
use tcCore\Question;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationSchoolYear;
use tcCore\SchoolLocationSection;
use tcCore\SchoolLocationUser;
use tcCore\SchoolYear;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\User;

class MergeIntoLocation extends Command
{
    use BaseCommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schoolLocation:mergeIntoLocation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge all school location data into another location';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        if (!$toBeMergedId = $this->ask('Which location do you want to merge')) {
            $this->error('please let us know which school location you want to merge');
            return Command::FAILURE;
        }

        $toBeMergedLocation = SchoolLocation::find($toBeMergedId);
        if (!$toBeMergedLocation) {
            $this->error('The school location could not be found, please try again');
            return Command::FAILURE;
        }

        if (!$masterId = $this->ask('Which location do you want to merge TO')) {
            $this->error('please let us know which school location you want to merge TO');
            return Command::FAILURE;
        }

        $masterLocation = SchoolLocation::find($masterId);
        if (!$masterLocation) {
            $this->error('The school location to merge to could not be found, please try again');
            return Command::FAILURE;
        }

        if (!$this->confirm(
            sprintf('Is it correct that you want to merge %s at %s in %s to %s at %s in %s',
                $toBeMergedLocation->name,
                $toBeMergedLocation->main_address,
                $toBeMergedLocation->main_city,
                $masterLocation->name,
                $masterLocation->main_address,
                $masterLocation->main_city))) {
            $this->components->alert('ok, we stop, please try again');
            return Command::FAILURE;
        }

        $this->mergeLocations($toBeMergedLocation, $masterLocation);

        return Command::SUCCESS;
    }

    protected function mergeLocations($from, $to)
    {
        $this->writeInfoText('Starting checks...',true);
        $fromSubjects = Subject::whereIn('section_id', SchoolLocationSection::where('school_location_id', $from->getKey())->select('section_id'))->get();
        $toSubjects = Subject::whereIn('section_id', SchoolLocationSection::where('school_location_id', $to->getKey())->select('section_id'))->get();

        $notFoundSubjects = collect([]);
        $fromSubjects->map(function (Subject $fromSubject) use ($toSubjects, $notFoundSubjects) {
            $toSubjectsFound = $toSubjects->where('base_subject_id', $fromSubject->base_subject_id)
                ->where('abbreviation', $fromSubject->abbreviation)
                ->where('name', $fromSubject->name);

            if ($toSubjectsFound->count() !== 1) {
                $notFoundSubjects->push($fromSubject);
            } else {
                $fromSubject->toSubjectId = $toSubjectsFound->first()->getKey();
            }
            return $fromSubject;
        });

        $fromPeriods = Period::whereIn('school_year_id', SchoolLocationSchoolYear::where('school_location_id', $from->getKey())->select('school_year_id'))->get();
        $toPeriods = Period::whereIn('school_year_id', SchoolLocationSchoolYear::where('school_location_id', $to->getKey())->select('school_year_id'))->get();

        $notFoundPeriods = collect([]);
        $fromPeriods->map(function (Period $fromPeriod) use ($toPeriods, $notFoundPeriods) {
            $toPeriodsFound = $toPeriods->where('start_date', $fromPeriod->start_date)
                ->where('end_date', $fromPeriod->end_date)
                ->where('name', $fromPeriod->name);
            if ($toPeriodsFound->count() !== 1) {
                $notFoundPeriods->push($fromPeriod);
            } else {
                $fromPeriod->toPeriodId = $toPeriodsFound->first()->getKey();
            }
            return $fromPeriod;
        });

        $fromSchoolYears = SchoolYear::whereIn('id', SchoolLocationSchoolYear::where('school_location_id', $from->getKey())->select('school_year_id'))->get();
        $toSchoolYears = SchoolYear::whereIn('id', SchoolLocationSchoolYear::where('school_location_id', $to->getKey())->select('school_year_id'))->get();

        $notFoundSchoolYears = collect([]);
        $fromSchoolYears->map(function (SchoolYear $fromSchoolYear) use ($toSchoolYears, $notFoundSchoolYears) {
            $toSchoolYearsFound = $toSchoolYears->where('year', $fromSchoolYear->year);
            if ($toSchoolYearsFound->count() !== 1) {
                $notFoundSchoolYears->push($fromSchoolYear);
            } else {
                $fromSchoolYear->toSchoolYearId = $toSchoolYearsFound->first()->getKey();
            }
            return $fromSchoolYear;
        });

        ActingAsHelper::getInstance()->setUser(User::whereSchoolLocationId($from->getKey())->first());
        $fromYearId = SchoolYearRepository::getCurrentSchoolYear();

        ActingAsHelper::getInstance()->setUser(User::whereSchoolLocationId($to->getKey())->first());
        $toYearId = SchoolYearRepository::getCurrentSchoolYear();

        $fromClassNames = SchoolClass::where('school_location_id', $from->getKey())->where('school_year_id',$fromYearId)->pluck('name');
        $toClassNames = SchoolClass::where('school_location_id', $to->getKey())->where('school_year_id',$toYearId)->pluck('name');
        $totalClassNames = $fromClassNames->merge($toClassNames);
        $duplicateClassNames = $totalClassNames->duplicates();

        $this->handlePossibleErrors($notFoundSubjects,$notFoundSchoolYears,$notFoundPeriods,$duplicateClassNames);

        // We can start the conversion
        // school_location_user (delete and add if non existent)


        // users
        // school_location_id ✓
        // test takes
        // period_id ✓
        // school_location_id ✓
        // tests
        // subjects (name, abbreviation, base_subject_id comparison) ✓
        // owner_id ✓
        // period_id ✓
        // questions
        // subjects ✓
        // p_values
        // subjects ✓
        // period_id ✓
        // school_class
        // school_location_id ✓
        // school_year_id ✓
        // check on name unique ✓
        // teachers
        // subject_id ✓
        // search_filters
        // heeft dit effect?
        // user is examcoordinator_for
        // school => school_location

        $fromPeriods->each(function(Period $period){
            $this->writeInfoText('updating periods for test takes');
            TestTake::where('period_id',$period->getKey())->withTrashed()->update(['period_id' => $period->toPeriodId]);
            $this->writeDoneInfo('DONE',color:'green');
            $this->writeInfoText('updating periods for tests');
            Test::where('period_id',$period->getKey())->withTrashed()->update(['period_id' => $period->toPeriodId]);
            $this->writeDoneInfo('DONE',color:'green');
            $this->writeInfoText('updating periods for pvalues');
            PValue::where('period_id',$period->getKey())->withTrashed()->update(['period_id' => $period->toPeriodId]);
            $this->writeDoneInfo('DONE',color:'green');
        });

        $fromSubjects->each(function(Subject $subject){
            $this->writeInfoText('updating subject '.$subject->name.' for tests');
            Test::where('subject_id',$subject->getKey())->withTrashed()->update(['subject_id' => $subject->toSubjectId]);
            $this->writeDoneInfo('DONE',color:'green');
            $this->writeInfoText('updating subject '.$subject->name.' for pvalues');
            PValue::where('subject_id',$subject->getKey())->withTrashed()->update(['subject_id' => $subject->toSubjectId]);
            $this->writeDoneInfo('DONE',color:'green');
            $this->writeInfoText('updating subject '.$subject->name.' for teachers');
            Teacher::where('subject_id',$subject->getKey())->withTrashed()->update(['subject_id' => $subject->toSubjectId]);
            $this->writeDoneInfo('DONE',color:'green');
            $this->writeInfoText('updating subject '.$subject->name.' for questions');
            Question::where('subject_id',$subject->getKey())->withTrashed()->update(['subject_id' => $subject->toSubjectId]);
            $this->writeDoneInfo('DONE',color:'green');
        });

        $this->writeInfoText('updating school years for school classes');
        $fromSchoolYears->each(function(SchoolYear $schoolYear){
            SchoolClass::where('school_year_id',$schoolYear->getKey())->withoutGlobalScope('visibleOnly')->withTrashed()->update(['school_year_id' => $schoolYear->toSchoolYearId]);
        });
        $this->writeDoneInfo('DONE',color:'green');

        $this->writeDoneInfo('Updating school location ids for users...');
        $userIds = User::where('school_location_id',$from->getKey())->withTrashed()->pluck('id');
        SchoolLocationUser::where('school_location_id',$from->getKey())->whereIn('user_id',$userIds)->delete();
        $upserts = [];
        $userIds->each(function($id) use (&$upserts, $to){
            $upserts[] = ['school_location_id' => $to->getKey(), 'user_id' => $id];
        });
        SchoolLocationUser::upsert($upserts,['school_location_id','user_id']);

        User::where('school_location_id',$from->getKey())->withTrashed()->update(['school_location_id' => $to->getKey()]);
        $this->writeDoneInfo('DONE',color:'green');
        $this->writeInfoText('updating school location ids for tests');
        Test::where('owner_id',$from->getKey())->withTrashed()->update(['owner_id' => $to->getKey()]);
        $this->writeDoneInfo('DONE',color:'green');
        $this->writeInfoText('updating school location ids for school classess');
        SchoolClass::where('school_location_id',$from->getKey())->withoutGlobalScope('visibleOnly')->withTrashed()->update(['school_location_id' => $to->getKey()]);
        $this->writeDoneInfo('DONE',color:'green');
        $this->writeInfoText('updating school location ids for test takes');
        TestTake::where('school_location_id',$from->getKey())->withTrashed()->update(['school_location_id' => $to->getKey()]);
        $this->writeDoneInfo('DONE',color:'green');
    }

    protected function handlePossibleErrors($notFoundSubjects, $notFoundSchoolYears, $notFoundPeriods, $duplicateClassNames, $start = true)
    {
        $hasErrors = false;
        collect([
            'notFoundSubjects'    => ['name' => 'subjects', 'key' => 'name',],
            'notFoundPeriods'     => ['name' => 'periods', 'key' => 'name',],
            'notFoundSchoolYears' => ['name' => 'school years', 'key' => 'year',],
            'duplicateClassNames' => ['name' => 'duplicate class names',],
        ])->each(function ($value, $key) use (&$hasErrors, $notFoundSubjects, $notFoundSchoolYears, $notFoundPeriods, $duplicateClassNames, $start) {
            if($start){
                $this->writeInfoText('Checking '.$value['name']);
            }
            if ($$key->count() > 0) {
                if(!$start) {
                    $this->echoNoContinueError($hasErrors, $$key, (object)$value, $key === 'duplicateClassNames');
                } else {
                    $this->writeDoneInfo('ERROR',color:'red');
                }
                $hasErrors = true;
            } else if($start){
                    $this->writeDoneInfo('SUCCESS',color:'green');
            }
        });
        if ($hasErrors) {
            if($start) {
                $this->handlePossibleErrors($notFoundSubjects,$notFoundSchoolYears,$notFoundPeriods,$duplicateClassNames,false);
            }
            return Command::FAILURE;
        }
    }

    protected function echoNoContinueError($hasErrors, $notFoundCollection, $data, $duplicates = false)
    {
        if(!$hasErrors){
            $this->components->error('Sorry we can\'t continue due to the following error(s):');
        }
        $list = [];
        if($duplicates){
            $list[] = (sprintf('Error in %s %s', ucfirst($data->name), var_export($notFoundCollection, true)));
        } else {
            $notFoundCollection->each(function($model) use($data, &$list){
                $list[] = (sprintf('Missing or duplicate %s: %s (id:%s)', ucfirst($data->name), $model->{$data->key}, $model->getKey()));
            });
        }
        $this->components->bulletList($list);
    }

    protected function checkSettingValue($type, $setting, $value)
    {
        $errorMessage = null;
        if ($type === 'feature') {
            if (!$fs = FeatureSetting::where('title', $setting)->where('value', $value)->first()) {
                $this->error(sprintf('The feature setting `%s` with value `%s`could not be found for any record, that doesn`t seem to be right', $setting, $value));
                return false;
            }
        } else {
            if (!SchoolLocation::where($setting, $value)->exists()) {
                $this->error(sprintf('No School Location could be found with setting `%s` and value `%s`, that doesn`t seem right', $setting, $value));
                return false;
            }
        }
        return true;
    }

    protected function checkSetting($type, $setting)
    {
        $errorMessage = null;
        if ($type === 'feature') {
            if (!$fs = FeatureSetting::where('title', $setting)->first()) {
                $this->error(sprintf('The feature setting `%s` could not be found for any record, that doesn`t seem to be right', $setting));
                return false;
            }
            if (!$fs->settingable instanceof SchoolLocation) {
                $this->error(sprintf('the feature setting `%s` does not seem to be of the school location model, `%s` model found', $setting, get_class($fs->settingable)));
                return false;
            }
        } elseif ($type === 'column') {
            if (!Schema::hasColumn('school_locations', $setting)) {
                $this->error(sprintf('The column `%s`, could not be found in the school_locations table', $setting));
                return false;
            }
        } else {
            $this->error(sprintf('The type of setting is unknown, needs to be feature or column, `%s` given', $type));
            return false;
        }
        return true;
    }
}
