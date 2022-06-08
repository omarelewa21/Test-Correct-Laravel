<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use tcCore\Attainment;
use Tests\TestCase;

class RepairAttainmentParentsTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testRepairAttainmentParentsCommand()
    {
        Artisan::call('repair:attainment_parents', ['--no_check' => true]);
        $collection = $this->getInputData();
        $collection->each(function ($struct, $key) {
            if (is_null($struct["subcode"])) {
                $this->checkAttainment($struct);
            } else {
                $this->checkAttainmentWithSub($struct);
            }
        });

    }

    private function checkAttainment($struct)
    {
        $attainment = Attainment::where(["base_subject_id" => $struct["base_subject_id"],
            "education_level_id" => $struct["education_level_id"],
            "code" => $struct["code"]])->whereNotNull('subcode')->whereNull('subsubcode')->first();
        $this->assertNotNull($attainment);
        $parent = Attainment::find($attainment->attainment_id);
        $this->assertNotNull($parent);
        $this->assertEquals($parent->base_subject_id, $struct["base_subject_id"]);
        $this->assertEquals($parent->education_level_id, $struct["education_level_id"]);
        $this->assertEquals($parent->code, $struct["code"]);
        if($struct["code"]=='K'){
            $this->assertEquals($parent->description, "Kennis");
        }elseif($struct["code"]=='V'){
            $this->assertEquals($parent->description, "Vaardigheden");
        }
        $this->assertNull($parent->subcode);
    }

    private function checkAttainmentWithSub($struct)
    {
        $attainment = Attainment::where(["base_subject_id" => $struct["base_subject_id"],
            "education_level_id" => $struct["education_level_id"],
            "code" => $struct["code"],
            "subcode" => $struct["subcode"]])->whereNotNull('subsubcode')->first();
        $this->assertNotNull($attainment);
        $parent = Attainment::find($attainment->attainment_id);
        $this->assertNotNull($parent);
        $this->assertEquals($parent->base_subject_id, $struct["base_subject_id"]);
        $this->assertEquals($parent->education_level_id, $struct["education_level_id"]);
        $this->assertEquals($parent->code, $struct["code"]);
        $this->assertNull($parent->subsubcode);
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
