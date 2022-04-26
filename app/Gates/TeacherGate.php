<?php

namespace tcCore\Gates;

use tcCore\DrawingQuestion;
use tcCore\TestQuestion;
use tcCore\TestTake;
use tcCore\User;

class TeacherGate
{
    /**
     * @var User
     */
    private $teacher;

    public function setTeacher(User $teacher)
    {
        $this->teacher = $teacher;
    }


    public function canAccessDrawingQuestionBackgroundImage(DrawingQuestion $drawingQuestion)
    {
        if (!$this->valid()) {
            return false;
        }

        return $this->isTeacherUsingThisDrawingQuestion($drawingQuestion);

    }

    public function isTeacherUsingThisDrawingQuestion(DrawingQuestion $drawingQuestion){
      $count = TestQuestion::where('question_id', $drawingQuestion->getKey())
          ->whereIn('test_id',
            TestTake::filtered([
            'withoutParticipants' => true,
        ])->pluck('test_id'))->count();

        return ($count > 0);
    }

    private function valid()
    {
        return optional($this->teacher)->isA('Teacher');
    }



}