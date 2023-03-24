<?php

namespace Tests\Unit\Scopes;

use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class TeachersForStudentScopeTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;
    private User $teacherOne;
    private User $studentOne;
    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherOne = ScenarioLoader::get('user');
        $this->studentOne = ScenarioLoader::get('student1');
    }

    /** @test */
    public function it_should_return_the_teachers_for_a_given_student()
    {
        /**
         * student one is in class::id = 1 which has teacher one
         */
        $teachers = User::teachersForStudent($this->studentOne)->get();
        $this->assertCount(1, $teachers);

        $this->assertCount(
            1,
            $teachers->filter(function ($teacher) {
                return $teacher->id == $this->teacherOne->id;
            })
        );
    }

    /**
     * @test
     */
    public function it_should_fail_if_scope_is_called_on_a_none_student()
    {
        try {
            User::teachersForStudent($this->teacherOne);
        } catch (\Exception $e) {
            $this->assertEquals('Not a valid student', $e->getMessage());
            return;
        }

        $this->assertFalse(true, __METHOD__ . ': should have thrown a exception.');
    }
}