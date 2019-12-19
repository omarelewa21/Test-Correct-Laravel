<?php
namespace Tests\Unit;

use tcCore\EduIxRegistration;
use tcCore\Http\Helpers\EduIxService;
use Tests\TestCase;

class EduIxRegistrationTest extends TestCase {

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function when created_it_should_be_open()
{
     $service =  new EduIxService('1tcts328-i7og-ihri-d7ch-o62jhk0oha2f', 'c18f06a6cc7685149e78ac305094ebef');

        $this->instance = EduIxRegistration::create([

        ]);

}


}
