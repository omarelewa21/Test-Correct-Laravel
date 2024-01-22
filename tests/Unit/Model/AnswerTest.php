<?php

namespace Tests\Unit\Model;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Factories\FactoryTestTake;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeDiscussing;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

/**
 * testing calculateFinalRating();
 * testing hasCoLearningDiscrepancy();
 */
class AnswerTest extends TestCase
{
    use DatabaseTransactions;

    const TEACHER_RATING = 5;
    const SYSTEM_RATING = 4;
    const STUDENT_RATING_1 = 1;
    const STUDENT_RATING_2 = 2;
    const STUDENT_RATING_NULL = null;

    protected FactoryTestTake $testTakeFactory;
    protected User $user;

    protected $loadScenario = FactoryScenarioSchoolSimple::class;

    private $systemAnswerRating;
    private $teacherAnswerRating;
    private $studentAnswerRatings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = ScenarioLoader::get('user');
        $this->testTakeFactory = FactoryScenarioTestTakeDiscussing::create($this->user)->testTakeFactory;
    }

    /**
     * tests that matching student ratings result in a final rating that is equal to the student rating
     * compares basic ratings without toggle data
     * @dataProvider matchingJsonData
     * @test
     */
    public function matchingStudentRatingsWithToggleData($firstAnswerRatingJson, $secondAnswerRatingJson)
    {
        $answer = $this->setUpTestTake(hasTeacherRatings: false);

        $firstAnswerRating = $this->studentAnswerRatings->first();
        $lastAnswerRating = $this->studentAnswerRatings->last();

        //set matching student rating
        $firstAnswerRating->rating = self::STUDENT_RATING_1;
        $lastAnswerRating->rating = self::STUDENT_RATING_1;

        //set matching toggle data (completion question)
        $firstAnswerRating->json = $firstAnswerRatingJson;
        $lastAnswerRating->json = $secondAnswerRatingJson;

        // count unique [ self::STUDENT_RATING_1 , self::STUDENT_RATING_1 ] = 1
        $this->assertEquals(1, $this->studentAnswerRatings->unique('rating')->count());

        $this->assertEquals(null, $answer->final_rating);

        $answer->final_rating = $answer->calculateFinalRating();

        $this->assertEquals(self::STUDENT_RATING_1, $answer->final_rating);
    }

    /**
     * tests that non matching student ratings result in null final rating
     * compares basic ratings without toggle data
     * @dataProvider nonMatchingJsonData
     * @test
     */
    public function nonMatchingStudentRatingsWithToggleData($firstAnswerRatingJson, $secondAnswerRatingJson)
    {
        $answer = $this->setUpTestTake(hasTeacherRatings: false);

        $firstAnswerRating = $this->studentAnswerRatings->first();
        $lastAnswerRating = $this->studentAnswerRatings->last();

        //set non matching student rating
        $firstAnswerRating->rating = self::STUDENT_RATING_1;
        $lastAnswerRating->rating = self::STUDENT_RATING_1;

        //set non matching toggle data (completion question)
        $firstAnswerRating->json = $firstAnswerRatingJson;
        $lastAnswerRating->json = $secondAnswerRatingJson;

        // count unique: when ratings are the same but toggle data is different, the ratings are not unique
        $this->assertEquals(1, $this->studentAnswerRatings->unique('rating')->count());

        $this->assertEquals(null, $answer->final_rating);

        $answer->final_rating = $answer->calculateFinalRating();

        // non matching student ratings should return null,
        // no SYSTEM, TEACHER or matching STUDENT ratings means no final rating
        $this->assertEquals(
            expected:null,
            actual: $answer->final_rating,
            message: 'answer ratings that have the same rating but different toggle data are not equal and should not have a final rating');
    }

    /**
     * tests that matching student ratings result in a final rating that is equal to the student rating
     * compares basic ratings without toggle data
     * @dataProvider matchingRatingData
     * @test
     */
    public function matchingStudentRatingsWithoutToggleData($firstRating, $secondRating)
    {
        $answer = $this->setUpTestTake(hasTeacherRatings: false);

        //set matching student rating
        $this->studentAnswerRatings->first()->rating = $firstRating;
        $this->studentAnswerRatings->last()->rating = $secondRating;

        // count unique [ self::STUDENT_RATING_1 , self::STUDENT_RATING_1 ] = 1
        $this->assertEquals(1, $this->studentAnswerRatings->unique('rating')->count());

        $this->assertEquals(null, $answer->final_rating);

        $answer->final_rating = $answer->calculateFinalRating();

        $this->assertEquals($firstRating, $answer->final_rating);
    }

    /**
     * tests that non matching student ratings result in null final rating
     * compares basic ratings without toggle data
     * @dataProvider nonMatchingRatingData
     * @test
     */
    public function nonMatchingStudentRatingsWithoutToggleData($firstRating, $secondRating)
    {
        $answer = $this->setUpTestTake(
            hasTeacherRatings: false,
            hasSystemRating: false,
            hasStudentRatings: true,
        );

        //set matching student rating
        $this->studentAnswerRatings->first()->update(['rating' => $firstRating]);
        $this->studentAnswerRatings->last()->update(['rating' => $secondRating]);
//        $this->studentAnswerRatings->refresh();

        // count unique [ self::STUDENT_RATING_1 , self::STUDENT_RATING_2 ] = 2
        $this->assertEquals(2, $this->studentAnswerRatings->unique('rating')->count());

        $this->assertEquals(null, $answer->final_rating);

        $answer->final_rating = $answer->calculateFinalRating();

        // non matching student ratings should return null,
        // no SYSTEM, TEACHER or matching STUDENT ratings means no final rating
        $this->assertEquals(null, $answer->final_rating);
    }


    /**
     * test that a when a teacher rating is available, it is used as final rating
     * and matching student ratings are ignored
     * @test
     */
    public function teacherAnswerRatingAvailable()
    {
        $answer = $this->setUpTestTake(hasSystemRating: true);

        //set matching student rating
        $this->studentAnswerRatings->first()->rating = self::STUDENT_RATING_1;
        $this->studentAnswerRatings->last()->rating = self::STUDENT_RATING_1;

        //set system rating to 4
        $this->systemAnswerRating->rating = self::SYSTEM_RATING;

        $this->assertEquals(1, $this->studentAnswerRatings->unique('rating')->count());

        $this->assertEquals(null, $answer->final_rating);

        $answer->final_rating = $answer->calculateFinalRating();

        $this->assertEquals(self::TEACHER_RATING, $answer->final_rating);
    }

    /**
     * test that a when a system rating is available, but no teacher rating, it is used as final rating
     * and matching student ratings are ignored
     * @test
     */
    public function systemAnswerRatingAvailable()
    {
        //no teacher ratings because they override system ratings
        $answer = $this->setUpTestTake(
            hasTeacherRatings: false,
            hasSystemRating: true,
            hasStudentRatings: true,
        );

        //set matching student rating
        $this->studentAnswerRatings->first()->rating = self::STUDENT_RATING_1;
        $this->studentAnswerRatings->last()->rating = self::STUDENT_RATING_1;

        $this->assertEquals(1, $this->studentAnswerRatings->unique('rating')->count());

        $this->assertEquals(null, $answer->final_rating);

        $answer->final_rating = $answer->calculateFinalRating();

        //assert that system rating is used as final rating
        $this->assertEquals(self::SYSTEM_RATING, $answer->final_rating);
    }

    /**
     * test that a when no teacher or system ratings are available
     * and student ratings are created but null, the final rating is null
     * @test
     */
    public function NoRatingsAvailable()
    {
        $answer = $this->setUpTestTake(
            hasTeacherRatings: false,
            hasSystemRating: false,
            hasStudentRatings: true,
        );

        //set matching student rating
        $this->studentAnswerRatings->first()->rating = null;
        $this->studentAnswerRatings->last()->rating = null;

        $this->assertEquals(1, $this->studentAnswerRatings->unique('rating')->count());

        $this->assertEquals(null, $answer->final_rating);

        $answer->final_rating = $answer->calculateFinalRating();

        //assert that the final rating is NULL because of there are no valid ratings available
        $this->assertEquals(null, $answer->final_rating);
    }

    /**
     * test that a when no teacher, system or student ratings are available, the final rating is null
     * @test
     */
    public function NoRatingsAvailableAtAll()
    {
        $answer = $this->setUpTestTake(
            hasTeacherRatings: false,
            hasStudentRatings: false,
            hasSystemRating: false
        );

        $this->assertEquals(0, $this->studentAnswerRatings->count());
        $this->assertEquals(null, $this->teacherAnswerRating);
        $this->assertEquals(null, $this->systemAnswerRating);

        $this->assertEquals(null, $answer->final_rating);

        $answer->final_rating = $answer->calculateFinalRating();

        //assert that the final rating is NULL because of there are no valid ratings available
        $this->assertEquals(null, $answer->final_rating);
    }

    /**
     * @dataProvider nonMatchingJsonData
     * @test
     */
    public function answerHasCoLearningDiscrepancy($firstAnswerRatingJson, $secondAnswerRatingJson)
    {
        $answer = $this->setUpTestTake(hasTeacherRatings: false);

        $firstAnswerRating = $this->studentAnswerRatings->first();
        $lastAnswerRating = $this->studentAnswerRatings->last();
        //set non matching student rating
        $firstAnswerRating->rating = self::STUDENT_RATING_1;
        $lastAnswerRating->rating = self::STUDENT_RATING_1;

        //set non matching toggle data (completion question)
        $firstAnswerRating->json = $firstAnswerRatingJson;
        $lastAnswerRating->json = $secondAnswerRatingJson;

        // count unique: when ratings are the same but toggle data is different, the ratings are not unique
        $this->assertEquals(1, $this->studentAnswerRatings->unique('rating')->count());

        $this->assertEquals(null, $answer->final_rating);

        $this->assertTrue(
            condition: $answer->hasCoLearningDiscrepancy(),
            message: 'answer should have co-learning discrepancy because of non matching toggle data'
        );
    }

    /**
     * @dataProvider matchingJsonData
     * @test
     */
    public function answerDoesNotHaveCoLearningDiscrepancy($firstAnswerRatingJson, $secondAnswerRatingJson)
    {
        $answer = $this->setUpTestTake(hasTeacherRatings: false);

        $firstAnswerRating = $this->studentAnswerRatings->first();
        $lastAnswerRating = $this->studentAnswerRatings->last();

        //set matching student rating
        $firstAnswerRating->rating = self::STUDENT_RATING_1;
        $lastAnswerRating->rating = self::STUDENT_RATING_1;
        $this->assertEquals(1, $this->studentAnswerRatings->unique('rating')->count());

        //set matching toggle data (completion question)
        $firstAnswerRating->json = $firstAnswerRatingJson;
        $lastAnswerRating->json = $secondAnswerRatingJson;

        // count unique: when the toggle data is the same, there are no discrepancies and the ratings do not matter
        $this->assertFalse(
            condition: $answer->hasCoLearningDiscrepancy(),
            message: 'answer should not have co-learning discrepancies because matching toggle data');

    }

    public static function nonMatchingJsonData()
    {
        return [
            [
                ['1' => 'answer1', '2' => 'answer2'],
                ['1' => 'answer3', '2' => 'answer4'],
            ],
            [
                ['1' => 'answer1', '2' => 'answer2'],
                ['1' => 'answer1', '2' => 'answer4'],
            ],
            [
                ['1' => 'answer1', '2' => 'answer2'],
                ['1' => 'answer3', '2' => 'answer2'],
            ],
            [
                ['1' => 'answer1', '2' => 'answer2'],
                ['2' => 'answer3', '3' => 'answer2'],
            ],
            [
                ['2' => 'answer1'],
                ['1' => 'answer3', '3' => 'answer2'],
            ],
            [
                ['2' => 'answer1', '3' => 'answer2', '4' => 'answer3'],
                ['1' => 'answer3', '3' => 'answer2'],
            ],
        ];
    }

    public static function matchingJsonData()
    {
        return [
            [
                ['1' => 'answer1', '2' => 'answer2'],
                ['1' => 'answer1', '2' => 'answer2'],
            ],
            [
                ['2' => 'answer1', '1' => 'answer2'],
                ['2' => 'answer1', '1' => 'answer2'],
            ],
            [
                [1 => 'answer1', 2 => 'answer2'],
                [1 => 'answer1', 2 => 'answer2'],
            ],
            [
                ['1' => 'answer1', 2 => 'answer2', 'berry' => 'answer3'],
                ['1' => 'answer1', 2 => 'answer2', 'berry' => 'answer3'],
            ],
            [
                ['1' => 'answer1', 2 => 'answer2', 'berry' => 'answer3'],
                null,
            ],
        ];
    }

    public static function nonMatchingRatingData()
    {
        return [
            [
                self::STUDENT_RATING_1,
                self::STUDENT_RATING_2,
            ],
            [
                self::STUDENT_RATING_2,
                self::STUDENT_RATING_NULL,
            ],
        ];
    }

    public static function matchingRatingData()
    {
        return [
            [
                self::STUDENT_RATING_1,
                self::STUDENT_RATING_1,
            ],
            [
                0,
                0,
            ],
        ];
    }

    /**
     * @return Answer
     */
    private function setUpTestTake(bool $hasTeacherRatings = true, bool $hasStudentRatings = true, bool $hasSystemRating = false): Answer
    {
        // $this->testTakeFactory doesn't have student or teacher answerRatings yet, add when needed for test
        if($hasTeacherRatings) $this->testTakeFactory->addTeacherAnswerRatings();
        if($hasStudentRatings) $this->testTakeFactory->addStudentAnswerRatings();
        $questionSubtype = $hasSystemRating ? 'matching' : 'completion';

        $testTake = $this->testTakeFactory->testTake;
        $answers = $testTake->testParticipants->flatMap->answers;

        $firstAnwswer = $answers->first(fn ($answer) => $answer->question->subtype === $questionSubtype);

        $answerRatings = $firstAnwswer->answerRatings;

        $this->teacherAnswerRating = $answerRatings->where('type', AnswerRating::TYPE_TEACHER)->first();
        if($this->teacherAnswerRating) {
            $this->teacherAnswerRating->update(['rating' => self::TEACHER_RATING]);
        }

        $this->studentAnswerRatings = $answerRatings->where('type', AnswerRating::TYPE_STUDENT);

        $systemAnswerRatings = $answerRatings->where('type', AnswerRating::TYPE_SYSTEM);
        $this->systemAnswerRating = $systemAnswerRatings->isNotEmpty()
            ? $systemAnswerRatings->first()
            : null;
        if($this->systemAnswerRating) {
            $this->systemAnswerRating->update(['rating' => self::SYSTEM_RATING]);
        }

        return $firstAnwswer;
    }
}
