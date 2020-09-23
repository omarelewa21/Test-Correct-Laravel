<?php

namespace Tests\Unit\QtiV2dot2dot2;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\CompletionQuestion;
use tcCore\Http\Helpers\QtiImporter\v2dot2dot2\QtiParser;
use tcCore\OpenQuestion;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Http\Helpers\QtiImporter\v2dot2dot0\QtiResource;
use tcCore\QtiModels\QtiResource as Resource;

class QtiResourceExtendedTextInteractionTest extends TestCase
{
    use DatabaseTransactions;

    private $instance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::where('username', 'd1@test-correct.nl')->first());

        $resource = new Resource(
            'ITM-230041',
            'imsqti_item_xmlv2p2',
            storage_path('../tests/_fixtures_qti/230041.xml'),
            '1',
            '88dec4d3-997f-4d3b-95cf-3345bf3c0f4b'
        );
        $this->instance = (new QtiResource($resource))->handle();
    }


    /** @test */
    public function it_can_handle_the_item_body()
    {
        $this->assertInstanceOf(
            OpenQuestion::class,
            $this->instance->question
        );
    }


}
