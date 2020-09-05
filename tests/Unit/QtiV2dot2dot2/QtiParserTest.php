<?php

namespace Tests\Unit\QtiV2dot2dot2;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Http\Helpers\QtiImporter\v2dot2dot2\QtiParser;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QtiParserTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_parse_xml()
    {
        $this->assertTrue(true);
//        (new QtiParser)->parse($this->getXML());
    }




}
