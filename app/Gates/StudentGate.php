<?php

namespace tcCore\Gates;

use tcCore\User;

class StudentGate
{
    private $student;

//    public function __construct(User $student)
//    {
//        $this->student = $student;
//    }

    public function setStudent(User $student) {
        $this->student = $student;
    }

    public function canAccessDrawingQuestionBackgroundImage(){
        if (!$this->valid()) {
            return false;
        }
        return true;

    }

   private function valid(){

        dd($this->student->hasRole('Student'));
        return (optional($this->student)->hasRole(['Student']));
    }


}