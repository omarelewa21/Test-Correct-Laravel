<?php

namespace tcCore\Factories;

use tcCore\Factories\Traits\RandomCharactersGeneratable;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\User;

class FactorySchoolLocation
{
    use RandomCharactersGeneratable;

    public School $school;
    public SchoolLocation $schoolLocation;
    public User $schoolManager;
    protected string $schoolLocationName;

    public static function create(School $school, string $schoolLocationName = null, array $properties = []): FactorySchoolLocation
    {
        $factory = new static;
        $factory->school = $school;

        if ($schoolLocationName === null) {
            $factory->schoolLocationName = 'SL-' . $factory->randomCharacters(5);
        } else {
            $factory->schoolLocationName = $schoolLocationName;
        }

        $schoolLocationProperties = array_merge($factory->definition(), $properties);

        $factory->schoolLocation = SchoolLocation::create($schoolLocationProperties);

        $factory->schoolManager = FactoryUser::createSchoolManager($factory->schoolLocation)->user;

        return $factory;
    }

    public function addEducationlevel(int $educationLevelid): FactorySchoolLocation
    {
        $this->schoolLocation->educationLevels()->attach($educationLevelid);

        return $this;
    }

    public function addEducationlevels(array $educationLevelIds): FactorySchoolLocation
    {
        foreach ($educationLevelIds as $educationLevelId) {
            $this->schoolLocation->educationLevels()->attach((int)$educationLevelId);
        }

        return $this;
    }


    protected function definition(): array
    {
        return [
            "name"                                   => $this->schoolLocationName,
            "customer_code"                          => strtoupper($this->schoolLocationName),
            "user_id"                                => $this->school->user_id ?? null,
            "school_id"                              => $this->school->getKey() ?? null,
            "grading_scale_id"                       => "1",
            "activated"                              => "1",
            "number_of_students"                     => "10",
            "number_of_teachers"                     => "10",
            "external_main_code"                     => "FF",
            "external_sub_code"                      => "00",
            "is_rtti_school_location"                => "0",
            "is_open_source_content_creator"         => "0",
            "is_allowed_to_view_open_source_content" => "0",
            "main_address"                           => "AgrobusinessPark 75",
            "invoice_address"                        => "AgrobusinessPark",
            "visit_address"                          => "AgrobusinessPark",
            "main_postal"                            => "6708PV",
            "invoice_postal"                         => "6708PV",
            "visit_postal"                           => "6708PV",
            "main_city"                              => "Wageningen",
            "invoice_city"                           => "Wageningen",
            "visit_city"                             => "Wageningen",
            "main_country"                           => "Netherlands",
            "invoice_country"                        => "Netherlands",
            "visit_country"                          => "Netherlands",
        ];
    }
}