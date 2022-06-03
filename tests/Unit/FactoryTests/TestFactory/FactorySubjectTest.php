<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Factories\FactorySubject;
use tcCore\User;
use Tests\TestCase;

class FactorySubjectTest extends TestCase
{
    use DatabaseTransactions;
    
    /** @test */
    public function can_get_subjects_for_User()
    {
        $userId = 1486;

        $subjectsCollection = FactorySubject::getSubjectsForUser(User::find($userId));

        $this->assertInstanceOf("Illuminate\Database\Eloquent\Collection", $subjectsCollection );
        $this->assertInstanceOf("tcCore\Subject", $subjectsCollection->first() );
    }

    /** @test */
    public function can_get_first_subject_for_User()
    {
        $userId = 1486;

        $subjectsModel = FactorySubject::getFirstSubjectForUser(User::find($userId));

        $this->assertInstanceOf("tcCore\Subject", $subjectsModel);
    }

    /** @test */
    public function can_get_random_subject_for_User()
    {
        $userId = 1486;

        $subjectsModel = FactorySubject::getRandomSubjectForUser(User::find($userId));

        $this->assertInstanceOf("tcCore\Subject", $subjectsModel);
    }

}