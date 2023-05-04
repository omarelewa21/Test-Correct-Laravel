<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use tcCore\Factories\FactorySubject;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoiceTrueFalse;
use tcCore\Factories\Questions\FactoryQuestionOpenLong;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;

class FactoryScenarioTestBiologie extends FactoryScenarioTest
{
    protected function createFactoryTest(): FactoryTest
    {
        $biologieSubjectInSameSectionAsUser = FactorySubject::getSubjectsForUser($this->user)
            ->where('name',  'Biologie')
            ->first()
            ->getKey();

        if(is_null($biologieSubjectInSameSectionAsUser)){
            //checking for Biologie in the same section, does not mean it is a valid subject for the user.
            throw new \Exception('No Biologie subject available for this user.');
        }

        return FactoryTest::create($this->user)
            ->setProperties([
                'name' => $this->testName ?? 'Test with Biologie as subject. ' . Carbon::now()->format('ymd-Hi'),
                'subject_id' => $biologieSubjectInSameSectionAsUser
            ])
            ->addQuestions([
                FactoryQuestionOpenShort::create(),
                FactoryQuestionMultipleChoiceTrueFalse::create(),
                FactoryQuestionOpenLong::create(),
            ]);
    }
}