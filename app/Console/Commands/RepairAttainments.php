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
        $this->handle302_306();
        $this->handleWiskundeC();
        $this->handleAttainmentsEmptyStatus();
    }


    protected function setModifyArr()
    {
        $this->modifyArr = [
            'wiskunde-4-K' => ['base_subject_id'=>26,'education_level_id'=>4,'code'=>'K','description'=>'Kennis'],
            'wiskunde-4-V' => ['base_subject_id'=>26,'education_level_id'=>4,'code'=>'V','description'=>'Vaardigheden'],
            'wiskunde-5-K' => ['base_subject_id'=>26,'education_level_id'=>5,'code'=>'K','description'=>'Kennis'],
            'wiskunde-5-V' => ['base_subject_id'=>26,'education_level_id'=>5,'code'=>'V','description'=>'Vaardigheden'],
            'wiskunde-6-K' => ['base_subject_id'=>26,'education_level_id'=>6,'code'=>'K','description'=>'Kennis'],
            'wiskunde-7-K' => ['base_subject_id'=>26,'education_level_id'=>7,'code'=>'K','description'=>'Kennis'],
            'Biologie-4-K' => ['base_subject_id'=>11,'education_level_id'=>4,'code'=>'K','description'=>'Kennis'],
            'Biologie-4-V' => ['base_subject_id'=>11,'education_level_id'=>4,'code'=>'V','description'=>'Vaardigheden'],
            'Biologie-5-K' => ['base_subject_id'=>11,'education_level_id'=>5,'code'=>'K','description'=>'Kennis'],
            'Biologie-5-V' => ['base_subject_id'=>11,'education_level_id'=>5,'code'=>'V','description'=>'Vaardigheden'],
            'Biologie-6-K' => ['base_subject_id'=>11,'education_level_id'=>6,'code'=>'K','description'=>'Kennis'],
            'Biologie-7-K' => ['base_subject_id'=>11,'education_level_id'=>7,'code'=>'K','description'=>'Kennis'],
            'Maatschappijleer-7-K' => ['base_subject_id'=>19,'education_level_id'=>7,'code'=>'ML1/K','description'=>'Kennis'],
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

    protected function  handle392()
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

    protected function handle302_306()
    {
        $this->handle302();
        $this->handle303();
        $this->handle304AndNewline();
        $this->handle305();
        $this->handle306();
    }

    protected function handle302()
    {
        $props = [
                            'base_subject_id'=>1,
                            'education_level_id'=>4,
                            'code'=>'NE/K',
                            'description'=>'Schrijfvaardigheid',
                            'subcode'=>7,
                            'subsubcode'=>null,
                            'status'=>'ACTIVE'
                        ];
        $attainment = $this->getAttainment($props);
        if($attainment->getKey()==302){
            $this->info('1/4/NE/K/7 -- 302 already handled');
            return;
        }
        $attainment = Attainment::find(302);
        $attainment->fill($props);
        $attainment->save();
        $this->info('handled: 302');
        $newLineProps = [
            'base_subject_id'=>1,
            'education_level_id'=>4,
            'code'=>'NE/K',
            'description'=>'Leesvaardigheid',
            'subcode'=>6,
            'subsubcode'=>null,
            'status'=>'ACTIVE',
            'attainment_id'=>$attainment->attainment_id,
        ];
        $attainment = $this->getAttainment($newLineProps);
        if(!is_null($attainment)){
            $this->info('1/4/NE/K/6 -- already handled');
            return;
        }
        $attainment = new Attainment();
        $attainment->fill($newLineProps);
        $attainment->save();
    }

    protected function handle303()
    {
        $props = [
            'base_subject_id'=>1,
            'education_level_id'=>4,
            'code'=>'NE/K',
            'description'=>'Fictie',
            'subcode'=>8,
            'subsubcode'=>null,
            'status'=>'ACTIVE'
        ];
        $attainment = $this->getAttainment($props);
        if($attainment->getKey()==303){
            $this->info('1/4/NE/K/8 -- 303 already handled');
            return;
        }
        $attainment = Attainment::find(303);
        $attainment->fill($props);
        $attainment->save();
        $this->info('handled: 303');
    }

    protected function handle304AndNewline()
    {
        $props = [
            'base_subject_id'=>1,
            'education_level_id'=>4,
            'code'=>'NE/V',
            'description'=>'Vaardigheden',
            'subcode'=>null,
            'subsubcode'=>null,
            'status'=>'ACTIVE'
        ];
        $newLineProps = [
            'base_subject_id'=>1,
            'education_level_id'=>4,
            'code'=>'NE/V',
            'description'=>'Verwerven, verwerken en verstrekken van informatie',
            'subcode'=>1,
            'subsubcode'=>null,
            'status'=>'ACTIVE',
            'attainment_id'=>304
        ];
        $attainment = $this->getAttainment($props);
        if($attainment->getKey()==304){
            $this->info('1/4/NE/V -- 304 already handled');
            return;
        }
        $attainment->fill($newLineProps);
        $attainment->save();
        $this->info('handled: new line');
        $attainment = Attainment::find(304);
        $attainment->fill($props);
        $attainment->save();
        $this->info('handled: 304');
    }

    protected function handle305()
    {
        $props = [
            'base_subject_id'=>1,
            'education_level_id'=>4,
            'code'=>'NE/V',
            'description'=>'Schrijven op basis van documentatie',
            'subcode'=>2,
            'subsubcode'=>null,
            'status'=>'ACTIVE',
            'attainment_id'=>304
        ];
        $attainment = $this->getAttainment($props);
        if($attainment->getKey()==305){
            $this->info('1/4/NE/V/2 -- 305 already handled');
            return;
        }
        $attainment = Attainment::find(305);
        $attainment->fill($props);
        $attainment->save();
        $this->info('handled: 305');
    }

    protected function handle306()
    {
        $props = [
            'base_subject_id'=>1,
            'education_level_id'=>4,
            'code'=>'NE/V',
            'description'=>'Vaardigheden in samenhang',
            'subcode'=>3,
            'subsubcode'=>null,
            'status'=>'ACTIVE',
            'attainment_id'=>304
        ];
        $attainment = $this->getAttainment($props);
        if($attainment->getKey()==306){
            $this->info('1/4/NE/V/3 -- 306 already handled');
            return;
        }$attainment->delete();
        $attainment = Attainment::find(306);
        $attainment->fill($props);
        $attainment->save();
        $this->info('handled: 306');
    }

    protected function handleWiskundeC()
    {
        $this->handleWiskundeC1();
        $this->handleWiskundeC2();
    }

    protected function handleWiskundeC1()
    {
        $attainment = $this->getAttainment([
            'base_subject_id'=>7,
            'education_level_id'=>1,
            'code'=>'F',
            'subcode'=>1,
            'subsubcode'=>null
        ]);
        if(is_null($attainment)){
            throw new \Exception('7/1/F/1 does not exist');
        }
        $attainment->description = null;
        $attainment->save();
        $this->info('handled: 7/1/F/1 wiskunde C');
    }

    protected function handleWiskundeC2()
    {
        $attainment = $this->getAttainment([
            'base_subject_id'=>7,
            'education_level_id'=>1,
            'code'=>'G',
            'subcode'=>1,
            'subsubcode'=>null
        ]);
        if(is_null($attainment)){
            throw new \Exception('7/1/G/1 does not exist');
        }
        $attainment->description = null;
        $attainment->save();
        $this->info('handled: 7/1/G/1 wiskunde C');
    }

    protected function handleAttainmentsEmptyStatus()
    {
        /**--- 68/1/G/1 -----*/
        $parentProps = ['base_subject_id'=>68,
            'education_level_id'=>1,
            'code'=>'G',
            'subcode'=>null,
            'subsubcode'=>null];
        $parentAttainment = $this->getAttainment($parentProps);
        if(is_null($parentAttainment)){
            $this->error('parent 68/1/G not found');
            return;
        }
        $props = [
            'base_subject_id'=>68,
            'education_level_id'=>1,
            'code'=>'G',
            'subcode'=>1,
            'subsubcode'=>null,
            'description'=>'Basisverslaglegging',
            'attainment_id'=>$parentAttainment->getKey()
        ];
        $attainment = new Attainment();
        $attainment->fill($props);
        $attainment->save();
        $this->info('68/1/G/1 handled');
        /**---  68/1/G/3 -----*/
        $parentProps = ['base_subject_id'=>68,
            'education_level_id'=>1,
            'code'=>'G',
            'subcode'=>null,
            'subsubcode'=>null];
        $parentAttainment = $this->getAttainment($parentProps);
        if(is_null($parentAttainment)){
            $this->error('parent 68/1/G not found');
            return;
        }
        $props = [
            'base_subject_id'=>68,
            'education_level_id'=>3,
            'code'=>'G',
            'subcode'=>1,
            'subsubcode'=>null,
            'description'=>'Basisverslaglegging',
            'attainment_id'=>$parentAttainment->getKey()
        ];
        $attainment = new Attainment();
        $attainment->fill($props);
        $attainment->save();
        $this->info('68/1/G/3 handled');
        /**---  70/1/A/1 -----*/
        $parentProps = ['base_subject_id'=>70,
            'education_level_id'=>1,
            'code'=>'A',
            'subcode'=>null,
            'subsubcode'=>null];
        $parentAttainment = $this->getAttainment($parentProps);
        if(is_null($parentAttainment)){
            $this->error('parent 68/1/G not found');
            return;
        }
        $props = [
            'base_subject_id'=>70,
            'education_level_id'=>1,
            'code'=>'A',
            'subcode'=>1,
            'subsubcode'=>null,
            'description'=>'Basisverslaglegging',
            'attainment_id'=>$parentAttainment->getKey()
        ];
        $attainment = new Attainment();
        $attainment->fill($props);
        $attainment->save();
        $this->info('70/1/A/1 handled');
        /**---  70/3/A/1 -----*/
        $parentProps = ['base_subject_id'=>70,
            'education_level_id'=>3,
            'code'=>'A',
            'subcode'=>null,
            'subsubcode'=>null];
        $parentAttainment = $this->getAttainment($parentProps);
        if(is_null($parentAttainment)){
            $this->error('parent 70/3/A not found');
            return;
        }
        $props = [
            'base_subject_id'=>70,
            'education_level_id'=>3,
            'code'=>'A',
            'subcode'=>1,
            'subsubcode'=>null,
            'description'=>'Basisverslaglegging',
            'attainment_id'=>$parentAttainment->getKey()
        ];
        $attainment = new Attainment();
        $attainment->fill($props);
        $attainment->save();
        $this->info('70/3/A/1 handled');
        /**---  76/3/A/1 -----*/
        $parentProps = ['base_subject_id'=>76,
            'education_level_id'=>3,
            'code'=>'A',
            'subcode'=>null,
            'subsubcode'=>null];
        $parentAttainment = $this->getAttainment($parentProps);
        if(is_null($parentAttainment)){
            $this->error('parent 76/3/A not found');
            return;
        }
        $props = [
            'base_subject_id'=>76,
            'education_level_id'=>3,
            'code'=>'A',
            'subcode'=>1,
            'subsubcode'=>null,
            'description'=>null,
            'attainment_id'=>$parentAttainment->getKey()
        ];
        $attainment = new Attainment();
        $attainment->fill($props);
        $attainment->save();
        $this->info('76/3/A/1 handled');
        /**---  87/6/B/1/8 -----*/
        $parentProps = ['base_subject_id'=>87,
            'education_level_id'=>6,
            'code'=>'B',
            'subcode'=>1,
            'subsubcode'=>null];
        $parentAttainment = $this->getAttainment($parentProps);
        if(is_null($parentAttainment)){
            $this->error('parent 87/6/B/1 not found');
            return;
        }
        $props = [
            'base_subject_id'=>87,
            'education_level_id'=>6,
            'code'=>'B',
            'subcode'=>1,
            'subsubcode'=>8,
            'description'=>'Een kandidaat kan ICT-vaardigheden toepassen, met name: tekstverwerkingsprogramma en presentatieprogramma.',
            'attainment_id'=>$parentAttainment->getKey()
        ];
        $attainment = new Attainment();
        $attainment->fill($props);
        $attainment->save();
        $this->info('87/6/B/1/8 handled');
        /**---  88/6/A -----*/
        $props = [
            'base_subject_id'=>88,
            'education_level_id'=>6,
            'code'=>'A',
            'subcode'=>null,
            'subsubcode'=>null
        ];
        $attainment = new Attainment();
        $attainment->fill($props);
        $attainment->save();
        DB::table('attainments')
            ->where('base_subject_id', $props['base_subject_id'])
            ->where('education_level_id', $props['education_level_id'])
            ->where('code', $props['code'])
            ->whereNotNull('subcode')
            ->whereNull('subsubcode')
            ->update(['attainment_id' => $attainment->getKey()]);
        $this->info('88/6/A handled');
    }

    protected function attainmentExist($props)
    {
        $attainment = $this->getAttainment($props);
        if(is_null($attainment)){
            return false;
        }
        return true;
    }

    protected function getAttainment($props)
    {
        $attainment = Attainment::where('base_subject_id', $props['base_subject_id'])
            ->where('education_level_id', $props['education_level_id'])
            ->where('code', $props['code'])
            ->where('subcode', $props['subcode'])
            ->where('subsubcode', $props['subsubcode'])->first();
        return $attainment;
    }
}
