<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use tcCore\Factories\FactorySubject;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoiceTrueFalse;
use tcCore\Factories\Questions\FactoryQuestionOpenLong;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;

class FactoryScenarioTestScheikunde extends FactoryScenarioTest
{
    protected function createFactoryTest(): FactoryTest
    {
        $scheikundeSubjectInSameSectionAsUser = FactorySubject::getSubjectsForUser($this->user)
            ->where('name',  'Scheikunde')
            ->first()
            ->getKey();
        if(is_null($scheikundeSubjectInSameSectionAsUser)){
            //checking for Scheikunde in the same section, does not mean it is a valid subject for the user.
            throw new \Exception('No Scheikunde subject available for this user.');
        }

        return FactoryTest::create($this->user)
            ->setProperties([
                'name' => $this->testName ?? 'Test with Scheikunde as subject. ' . Carbon::now()->format('ymd-Hi'),
                'subject_id' => $scheikundeSubjectInSameSectionAsUser
            ])
            ->addQuestions([
                FactoryQuestionOpenShort::create(),
                FactoryQuestionMultipleChoiceTrueFalse::create(),
                FactoryQuestionOpenLong::create(),
            ]);
    }
}