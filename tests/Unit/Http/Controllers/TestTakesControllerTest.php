<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit\Http\Controllers;

use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Controllers\TestTakesController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\TestTake;
use tcCore\User;
use tcCore\TestQuestion;
use Tests\ScenarioLoader;
use Tests\TestCase;
use Tests\Traits\TestTrait;
use Tests\Traits\TestTakeTrait;
use Tests\Traits\GroupQuestionTrait;
use Tests\Traits\MultipleChoiceQuestionTrait;

/**
 * @group ignore
 */
class TestTakesControllerTest extends TestCase
{

    use TestTrait;
    use TestTakeTrait;
    use GroupQuestionTrait;
    use MultipleChoiceQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;

    protected $loadScenario = FactoryScenarioSchoolSimple::class;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = ScenarioLoader::get('user');
        ActingAsHelper::getInstance()->setUser($this->user);
    }

    /**
     * @test
     */
    public function calculateMaxScore()
    {
        $this->actingAs($this->user);

        $attributes = $this->getTestAttributes();
        $this->createTLCTest($attributes, $this->user);
        $attributes = $this->getAttributesForCarouselGroupQuestion($this->originalTestId);
        $testQuestionId = $this->createGroupQuestion($attributes, $this->user);
        $groupTestQuestion = TestQuestion::find($testQuestionId);
        $attributes = $this->getAttributesForMultipleChoiceQuestion($this->originalTestId);
        for ($i = 0; $i < 10; $i++) {
            $this->createMultipleChoiceQuestionInGroup($attributes, $groupTestQuestion->uuid, $this->user);
        }
        $this->createMultipleChoiceQuestion($attributes, $this->user);
        $testTakeId = $this->initDefaultTestTake($this->originalTestId, $this->user);
        $testTake = TestTake::find($testTakeId);

        $response = $this->get(self::authUserGetRequest(
            'test_take_max_score/' . $testTake->uuid,
            [],
            $this->user
        ));
        $response->assertStatus(200);
        $this->assertEquals(20, $response->getContent());
    }
}