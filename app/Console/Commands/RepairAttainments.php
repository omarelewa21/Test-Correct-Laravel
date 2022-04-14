<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use tcCore\Attainment;

class RepairAttainments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:attainments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '13-04-2022 repair faulty import of attainments';


    protected $bar = null;
    protected $modifyArr = [];
    protected $checkArr = [];
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setModifyArr();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
//        $this->bar = $this->output->createProgressBar(count($this->modifyArr)+9);
//        $this->bar->start();
        $this->handleMissingToplevelAttainments();
        $this->handleEconomie();
        $this->handle499();
        $this->handle799();
        $this->handle665();
        $this->handle748();
        $this->handle392();
        $this->handle393();
        $this->handle307();
        $this->handle1242();
        $this->handle182();
        $this->handle223();
//        $this->bar->finish();
    }


    protected function setModifyArr()
    {
        $this->modifyArr = [
            'wiskunde-4-K' => ['base_subject_id'=>26,'education_level_id'=>4,'code'=>'K','description'=>'toplevel eindterm'],
            'wiskunde-4-V' => ['base_subject_id'=>26,'education_level_id'=>4,'code'=>'V','description'=>'toplevel eindterm'],
            'wiskunde-5-K' => ['base_subject_id'=>26,'education_level_id'=>5,'code'=>'K','description'=>'toplevel eindterm'],
            'wiskunde-5-V' => ['base_subject_id'=>26,'education_level_id'=>5,'code'=>'V','description'=>'toplevel eindterm'],
            'wiskunde-6-K' => ['base_subject_id'=>26,'education_level_id'=>6,'code'=>'K','description'=>'toplevel eindterm'],
            'wiskunde-7-K' => ['base_subject_id'=>26,'education_level_id'=>7,'code'=>'K','description'=>'toplevel eindterm'],
            'Biologie-4-K' => ['base_subject_id'=>11,'education_level_id'=>4,'code'=>'K','description'=>'toplevel eindterm'],
            'Biologie-4-V' => ['base_subject_id'=>11,'education_level_id'=>4,'code'=>'V','description'=>'toplevel eindterm'],
            'Biologie-5-K' => ['base_subject_id'=>11,'education_level_id'=>5,'code'=>'K','description'=>'toplevel eindterm'],
            'Biologie-5-V' => ['base_subject_id'=>11,'education_level_id'=>5,'code'=>'V','description'=>'toplevel eindterm'],
            'Biologie-6-K' => ['base_subject_id'=>11,'education_level_id'=>6,'code'=>'K','description'=>'toplevel eindterm'],
            'Biologie-7-K' => ['base_subject_id'=>11,'education_level_id'=>7,'code'=>'K','description'=>'toplevel eindterm'],
            'Maatschappijleer-7-K' => ['base_subject_id'=>19,'education_level_id'=>7,'code'=>'ML1/K','description'=>'toplevel eindterm'],
        ];
    }

    protected function inCheckArr($check)
    {
        $validateArr = [    'base_subject_id'=>$check['base_subject_id'],
                            'education_level_id'=>$check['education_level_id'],
                            'code'=>$check['code']];
        foreach ($this->checkArr as $key => $value){
            if($value===$validateArr){
                return true;
            }
        }
        return false;
    }

    protected function handleMissingToplevelAttainments()
    {
        $attainments = Attainment::all();
        $faultArr = [];
        foreach ($attainments as $attainment){
            $parent = $attainment->attainment;
            if(is_null($parent)){
                continue;
            }
            if($attainment->education_level_id!=$parent->education_level_id){
                $faultArr[] = [ 'id'=>$attainment->getKey(),
                    'base_subject_id'=>$attainment->base_subject_id,
                    'attainment'=>$attainment->attainment_id,
                    'education_level_id'=>$attainment->education_level_id,
                    'code'=>$attainment->code,
                    'subcode'=>$attainment->subcode,
                    'subsubcode'=>$attainment->subsubcode];
                $this->checkArr[] = [
                    'base_subject_id'=>$attainment->base_subject_id,
                    'education_level_id'=>$attainment->education_level_id,
                    'code'=>$attainment->code,
                ];
            }
        }
        if(count($faultArr)===0){
            $this->info('no faulty attainments to repair');
        }

        $this->checkArr = array_map("unserialize", array_unique(array_map("serialize", $this->checkArr)));
        foreach ($this->modifyArr as $key => $props){
            if(!$this->inCheckArr($props)){
                $this->info('not in checkArr:'.$key);
                //$this->bar->advance();
                continue;
            }
            $attainment = new Attainment();
            $props = array_merge($props,['subcode'=>null,
                'subsubcode'=>null,
                'status'=>'ACTIVE']);
            $attainment->fill($props);
            $attainment->save();
            DB::table('attainments')
                ->where('base_subject_id', $props['base_subject_id'])
                ->where('education_level_id', $props['education_level_id'])
                ->where('code', $props['code'])
                ->whereNotNull('subcode')
                ->whereNull('subsubcode')
                ->update(['attainment_id' => $attainment->getKey()]);
            $this->info('handled:'.$key);
            //$this->bar->advance();
        }
    }

    protected function handleEconomie()
    {
        $attainment = Attainment::where('base_subject_id', 17)
            ->where('education_level_id', 6)
            ->where('code', 'EC/K/1')
            ->whereNull('subcode')
            ->whereNull('subsubcode')->first();
        if(is_null($attainment)){
            $this->error('parent attainment 17/6/EC/K/1 not found');
            exit();
        }
        DB::table('attainments')
            ->where('base_subject_id', 17)
            ->where('education_level_id', 6)
            ->where('code', 'EC/K/1')
            ->whereNotNull('subcode')
            ->whereNull('subsubcode')
            ->update(['attainment_id' => $attainment->getKey()]);
        $this->info('handled:17/6/EC/K/1 ');
        //$this->bar->advance();
        $attainment = Attainment::where('base_subject_id', 17)
            ->where('education_level_id', 6)
            ->where('code', 'EC/K/2')
            ->whereNull('subcode')
            ->whereNull('subsubcode')->first();
        if(is_null($attainment)){
            $this->error('parent attainment 17/6/EC/K/2 not found');
            exit();
        }
        DB::table('attainments')
            ->where('base_subject_id', 17)
            ->where('education_level_id', 6)
            ->where('code', 'EC/K/2')
            ->whereNotNull('subcode')
            ->whereNull('subsubcode')
            ->update(['attainment_id' => $attainment->getKey()]);
        $this->info('handled:17/6/EC/K/2 ');
        //$this->bar->advance();
    }

    protected function handle499()
    {
        $attainment = Attainment::find(499);
        $props = [  'base_subject_id'=>9,
                    'education_level_id'=>1,
                    'code'=>'A',
                    'description'=>'Natuurwetenschappelijk instrumentarium',
                    'subcode'=>8,
                    'subsubcode'=>null,
                    'status'=>'ACTIVE',
                    'attainment_id'=>52
                ];
        $attainment->fill($props);
        $attainment->save();
        $this->info('handled:9/1/A/8 -- 499 update ');
        //$this->bar->advance();
    }

    protected function handle799()
    {
        $attainment = Attainment::withTrashed()->find(799);
        $attainment->restore();
        $this->info('handled:9/1/A/8 -- 799 restore ');
        //$this->bar->advance();
    }

    protected function handle665()
    {
        //665 zit er twee keer in 1 niveau vwo 1 havo. Havo in db
        $parentAttainment = Attainment::where('base_subject_id', 14)
            ->where('education_level_id', 1)
            ->where('code', 'A')
            ->where('subcode',3)
            ->whereNull('subsubcode')->first();
        if(is_null($parentAttainment)){
            $this->error('parent attainment 14/1/A/3 not found');
            exit();
        }
        if($this->attainmentExist([     'base_subject_id'=>14,
                                        'education_level_id'=>1,
                                        'code'=>'A',
                                        'subcode'=>3,
                                        'subsubcode'=>6])){
            $this->info('14/1/A/3/6 -- 665 already handled');
            return;
        }

        $attainment = new Attainment();
        $props = [  'base_subject_id'=>14,
                    'education_level_id'=>1,
                    'code'=>'A',
                    'description'=>'Werken in contexten',
                    'subcode'=>3,
                    'subsubcode'=>6,
                    'status'=>'ACTIVE',
                    'attainment_id'=>$parentAttainment->getKey()
        ];
        $attainment->fill($props);
        $attainment->save();
        $this->info('handled:14/1/A/3/6 -- 665 ');
    }

    protected function handle748()
    {
        //748 zit er twee keer in. De eerste keer is wrsch vergissing. Die attainment is in de import eruit gevallen en moet opnieuw worden toegevoegd
        if($this->attainmentExist([     'base_subject_id'=>19,
            'education_level_id'=>1,
            'code'=>'A',
            'subcode'=>1,
            'subsubcode'=>2])){
            $this->info('19/1/A/1/2 -- 748 already handled');
            return;
        }
        $attainment = new Attainment();
        $props = [  'base_subject_id'=>19,
                    'education_level_id'=>1,
                    'code'=>'A',
                    'description'=>'De kandidaat kan (verworven) informatie verwerken o.a. met behulp van ICT en daaruit beredeneerde conclusies trekken.',
                    'subcode'=>1,
                    'subsubcode'=>2,
                    'status'=>'ACTIVE',
                    'attainment_id'=>747
        ];
        $attainment->fill($props);
        $attainment->save();
        $this->info('handled:19/1/A/1/2 -- 748 ');
    }

    protected function handle393()
    {
        //392 stond er een tweede keer in ipv 393. Dus 393 restoren en child updaten
        $parentAttainment = Attainment::where('base_subject_id', 28)
            ->where('education_level_id', 4)
            ->where('code', 'K')
            ->whereNull('subcode')
            ->whereNull('subsubcode')->first();
        if(is_null($parentAttainment)){
            $this->error('parent attainment 28/4/K not found');
            exit();
        }
        $attainment = Attainment::withTrashed()->find(393);
        $attainment->restore();
        $props = [
            'code'=>'K',
            'subcode'=>2,
            'attainment_id'=>$parentAttainment->getKey()
        ];
        $attainment->fill($props);
        $attainment->save();
        $this->info('handled:28/4/K/2 -- 393 restore ');
        $childAttainment = Attainment::find(1297);
        $childAttainment->attainment_id = 393;
        $childAttainment->save();
        //$this->bar->advance();
    }

    protected function handle392()
    {
        //392 is op de plek van 393 terecht gekomen
        $attainment = Attainment::find(392);
        $props = [
            'description'=>'Oriëntatie op leren en werken',
            'subcode'=>1,
        ];
        $attainment->fill($props);
        $attainment->save();
        $this->info('handled:28/4/K/1 -- 392 update ');
    }

    protected function handle307()
    {
        //307 staat zowel in Fries als in Nederlands, Nl in db
        $parentAttainment = Attainment::where('base_subject_id', 2)
            ->where('education_level_id', 4)
            ->where('code', 'FR/K')
            ->whereNull('subcode')
            ->whereNull('subsubcode')->first();
        if(is_null($parentAttainment)){
            $this->error('parent attainment 2/4/K not found');
            exit();
        }
        if($this->attainmentExist([     'base_subject_id'=>2,
            'education_level_id'=>4,
            'code'=>'FR/K',
            'subcode'=>1,
            'subsubcode'=>null])){
            $this->info('2/4/FR/K/1 -- 307 already handled');
            return;
        }

        $attainment = new Attainment();
        $props = [  'base_subject_id'=>2,
            'education_level_id'=>4,
            'code'=>'FR/K',
            'description'=>'Oriëntatie op leren en werken',
            'subcode'=>1,
            'subsubcode'=>null,
            'status'=>'ACTIVE',
            'attainment_id'=>$parentAttainment->getKey()
        ];
        $attainment->fill($props);
        $attainment->save();
        $childAttainment = Attainment::find(1197);
        $childAttainment->attainment_id = $attainment->getKey();
        $childAttainment->save();
        $this->info('handled:2/4/FR/K/1 -- 307 create ');
    }

    protected function handle1242()
    {
        //1242 staat zowel in Duits als in Spaans, Spaans in db, geen childeren
        if($this->attainmentExist([     'base_subject_id'=>24,
            'education_level_id'=>4,
            'code'=>'MVT/V',
            'subcode'=>3,
            'subsubcode'=>1])){
            $this->info('24/4/MVT/V/3/2 -- 1242 already handled');
            return;
        }

        $attainment = new Attainment();
        $props = [  'base_subject_id'=>24,
                    'education_level_id'=>4,
                    'code'=>'MVT/V',
                    'description'=>'De kandidaat kan kennis van land en samenleving rond bepaalde onderwerpen toepassen bij het herkennen en interpreteren van cultuuruitingen die specifiek zijn voor het taalgebied of daarmee in directe relatie staan.',
                    'subcode'=>3,
                    'subsubcode'=>1,
                    'status'=>'ACTIVE',
                    'attainment_id'=>349
        ];
        $attainment->fill($props);
        $attainment->save();
        $this->info('handled:24/4/MVT/V/3/2 -- 1242 create ');
    }

    protected function handle182()
    {
        // twee eindtermen hebben bij Engels een attainment_id van 182 (Duits). Dit moet zijn 158
        DB::table('attainments')
            ->where('base_subject_id', 22)
            ->where('attainment_id', 182)
            ->update(['attainment_id' => 158]);
        $this->info('handled: 182');
    }

    protected function handle223()
    {
        // drie eindtermen hebben bij Natuurkunde een attainment_id van 223 (Scheikunde). Dit moet zijn 222
        DB::table('attainments')
            ->where('base_subject_id', 9)
            ->where('attainment_id', 223)
            ->update(['attainment_id' => 222]);
        $this->info('handled: 223');
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
