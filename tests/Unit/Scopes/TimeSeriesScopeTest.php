<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit\Scopes;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\PValue;
use Tests\TestCase;
use Tests\Unit\TimeSeries;
use function dd;
use function now;

class TimeSeriesScopeTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_create_a_time_series_per_day()
    {
        $this->markTestSkipped('This test is not working yet');

        $qb = PValue::take(10)->joinWithTimeSeries(
            Carbon::parse('2015-10-28'),
            Carbon::parse('2015-10-28')->addDays(3),
            'p_values.created_at'
        );//->where('id', 1);
        dd($qb->get());


        $this->assertCount(30, $result);
    }


}
