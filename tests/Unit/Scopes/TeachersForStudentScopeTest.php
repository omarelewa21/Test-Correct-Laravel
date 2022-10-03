<?php

namespace Tests\Unit\Scopes;

use tcCore\User;
use Tests\TestCase;

class TeachersForStudentScopeTest extends TestCase
{
    /** @test */
    public function it_should_return_the_teachers_for_a_given_student()
    {
        /**
         * student one is in class::id = 1 which has teacher one and teacher two;
         */
        $teachers = User::teachersForStudent($this->getStudentOne())->get();
        $this->assertCount(2, $teachers);

        $this->assertCount(
            1,
            $teachers->filter(function ($teacher) {
                return $teacher->id == $this->getTeacherOne()->id;
            })
        );

        $this->assertCount(
            1,
            $teachers->filter(function ($teacher) {
                // teacher TWO
                return $teacher->id == 1496;
            })
        );
    }

    /**
     * @test
     */
    public function it_should_fail_if_scope_is_called_on_a_none_student()
    {
        try {
            User::teachersForStudent($this->getTeacherOne());
        } catch (\Exception $e) {
            $this->assertEquals('Not a valid student', $e->getMessage());
            return;
        }

        $this->assertFalse(true, __METHOD__ . ': should have thrown a exception.');
    }
}