<?php

namespace tcCore\Factories\Traits;

use tcCore\Factories\FactoryEducationLevel;
use tcCore\Factories\FactorySubject;
use tcCore\User;

trait FactoryPropertyDefaults
{
    protected function getPropertiesForBuilding(User $user, array $properties): array
    {
        $schoolLocationId = $properties['school_location_id'] ??= $user->school_location_id;
        $subjectId = $properties['subject_id'] ??= FactorySubject::getFirstSubjectForUser($user)->getKey();
        $educationLevelId = $properties['education_level_id'] ??= FactoryEducationLevel::getFirstEducationLevelForUser(
            $user
        )->getKey();
        $educationLevelYear = $properties['education_level_year'] ??= 1;
        return array($schoolLocationId, $subjectId, $educationLevelId, $educationLevelYear);
    }
}