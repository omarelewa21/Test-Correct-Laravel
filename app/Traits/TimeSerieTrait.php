<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 26/03/16
 * Time: 21:12
 */

/**
 * This trait adds two columns and three attributes to your model;
 */

namespace tcCore\Traits;


use Illuminate\Support\Facades\DB;
use tcCore\Lib\Models\BaseModel;
use tcCore\Scopes\ArchivedScope;
use Illuminate\Support\Facades\Auth;
use tcCore\ArchivedModel;
use tcCore\User;

trait TimeSerieTrait
{
    public function scopeJoinWithTimeSeries($query, $startDate, $endDate, $joinField)
    {
        $qb = DB::query()->select(
            "
             
                    select gen_date from 
                        (select adddate('1970-01-01',t4*10000 + t3*1000 + t2*100 + t1*10 + t0) gen_date from
                        (select 0 t0 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
                        (select 0 t1 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
                        (select 0 t2 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
                        (select 0 t3 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3,
                        (select 0 t4 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t4) v
                        
                "
        )->whereBetween('gen_date between',['2015-10-28', '2015-10-30']);

        $query->leftJoin($qb,'gen_date', '=', $joinField);

        return $query;
    }
}
