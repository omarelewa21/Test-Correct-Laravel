<?php
namespace Tests\Unit\Http\Controllers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\SchoolClass;
use Tests\TestCase;

class SchoolClassControllerTest extends TestCase
{
    

    /** @test */
    public function filter_current_returns_current_classes()
    {

        $response = $this->get(static::authTeacherOneGetRequest(        'school_class', 
                                                                        [   'mode'=> 'list',
                                                                            'filter' => [   'current'=> '1' ]
                                                                        ]
                                                                )
                            )->assertStatus(200);
        $this->assertEquals(2,count($response->original));
    }

    /** @test */
    public function filter_current_false_returns_all_classes()
    {

        $response = $this->get(static::authTeacherOneGetRequest(        'school_class', 
                                                                        [   'mode'=> 'list',
                                                                            'filter' => [   'current'=> '0' ]
                                                                        ]
                                                                )
                            )->assertStatus(200);
        $this->assertEquals(3,count($response->original));
    }

}
