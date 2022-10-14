<?php

namespace Tests\Unit\Scopes;

use tcCore\EducationLevel;
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
}