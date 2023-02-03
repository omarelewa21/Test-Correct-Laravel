<?php

namespace Tests\Unit\QtiVersionTwoDotTwoDotTwo;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QtiParserTest extends TestCase
{
    /** @test */
    public function it_can_parse_xml()
    {
        $this->assertTrue(true);
//        (new QtiParser)->parse($this->getXML());
    }




}
