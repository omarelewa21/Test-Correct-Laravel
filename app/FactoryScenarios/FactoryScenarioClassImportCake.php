<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\SchoolYear;

class FactoryScenarioClassImportCake
{
    public SchoolLocation $schoolLocation;
    public $schoolClasses;
    private array $classNames = [
        'VAKIMPORTER',
        'ANDERVAKIMPORTER',
        'ANDERVAK2IMPORTER',
        'ANDERVAK3IMPORTER',
        'ANDERVAK4IMPORTER',
        'KLASIMPORTER',
        'ANDEREKLASIMPORTER',
        'ANDEREKLAS2IMPORTER',
        'ANDEREKLAS3IMPORTER',
        'ANDEREKLAS4IMPORTER',
        'ANDEREKLAS5IMPORTER',
    ];

    public static function create(?SchoolLocation $schoolLocation, bool $trashIfExists = false)
    {
        $factory = new static;

        $schoolYear = $factory->getSchoolYearFromSchoolLocation($schoolLocation);

        if ($factory->classesAlreadyExist()) {
            if ($trashIfExists) {
                $factory->trashSchoolClasses();
            } else {
                throw new \Exception('Cannot create import classes, because one or multiple with the same name already exist');
            }
        }

        $factory->schoolClasses = collect($factory->classNames)->map(function ($name) use ($schoolYear) {
            return FactorySchoolClass::create(schoolYear: $schoolYear, name: $name)->schoolClass;
        });

        return $factory;
    }

    private function classesAlreadyExist(): bool
    {
        return SchoolClass::whereSchoolLocationId($this->schoolLocation->getKey())
            ->whereIn('name', $this->classNames)
            ->exists();
    }
    private function trashSchoolClasses(): bool
    {
        return SchoolClass::whereSchoolLocationId($this->schoolLocation->getKey())
            ->whereIn('name', $this->classNames)
            ->forceDelete();
    }

    private function getSchoolYearFromSchoolLocation(?SchoolLocation $schoolLocation): SchoolYear
    {
        if (!$schoolLocation) {
            $this->schoolLocation = FactorySchoolLocation::create(
                FactorySchool::create('Importer School')->school,
                'Importer schoollocation'
            )->addEducationlevel(1)
                ->schoolLocation;

            return FactorySchoolYear::create(
                $this->schoolLocation,
                (int)Carbon::today()->format('Y')
            )
                ->addPeriodFullYear()
                ->schoolYear;
        }

        $this->schoolLocation = $schoolLocation;
        $helper = ActingAsHelper::getInstance();
        $helper->setUser($schoolLocation->users()->first());
        return SchoolYearRepository::getCurrentSchoolYear();
    }
}