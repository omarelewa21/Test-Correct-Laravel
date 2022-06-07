<?php

namespace Tests\Unit\FactoryTests\TestFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Factories\FactoryPeriod;
use tcCore\Factories\FactorySubject;
use tcCore\Factories\FactoryTest;
use tcCore\Test;
use tcCore\User;
use Tests\TestCase;

class FactoryTestTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /** @test */
    public function can_create_a_test()
    {
        $startCount = Test::count();

        FactoryTest::create();

        $this->assertEquals(++$startCount, Test::count());
    }
    /** @test */
    public function can_create_a_test_for_another_school_user()
    {
        $startCount = Test::count();

        $user = User::find(1500);

        FactoryTest::create($user)->getTestModel();

        $this->assertEquals(++$startCount, Test::count());
    }

    /** @test */
    public function can_create_a_default_test_with_a_valid_Period_id()
    {
        $teacherUserId = 1496;
        $startCount = Test::count();

        $test = FactoryTest::create()->setProperties(['author_id' => $teacherUserId, 'owner_id' => 2]);
        $this->assertEquals(++$startCount, Test::count());

        //asserting model instance === model instance doesn't work
        $this->assertContains(
            $test->getPeriodModel()->toArray(),
            FactoryPeriod::getPeriodsForUser(User::find($teacherUserId))->toArray()
        );
    }

    /** @test */
    public function can_create_a_default_test_with_a_valid_Subject_id()
    {
        $teacherUserId = 1496;
        $startCount = Test::count();

        $test = FactoryTest::create()
            ->setProperties([
                'author_id' => $teacherUserId,
                'owner_id' => 2
            ]);
        $this->assertEquals(++$startCount, Test::count());

        //asserting model instance === model instance doesn't work
        $this->assertContains(
            $test->getSubjectModel()->toArray(),
//            $test->getPropertyByName('testSubject')->toArray(),
            FactorySubject::getSubjectsForUser(User::find($teacherUserId))->toArray()
        );
    }

    /** @test */
    public function can_create_a_test_for_teacher()
    {
        $teacherUserId = 1496;
        $startCount = Test::count();

        $test = FactoryTest::create()
            ->setProperties([
                'author_id' => $teacherUserId,
                'owner_id' => 2
            ])->getTestModel();

        $this->assertEquals(++$startCount, Test::count());
        $this->assertEquals($teacherUserId, $test->fresh()->author_id);
    }

    /** @test */
    public function can_create_a_test_with_shuffle_questions_turned_on()
    {
        $startCount = Test::count();

        $test = FactoryTest::create()
            ->setProperties([
                'shuffle' => '1'
            ])
            ->getTestModel();

        $this->assertEquals(++$startCount, Test::count());
        $this->assertEquals('1', $test->fresh()->shuffle);
    }

    /**
     * @test
     * @dataProvider provideOverrideAttributes
     */
    public function can_create_a_test_and_override_default_attributes($attributes)
    {
        $startCount = Test::count();

        $test = FactoryTest::create()
            ->setProperties($attributes)
            ->getTestModel();

        $this->assertEquals(++$startCount, Test::count());
        foreach ($attributes as $attribute => $value) {
            $this->assertEquals($value, $test->fresh()->getAttribute($attribute));
        }
    }

    public function provideOverrideAttributes()
    {
        return [
            'introduction' => [
                [
                    'introduction' => 'Arbitrary new introduction',
                ]
            ],
            'shuffle (set to true)' => [
                [
                    'shuffle' => "1",
                ]
            ],
            'name' => [
                [
                    'name' => "Custom-test-name",
                ]
            ],
            'abbreviation' => [
                [
                    'abbreviation' => "custom",
                ]
            ],
            'author_id as string' => [
                [
                    'author_id' => "1496",
                ]
            ],
            'author_id as int' => [
                [
                    'author_id' => 1496,
                ]
            ],
            'wrong owner_id' => [
                [
                    'author_id' => 1496,
                    'owner_id' => 2,
                ]
            ],
            'empty_name' => [
                [
                    'name' => "",
                ]
            ],
            'empty_abbreviation' => [
                [
                    'abbreviation' => "",
                ]
            ],
        ];
    }
}
