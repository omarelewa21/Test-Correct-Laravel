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
        $testTake = TestTake::whereUuid('25b9e935-1080-476b-a825-fb9b4f828fb6')->first();
        $data = \tcCore\Http\Livewire\Student\TestTake::getData($testTake);
        $this->assertCount(6, $data);
//        $this->assertNull($data->first()->answer);

        dd($data->filter(function($item, $key){
            if ($key==4|| $key == 0) {
                return $item;
            }
        }));
    }
}
