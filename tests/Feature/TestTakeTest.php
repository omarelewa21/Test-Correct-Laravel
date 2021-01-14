<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\TestTake;
use tcCore\User;
use Tests\TestCase;
use Livewire\Livewire;

class TestTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_fetch_the_test_take()
    {
        $testTake = TestTake::whereUuid('afd05e38-3fdd-432a-8ecd-cb09550f0200')->first();
        $data = \tcCore\Http\Livewire\Student\TestTake::getData($testTake);
        $this->assertCount(6, $data);
        $this->assertNull($data->first()->answer);
    }
}
