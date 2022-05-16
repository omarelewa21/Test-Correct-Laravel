<?php

namespace Tests\Unit\Gates;

use Mockery\MockInterface;
use tcCore\DrawingQuestion;
use tcCore\Gates\StudentGate;
use tcCore\Gates\TeacherGate;
use tcCore\User;
use Tests\TestCase;

class TeacherGateTest extends TestCase
{
    /**
     * @var StudentGate
     */
    private $gate;
    private $drawingQuestion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gate = new TeacherGate();
        $this->gate->setTeacher(self::getTeacherOne());
        $this->drawingQuestion = DrawingQuestion::find(25);
    }

    /** @test */
    public function it_wont_pass_if_user_doesnot_have_role_teacher()
    {
        $this->gate->setTeacher(self::getStudentOne());
        $this->assertFalse($this->gate->canAccessDrawingQuestionBackgroundImage($this->drawingQuestion));
    }
    
    /** @test */
    public function it_will_pass_if_user_is_teacher_and_TeacherIsUsingThisDrawingQuestion()
    {
        $this->partialMock(TeacherGate::class, function(MockInterface $mock){
           $mock->shouldReceive('isTeacherUsingThisDrawingQuestion')
               ->once()
               ->andReturn(true);
        });
        $this->gate = app()->make(TeacherGate::class);
        $this->gate->setTeacher(self::getTeacherOne());

        $this->assertTrue($this->gate->canAccessDrawingQuestionBackgroundImage($this->drawingQuestion));
    }

    /** @test */
    public function it_will_fail_if_user_is_teacher_and_TeacherIsNotUsingThisDrawingQuestion()
    {
        $this->partialMock(TeacherGate::class, function(MockInterface $mock){
            $mock->shouldReceive('isTeacherUsingThisDrawingQuestion')
                ->once()
                ->andReturn(false);
        });
        $this->gate = app()->make(TeacherGate::class);
        $this->gate->setTeacher(self::getTeacherOne());

        $this->assertFalse($this->gate->canAccessDrawingQuestionBackgroundImage($this->drawingQuestion));
    }

    /** @test */
    public function it_will_pass_if_student_is_participant_in_a_test_containing_this_image()
    {
        // it is needed to login the user because scope filtered on testTakes requires the auth user;
        $this->actingAs(self::getTeacherOne());
        $this->gate->setTeacher(self::getTeacherOne());
        $this->assertTrue($this->gate->canAccessDrawingQuestionBackgroundImage($this->drawingQuestion));
    }

    /** @test */
    public function it_will_fail_if_student_is_not_particiapating_in_a_test_containing_this_drawingQuestion()
    {
        // it is needed to login the user because scope filtered on testTakes requires the auth user;
        $teacher = User::find(1500);
        $this->actingAs($teacher);
        $this->gate->setTeacher($teacher);
        $this->assertFalse($this->gate->canAccessDrawingQuestionBackgroundImage($this->drawingQuestion));
    }


}