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
use tcCore\Http\Requests\Request;
use tcCore\Test;
use tcCore\TestKind;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\User;
use Tests\TestCase;

class RequestFilterTest extends TestCase
{

    /** @test */
    public function a_string_with_script_tag_is_filtered()
    {
        $list = (object) [
            'toTest' => "a<script>alert('hallo');</script>",
            'expected' => "a",
        ];

        Request::filter($list->toTest);

        $this->assertEquals($list->expected, $list->toTest);
    }

    /** @test */
    public function an_array_with_script_tag_is_filtered()
    {
        $list = (object) [
            'toTest' => ['b' => "a<script>alert('hallo');</script>"],
            'expected' => ['b' => "a"],
        ];

        Request::filter($list->toTest);

        $this->assertEquals($list->expected, $list->toTest);
    }

    /** @test */
    public function a_nested_array_with_script_tag_is_filtered()
    {
        $list = (object) [
            'toTest' => ['b' => ['c' => "a<script>alert('hallo');</script>"]],
            'expected' => ['b' => ['c' => "a"]],
        ];

        Request::filter($list->toTest);

        $this->assertEquals($list->expected, $list->toTest);
    }

    /** @test */
    public function an_unnamed_nested_array_with_script_tag_is_filtered()
    {
        $list = (object) [
            'toTest' => ['b' => ["a<script>alert('hallo');</script>"]],
            'expected' => ['b' => ["a"]],
        ];

        Request::filter($list->toTest);

        $this->assertEquals($list->expected, $list->toTest);
    }
}
