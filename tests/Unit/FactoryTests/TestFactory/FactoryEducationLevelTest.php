<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Factories\FactoryEducationLevel;
use tcCore\User;
use Tests\TestCase;

class FactoryEducationLevelTest extends TestCase
{
    use DatabaseTransactions;
    
    /** @test */
    public function can_get_education_levels_for_User()
    {
        $userId = 1486;

        $educationLevelsCollection = FactoryEducationLevel::getEducationLevelsForUser(User::find($userId));

        $this->assertInstanceOf("Illuminate\Database\Eloquent\Collection", $educationLevelsCollection );
        $this->assertInstanceOf("tcCore\EducationLevel", $educationLevelsCollection->first() );
    }

    /** @test */
    public function can_get_first_education_level_for_User()
    {
        $userId = 1486;

        $educationLevelsModel = FactoryEducationLevel::getFirstEducationLevelForUser(User::find($userId));

        $this->assertInstanceOf("tcCore\EducationLevel", $educationLevelsModel);
    }

    /** @test */
    public function can_get_random_education_level_for_User()
    {
        $userId = 1486;

        $educationLevelsModel = FactoryEducationLevel::getRandomEducationLevelForUser(User::find($userId));

        $this->assertInstanceOf("tcCore\EducationLevel", $educationLevelsModel);
    }

}