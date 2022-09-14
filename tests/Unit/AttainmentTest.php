<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

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
    /** @test */
    public function should_have_a_name_field_base_on_order_and_type()
    {


        $this->assertEquals('1.0', Attainment::find(407)->name);
        $this->assertEquals('2.0', Attainment::find(408)->name);
    }
}
