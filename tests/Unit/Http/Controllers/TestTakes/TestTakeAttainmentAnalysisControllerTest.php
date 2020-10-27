<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit\Http\Controllers\TestTakes;

use tcCore\User;
use Tests\TestCase;

class TestTakeAttainmentAnalysisControllerTest extends TestCase
{

//    use \Illuminate\Foundation\Testing\DatabaseTransactions;


    /** @test */
    public function get_complete_attainment_analysis()
    {
        $url = (str_replace('+','%2B',static::authUserGetRequest(
            route('test_take_attainment_analysis.index','06b92bfa-cf9d-4df6-9b30-9fa6e69c3e6c'),
            [],
            User::whereUsername('carloschoep+k999docent14@hotmail.com')->first()
        )));
        $response = $this->get(
            $url
        );
        dd($response);

    }

    /** @test */
    public function get_complete_attainment_analysis_for_attainment()
    {
        $url = (str_replace('+','%2B',static::authUserGetRequest(
            route('test_take_attainment_analysis.show',['06b92bfa-cf9d-4df6-9b30-9fa6e69c3e6c','7207942b-fcdf-11ea-92d9-5616569c777a']),
            [],
            User::whereUsername('carloschoep+k999docent14@hotmail.com')->first()
        )));
        $response = $this->get(
            $url
        );
        dd($response);

    }

    /** @test */
    public function prutstest()
    {
        $response = $this->get(
            static::authTeacherOneGetRequest(
                'group_question_question/5/3',
                []
            )
        );
        dd($response);
    }


}