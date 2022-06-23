<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use tcCore\Attainment;

class RepairAttainmentParents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:attainment_parents  {--no_check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'repairs missing parents after attainment import';

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
        $msg = 'Did you backup attainments table and question_attainments table?';
        if(!$this->option('no_check')&&!$this->confirm($msg)){
            exit;
        }
        $collection = $this->getInputData();
        $collection->each(function($struct, $key) {
            if(is_null($struct["subcode"])){
                $attainment = Attainment::where("base_subject_id", $struct["base_subject_id"])
                    ->where("education_level_id", $struct["education_level_id"])
                    ->where("code", $struct["code"])
                    ->whereNull('subcode')
                    ->whereNull('subsubcode')->first();
            }else {
                $attainment = Attainment::where("base_subject_id", $struct["base_subject_id"])
                    ->where("education_level_id", $struct["education_level_id"])
                    ->where("code", $struct["code"])
                    ->whereNotNull('subcode')
                    ->whereNull('subsubcode')->first();
            }
            if(!is_null($attainment)){
                $this->info(sprintf('parent already handled base_subject:%s, eductation_level:%s, code:%s',$struct["base_subject_name"],$struct["education_level_name"],$struct["code"]));
                return true;
            }
            $this->makeParent($struct);
        });
    }

    private function makeParent($struct)
    {
        $description = '';
        switch ($struct['code']){
            case 'K':
                $description = 'Kennis';
                break;
            case 'V':
                $description = 'Vaardigheden';
                break;
        }
        $fill = collect($struct)->merge(['description'=>$description,'status'=>'ACTIVE'])->toArray();
        $attainment  = new Attainment();
        $attainment->fill($fill);
        $attainment->save();
        $this->updateChildren($attainment,$struct);
    }

    private function updateChildren($attainment,$struct)
    {
        if(is_null($struct["subcode"])){
            $children = Attainment::where("base_subject_id" , $struct["base_subject_id"])
                ->where("education_level_id" , $struct["education_level_id"])
                ->where("code" , $struct["code"])
                ->whereNotNull('subcode')
                ->whereNull('subsubcode')
                ->get();
        }else{
            $children = Attainment::where("base_subject_id" , $struct["base_subject_id"])
                ->where("education_level_id" , $struct["education_level_id"])
                ->where("code" , $struct["code"])
                ->whereNotNull('subcode')
                ->whereNotNull('subsubcode')
                ->get();
        }
        $children->each(function($childAttainment) use ($attainment){
            $childAttainment->attainment_id = $attainment->getKey();
            $childAttainment->save();
        });
    }

    private function getInputData()
    {
        return collect([
            0 => [
                "base_subject_id" => 11,
                "base_subject_name" => "Biologie",
                "education_level_id" => 4,
                "education_level_name" => "Mavo / Vmbo tl",
                "code" => "K",
                "subcode" => null,
                "subsubcode" => null,
            ],
            13 => [
                "base_subject_id" => 11,
                "base_subject_name" => "Biologie",
                "education_level_id" => 4,
                "education_level_name" => "Mavo / Vmbo tl",
                "code" => "V",
                "subcode" => null,
                "subsubcode" => null,
            ],
            17 => [
                "base_subject_id" => 11,
                "base_subject_name" => "Biologie",
                "education_level_id" => 5,
                "education_level_name" => "Vmbo gl",
                "code" => "K",
                "subcode" => null,
                "subsubcode" => null,
            ],
            30 => [
                "base_subject_id" => 11,
                "base_subject_name" => "Biologie",
                "education_level_id" => 5,
                "education_level_name" => "Vmbo gl",
                "code" => "V",
                "subcode" => null,
                "subsubcode" => null,
            ],
            34 => [
                "base_subject_id" => 11,
                "base_subject_name" => "Biologie",
                "education_level_id" => 6,
                "education_level_name" => "Vmbo kb",
                "code" => "K",
                "subcode" => null,
                "subsubcode" => null,
            ],
            47 => [
                "base_subject_id" => 11,
                "base_subject_name" => "Biologie",
                "education_level_id" => 7,
                "education_level_name" => "Vmbo bb",
                "code" => "K",
                "subcode" => null,
                "subsubcode" => null,
            ],
            59 => [
                "base_subject_id" => 19,
                "base_subject_name" => "Maatschappijleer",
                "education_level_id" => 7,
                "education_level_name" => "Vmbo bb",
                "code" => "ML1/K",
                "subcode" => null,
                "subsubcode" => null,
            ],
            66 => [
                "base_subject_id" => 26,
                "base_subject_name" => "Wiskunde",
                "education_level_id" => 4,
                "education_level_name" => "Mavo / Vmbo tl",
                "code" => "K",
                "subcode" => null,
                "subsubcode" => null,
            ],
            74 => [
                "base_subject_id" => 26,
                "base_subject_name" => "Wiskunde",
                "education_level_id" => 4,
                "education_level_name" => "Mavo / Vmbo tl",
                "code" => "V",
                "subcode" => null,
                "subsubcode" => null,
            ],
            78 => [
                "base_subject_id" => 26,
                "base_subject_name" => "Wiskunde",
                "education_level_id" => 5,
                "education_level_name" => "Vmbo gl",
                "code" => "K",
                "subcode" => null,
                "subsubcode" => null,
            ],
            86 => [
                "base_subject_id" => 26,
                "base_subject_name" => "Wiskunde",
                "education_level_id" => 5,
                "education_level_name" => "Vmbo gl",
                "code" => "V",
                "subcode" => null,
                "subsubcode" => null,
            ],
            90 => [
                "base_subject_id" => 26,
                "base_subject_name" => "Wiskunde",
                "education_level_id" => 6,
                "education_level_name" => "Vmbo kb",
                "code" => "K",
                "subcode" => null,
                "subsubcode" => null,
            ],
            98 => [
                "base_subject_id" => 26,
                "base_subject_name" => "Wiskunde",
                "education_level_id" => 7,
                "education_level_name" => "Vmbo bb",
                "code" => "K",
                "subcode" => null,
                "subsubcode" => null,
            ],
            106 => [
                "base_subject_id" => 84,
                "base_subject_name" => "Turks",
                "education_level_id" => 3,
                "education_level_name" => "Havo",
                "code" => "A",
                "subcode" => 1,
                "subsubcode" => null,
            ],
            111 => [
                "base_subject_id" => 84,
                "base_subject_name" => "Turks",
                "education_level_id" => 3,
                "education_level_name" => "Havo",
                "code" => "B",
                "subcode" => 1,
                "subsubcode" => null,
            ]
        ]);

    }
}
