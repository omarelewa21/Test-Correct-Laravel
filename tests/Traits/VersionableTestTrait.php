<?php

namespace Tests\Traits;

use tcCore\EducationLevel;
use tcCore\Http\Enums\WordType;
use tcCore\SchoolLocation;
use tcCore\Subject;
use tcCore\User;
use tcCore\Word;
use tcCore\WordList;
use Tests\ScenarioLoader;

trait VersionableTestTrait
{
    protected User $teacherOne;
    protected User $teacherTwo;
    protected User $teacherThree;

    protected Subject $subject;
    protected EducationLevel $educationLevel;
    protected SchoolLocation $schoolLocation;
    protected int $educationLevelYear = 1;

    protected function setUpVersionableTest(): void
    {
        $this->teacherOne = ScenarioLoader::get('teachers')->get(0);
        $this->teacherTwo = ScenarioLoader::get('teachers')->get(1);
        $this->teacherThree = ScenarioLoader::get('teachers')->get(2);
        $this->subject = Subject::first();
        $this->educationLevel = EducationLevel::first();
        $this->schoolLocation = $this->teacherOne->schoolLocation;
    }


    protected function defaultWordList(
        ?string $name = 'testlist',
        ?User   $user = null,
        ?int    $subjectId = null,
        ?int    $educationLevelId = null,
        ?int    $educationLevelYear = null,
        ?int    $schoolLocationId = null,
    ): WordList {
        return WordList::build(
            $name,
            $user ?? $this->teacherOne,
            $subjectId ?? $this->subject->getKey(),
            $educationLevelId ?? $this->educationLevel->getKey(),
            $educationLevelYear ?? $this->educationLevelYear,
            $schoolLocationId ?? $this->schoolLocation->getKey(),
        );
    }

    protected function defaultWord(
        ?string   $text = 'Kaas',
        ?WordType $type = null,
        ?User     $user = null,
        ?int      $subjectId = null,
        ?int      $educationLevelId = null,
        ?int      $educationLevelYear = null,
        ?int      $schoolLocationId = null,
        ?Word     $subjectWord = null,
    ): Word {
        return Word::build(
            $text,
            $type ?? WordType::SUBJECT,
            $user ?? $this->teacherOne,
            $subjectId ?? $this->subject->getKey(),
            $educationLevelId ?? $this->educationLevel->getKey(),
            $educationLevelYear ?? $this->educationLevelYear,
            $schoolLocationId ?? $this->schoolLocation->getKey(),
            $subjectWord?->getKey() ?? null,
        );
    }
}