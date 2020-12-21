<?php

namespace Tests\Unit\QtiVersionTwoDotTwoDotTwo;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\CompletionQuestion;
use tcCore\User;
use Tests\TestCase;
use tcCore\Http\Helpers\QtiImporter\VersionTwoDotTwoDotZero\QtiResource;
use tcCore\QtiModels\QtiResource as Resource;

class QtiResourceExtendedTextInteraction230002Test extends TestCase
{
    use DatabaseTransactions;

    private $instance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::where('username', 'd1@test-correct.nl')->first());

        $resource = new Resource(
            'ITM-230002',
            'imsqti_item_xmlv2p2',
            storage_path('../tests/_fixtures_qti/230002.xml'),
            '1',
            '88dec4d3-997f-4d3b-95cf-3345bf3c0f4b'
        );
        $this->instance = (new QtiResource($resource))->handle();
    }


    /** @test */
    public function it_can_handle_the_item_body()
    {
        $this->assertInstanceOf(
            CompletionQuestion::class,
            $this->instance->question
        );
    }


}
