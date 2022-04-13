<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use tcCore\Attainment;

class CheckRepairAttainments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:check_attainments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $attainments = Attainment::all();
        $faultArr = [];
        foreach ($attainments as $attainment){
            $parent = $attainment->attainment;
            if(is_null($parent)){
                continue;
            }
            if($attainment->education_level_id!=$parent->education_level_id||$attainment->base_subject_id!=$parent->base_subject_id){
                $faultArr[] = [ 'id'=>$attainment->getKey(),
                    'base_subject_id'=>$attainment->base_subject_id,
                    'attainment'=>$attainment->attainment_id,
                    'education_level_id'=>$attainment->education_level_id,
                    'code'=>$attainment->code,
                    'subcode'=>$attainment->subcode,
                    'subsubcode'=>$attainment->subsubcode];
            }

        }
        $this->table([  "id",
                        "base_subject_id",
                        "attainment",
                        "education_level_id",
                        "code",
                        "subcode",
                        "subsubcode"], $faultArr);
        $this->info(count($faultArr));
    }
}
