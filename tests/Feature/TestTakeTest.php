<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\TestTake;
use tcCore\User;
use Tests\TestCase;
use Livewire\Livewire;

class TestTakeTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_fetch_the_test_take_1()
    {

        $testTake = TestTake::whereUuid('25b9e935-1080-476b-a825-fb9b4f828fb6')->first();
        $data = \tcCore\Http\Livewire\Student\TestTake::getData($testTake);
        $this->assertCount(6, $data);
        $this->assertEquals(collect([]), $data);


//        dd($data->filter(function($item, $key){
//            return ($key==1 || $key == 0);
//
//        }));
    }
}
