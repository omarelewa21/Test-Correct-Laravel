<?php

namespace Tests\Unit\Gates;

use Mockery\MockInterface;
use tcCore\DrawingQuestion;
use tcCore\Gates\StudentGate;
use tcCore\User;
use Tests\TestCase;

class StudentGateTest extends TestCase
{
    /**
     * @var StudentGate
     */
    private $gate;
    private $drawingQuestion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gate = new StudentGate();
        $this->gate->setStudent(self::getStudentOne());
        $this->drawingQuestion = DrawingQuestion::find(25);
    }

    /** @test */
    public function it_wont_pass_if_user_doesnot_have_role_student()
    {
        $this->gate->setStudent(self::getTeacherOne());
        $this->assertFalse($this->gate->canAccessDrawingQuestionQuestionBackgroundImage($this->drawingQuestion));
    }
    
    /** @test */
    public function it_will_pass_if_user_is_student_and_has_is_participantInOpenTestUsingThisDrawingQuestion()
    {
        $this->partialMock(StudentGate::class, function(MockInterface $mock){
           $mock->shouldReceive('isStudentParticipantInOpenTestUsingThisDrawingQuestion')
               ->once()
               ->andReturn(true);
        });
        $this->gate = app()->make(StudentGate::class);
        $this->gate->setStudent(self::getStudentOne());

        $this->assertTrue($this->gate->canAccessDrawingQuestionQuestionBackgroundImage($this->drawingQuestion));
    }

    /** @test */
    public function it_will_fail_if_user_is_student_and_has_no_participantInOpenTestUsingThisDrawingQuestion()
    {
        $this->partialMock(StudentGate::class, function(MockInterface $mock){
            $mock->shouldReceive('isStudentParticipantInOpenTestUsingThisDrawingQuestion')
                ->once()
                ->andReturn(false);
        });
        $this->gate = app()->make(StudentGate::class);
        $this->gate->setStudent(self::getStudentOne());

        $this->assertFalse($this->gate->canAccessDrawingQuestionQuestionBackgroundImage($this->drawingQuestion));
    }

    /** @test */
    public function it_will_pass_if_student_is_participant_in_a_test_containing_this_image()
    {
        $this->gate->setStudent(self::getStudentOne());
        $this->assertTrue($this->gate->canAccessDrawingQuestionQuestionBackgroundImage($this->drawingQuestion));
    }

    /** @test */
    public function it_will_fail_if_student_is_not_particiapating_in_a_test_containing_this_drawingQuestion()
    {
        $this->gate->setStudent(User::find(1498));
        $this->assertFalse($this->gate->canAccessDrawingQuestionQuestionBackgroundImage($this->drawingQuestion));
    }
}