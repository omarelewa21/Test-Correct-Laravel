<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use tcCore\ArchivedModel;
use tcCore\Attainment;
use tcCore\EckidUser;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Teacher;
use tcCore\TestTake;
use tcCore\UmbrellaOrganization;
use tcCore\User;
use Tests\TestCase;

class AttainmentTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function name_should_return_attainment_translation_with_order_number()
    {
        app()->setLocale('nl');
        $this->assertEquals('Eindterm 1', Attainment::find(407)->name);
        app()->setLocale('en');
        $this->assertEquals('Attainment 2', Attainment::find(408)->name);
    }

    /** @test */
    public function when_learning_goal_then_name_should_return_with_order_number()
    {
        app()->setLocale('nl');
        $attainment = Attainment::find(407);
        $attainment->is_learning_goal = 1;
        $attainment->save();
        $this->assertEquals('Leerdoel 1', $attainment->fresh()->name);
        app()->setLocale('en');
        $this->assertEquals('Learning goal 1', $attainment->name);
    }
}
