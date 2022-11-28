<?php

namespace Tests\Unit\Scopes;

use tcCore\Attainment;
use tcCore\EducationLevel;
use tcCore\LearningGoal;
use tcCore\User;
use Tests\TestCase;

class EductionLevelYearForStudentTest extends TestCase
{
    /** @test */
    public function it_should_return_the_education_level_years_for_a_student()
    {
        /**
         * student one  is only in education_level_year 1
         */
        $years = EducationLevel::yearsForStudent($this->getStudentOne());
        $this->assertCount(1, $years);

        $this->assertCount(
            1,
            $years->filter(function ($year) {
                return 1 == $year;
            })
        );
    }

    /** @test */
    public function it_should_return_attainmentType_as_learningGoal_when_in_bovenbouw()
    {
        $this->assertEquals(
            LearningGoal::TYPE,
            EducationLevel::getAttainmentType($this->getStudentOne())
        );
    }

    /**
     * @test
     */
    public function it_should_return_attainmentType_as_attainment_when_in_bovenbouw()
    {
        $this->assertEquals(
            Attainment::TYPE,
            EducationLevel::getAttainmentType(User::where('username', 'student_p_value_1@sobit.nl')->first())
        );
    }

    /**
     * @test
     * @dataProvider provideMinAttainmentYear
     */
    public function it_should_return_four_when_education_level_vwo_is_asked_for_min_attainment_year($name, $minAttainmentYear)
    {
        $this->assertEquals(
            $minAttainmentYear,
            EducationLevel::firstWhere('name', $name)->min_attainment_year
        );
    }

    public function provideMinAttainmentYear()
    {
        return [
            ['VWO', 4],
            ['Gymnasium', 4],
            ['Havo', 4],
            ['Mavo / VMBO tl', 3],
            ['Vmbo gl', 3],
            ['Vmbo kb', 3],
            ['Vmbo bb', 3],
            ['Havo/VWO', 3],
        ];


    }


}
