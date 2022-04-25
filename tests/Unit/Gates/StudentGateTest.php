<?php

namespace Tests\Unit\Gates;

use tcCore\Gates\StudentGate;
use Tests\TestCase;

class StudentGateTest extends TestCase
{
    /** @test */
    public function it_wont_pass_if_user_doesnot_have_role_student()
    {
        $gate = new StudentGate();
        $gate->setStudent(self::getTeacherOne());
        $this->assertFalse($gate->canAccessDrawingQuestionBackgroundImage());
    }
    
    /** @test */
    public function it_will_pass_if_user_is_student()
    {
        $gate = new StudentGate();
        $gate->setStudent(self::getStudentOne());
        $this->assertTrue($gate->canAccessDrawingQuestionBackgroundImage());
    }


}