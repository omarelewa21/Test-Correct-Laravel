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

    /** @test */
    public function demo_class_after_create_has_do_not_overwrite_from_interface_set_to_true(){
        $schoolClass = factory(SchoolClass::class, 1)->make(['demo' => true,
            'do_not_overwrite_from_interface' => false])->first();
        $schoolClass->save();
        $this->assertEquals(true, $schoolClass->do_not_overwrite_from_interface);
    }

    /** @test */
    public function not_demo_class_after_create_has_do_not_overwrite_from_interface_set_to_false(){
        $schoolClass = factory(SchoolClass::class, 1)->make(['demo' => false,
            'do_not_overwrite_from_interface' => false])->first();
        $schoolClass->save();
        $this->assertEquals(false, $schoolClass->do_not_overwrite_from_interface);
    }

    /** @test */
    public function demo_class_after_create_has_do_not_overwrite_from_interface_set_to_true_after_update(){
        $schoolClass = factory(SchoolClass::class, 1)->make(['demo' => true,
            'do_not_overwrite_from_interface' => false])->first();
        $schoolClass->save();
        $schoolClass->do_not_overwrite_from_interface = false;
        $schoolClass->save();
        $schoolClassToTest = SchoolClass::find($schoolClass->id)->first();
        $this->assertEquals(true, $schoolClassToTest->do_not_overwrite_from_interface);
    }

}
