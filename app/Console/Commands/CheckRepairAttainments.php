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
        $this->info('checking wrong taxonomy:');
        $this->table([  "id",
                        "base_subject_id",
                        "attainment",
                        "education_level_id",
                        "code",
                        "subcode",
                        "subsubcode"], $faultArr);
        $this->info(count($faultArr));
        $this->checkEconomie();
        $this->check499();
        $this->check799();
        $this->check665();
        $this->check748();
        $this->check392();
        $this->check393();
        $this->check307();
        $this->check1242();
        $this->check182();
        $this->check223();
        $this->check302_306();
    }

    protected function checkEconomie()
    {
        $this->info('checking economie');
        $this->checkEconomieById(2003,2002);
        $this->checkEconomieById(2005,2004);
    }

    protected function checkEconomieById($id,$expected)
    {
        $attainment = Attainment::find($id);
        $check = $attainment->attainment_id==$expected?true:false;
        if($check){
            $this->info($id.' has '.$expected.' as attainment_id');
        }else{
            $this->error($id.' has '.$attainment->attainment_id.' as attainment_id');
        }
    }

    protected function check499()
    {
        $attainment = Attainment::find(499);
        $check = $attainment->base_subject_id==9?true:false;
        if($check){
            $this->info('499 has 9 as base_subject_id');
        }else{
            $this->error('499 has '.$attainment->base_subject_id.' as base_subject_id');
        }
    }

    protected function check799()
    {
        $attainment = Attainment::find(799);
        if($attainment){
            $this->info('799 not trashed');
        }else{
            $this->error('799 trashed');
        }
    }

    protected function check665()
    {
        $check = $this->attainmentExist([     'base_subject_id'=>14,
            'education_level_id'=>1,
            'code'=>'A',
            'subcode'=>3,
            'subsubcode'=>6]);
        if($check){
            $this->info('14/1/A/3/6 exists');
        }else{
            $this->error('14/1/A/3/6 does not exist');
        }
    }

    protected function check748()
    {
        $check = $this->attainmentExist([     'base_subject_id'=>19,
        'education_level_id'=>1,
        'code'=>'A',
        'subcode'=>1,
        'subsubcode'=>2]);
        if($check){
            $this->info('19/1/A/1/2 exists');
        }else{
            $this->error('19/1/A/1/2 does not exist');
        }
    }

    protected function check392()
    {
        $attainment = Attainment::find(392);
        $check = $attainment->subcode===1?true:false;
        if($check){
            $this->info('392 has 1 as subcode');
        }else{
            $this->error('392 has '.$attainment->subcode.' as subcode');
        }
    }

    protected function check393()
    {
        $attainment = Attainment::find(393);
        if($attainment){
            $this->info('393 not trashed');
        }else{
            $this->error('393 trashed');
        }
    }

    protected function check307()
    {
        $check = $this->attainmentExist([  'base_subject_id'=>2,
            'education_level_id'=>4,
            'code'=>'FR/K',
            'description'=>'Oriëntatie op leren en werken',
            'subcode'=>1,
            'subsubcode'=>null,
            'status'=>'ACTIVE'
        ]);
        if($check){
            $this->info('2/4/FR/K1 exists');
        }else{
            $this->error('2/4/FR/K1 does not exist');
        }
    }

    protected function check1242()
    {
        $check = $this->attainmentExist([  'base_subject_id'=>24,
            'education_level_id'=>4,
            'code'=>'MVT/V',
            'description'=>'De kandidaat kan kennis van land en samenleving rond bepaalde onderwerpen toepassen bij het herkennen en interpreteren van cultuuruitingen die specifiek zijn voor het taalgebied of daarmee in directe relatie staan.',
            'subcode'=>3,
            'subsubcode'=>1,
            'status'=>'ACTIVE',
            'attainment_id'=>349
        ]);
        if($check){
            $this->info('24/4/MVT/V/3/2 exists');
        }else{
            $this->error('24/4/MVT/V/3/2 does not exist');
        }
    }

    protected function check182()
    {
        $attainment = Attainment::find(818);
        if($attainment->attainment_id==158){
            $this->info('818 has 158 as attainment_id');
        }else{
            $this->error('818 has '.$attainment->attainment_id.' as attainment_id');
        }
        $attainment = Attainment::find(819);
        if($attainment->attainment_id==158){
            $this->info('819 has 158 as attainment_id');
        }else{
            $this->error('819 has '.$attainment->attainment_id.' as attainment_id');
        }
    }

    protected function check223()
    {
        $attainment = Attainment::find(932);
        if($attainment->attainment_id==222){
            $this->info('932 has 222 as attainment_id');
        }else{
            $this->error('932 has '.$attainment->attainment_id.' as attainment_id');
        }
        $attainment = Attainment::find(933);
        if($attainment->attainment_id==222){
            $this->info('933 has 222 as attainment_id');
        }else{
            $this->error('933 has '.$attainment->attainment_id.' as attainment_id');
        }
        $attainment = Attainment::find(934);
        if($attainment->attainment_id==222){
            $this->info('934 has 222 as attainment_id');
        }else{
            $this->error('934 has '.$attainment->attainment_id.' as attainment_id');
        }
    }

    protected function check302_306()
    {
        $this->check302();
        $this->check303();
        $this->check304AndNewline();
        $this->check305();
        $this->check306();
    }

    protected function check302()
    {
        $attainment = Attainment::find(302);
        if($attainment->description==''){
            $this->info('302 has correct description');
        }else{
            $this->error('302 has description:'.$attainment->description);
        }
    }
    protected function check303()
    {
        $attainment = Attainment::find(303);
        if($attainment->description==''){
            $this->info('303 has correct description');
        }else{
            $this->error('303 has description:'.$attainment->description);
        }
    }
    protected function check304AndNewline()
    {
        $attainment = Attainment::find(304);
        if($attainment->description==''){
            $this->info('304 has correct description');
        }else{
            $this->error('304 has description:'.$attainment->description);
        }
        $check = $this->attainmentExist([
            'base_subject_id'=>1,
            'education_level_id'=>4,
            'code'=>'NE/V',
            'description'=>'Verwerven, verwerken en verstrekken van informatie',
            'subcode'=>1,
            'subsubcode'=>null,
            'status'=>'ACTIVE',
            'attainment_id'=>304
        ]);
        if($check){
            $this->info('1/4/NE/V/1 exists');
        }else{
            $this->error('1/4/NE/V/1 does not exist');
        }
    }
    protected function check305()
    {
        $attainment = Attainment::find(305);
        if($attainment->description==''){
            $this->info('305 has correct description');
        }else{
            $this->error('305 has description:'.$attainment->description);
        }
    }
    protected function check306()
    {
        $attainment = Attainment::find(306);
        if($attainment->description==''){
            $this->info('306 has correct description');
        }else{
            $this->error('306 has description:'.$attainment->description);
        }
    }

    protected function attainmentExist($props)
    {
        $attainment = Attainment::where('base_subject_id', $props['base_subject_id'])
            ->where('education_level_id', $props['education_level_id'])
            ->where('code', $props['code'])
            ->where('subcode', $props['subcode'])
            ->where('subsubcode', $props['subsubcode'])->first();
        if(is_null($attainment)){
            return false;
        }
        return true;
    }
}
