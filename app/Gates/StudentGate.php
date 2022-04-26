<?php

namespace tcCore\Gates;

use Illuminate\Support\Facades\DB;
use tcCore\DrawingQuestion;
use tcCore\TestParticipant;
use tcCore\TestQuestion;
use tcCore\TestTake;
use tcCore\User;

class StudentGate
{
    private $student;

//    public function __construct(User $student)
//    {
//        $this->student = $student;
//    }

    public function setStudent(User $student)
    {
        $this->student = $student;
    }

    public function canAccessDrawingQuestionQuestionBackgroundImage(DrawingQuestion $drawingQuestion)
    {
        if (!$this->valid()) {
            return false;
        }

        return $this->isStudentParticipantInOpenTestUsingThisDrawingQuestion($drawingQuestion);

    }

    public function isStudentParticipantInOpenTestUsingThisDrawingQuestion($drawingQuestion)
    {
        return (TestParticipant::participationsOfUserAndQuestion($this->student, $drawingQuestion)->count() > 0);
    }

    private function valid()
    {
        return optional($this->student)->isA('Student');
    }


}