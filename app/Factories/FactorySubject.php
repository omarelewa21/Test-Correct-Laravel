<?php

namespace tcCore\Factories;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Period;
use tcCore\Subject;
use tcCore\User;

class FactorySubject
{
    use DoWhileLoggedInTrait;

    private User $user;

    public static function getSubjectsForUser(User $user)
    {
        $SubjectFactory = new self;
        $SubjectFactory->user = $user;

        return $SubjectFactory->getValidSubjects();
    }

    protected function getValidSubjects()
    {
        return $this->doWhileLoggedIn(function () {
            return Subject::filtered(['user_current' => (string)$this->user->id], ['name' => 'asc'])->with('baseSubject')->get();
        }, $this->user);
    }

    public static function getFirstSubjectForUser(User $user)
    {
        $SubjectFactory = new self;
        $SubjectFactory->user = $user;

        return $SubjectFactory->getValidSubjects()->first();
    }

    public static function getRandomSubjectForUser(User $user)
    {
        $SubjectFactory = new self;
        $SubjectFactory->user = $user;

        return $SubjectFactory->getValidSubjects()->random();
    }
}