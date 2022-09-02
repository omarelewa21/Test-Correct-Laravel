<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Attainment;
use tcCore\BaseSubject;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;
use tcCore\PValueAttainment;
use tcCore\Subject;
use Tests\TestCase;

class PValueTaxonomyRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_show_a_bloom_p_value_report_for_a_subject()
    {
        //  $factory = FactoryScenarioTestTakeRated::create($this->getTeacherOne());

        $studentOne = $this->getStudentOne();
        $data = PValueTaxonomyBloomRepository::getPValueForStudentForSubject(
            $studentOne,
            1,
            collect(),
            collect(),
            collect()

        );

        $this->assertEquals(count(PValueTaxonomyBloomRepository::OPTIONS), collect($data)->count());
        $this->assertEquals(count(PValueTaxonomyBloomRepository::OPTIONS), collect($data)->filter(function ($item) {
            return in_array($item[0], PValueTaxonomyBloomRepository::OPTIONS);
        })->count());
    }

    /** @test */
    public function can_generate_a_empty_taxanomy_options_graph_data_array()
    {
        $data = PValueTaxonomyMillerRepository::createEmptyTaxonomyResponse(PValueTaxonomyMillerRepository::OPTIONS);

        $this->assertEquals(PValueTaxonomyMillerRepository::OPTIONS, array_keys($data));
        $this->assertEquals(count(PValueTaxonomyMillerRepository::OPTIONS), collect($data)->filter(function ($item) {
            return $item === ['score' => 0, 'count' => 0];
        })->count());
    }

    /** @test */
    public function it_can_show_a_miller_p_value_report_for_a_subject()
    {
//        $factory = FactoryScenarioTestTakeRated::create($this->getTeacherOne());

        $studentOne = $this->getStudentOne();
        $data = PValueTaxonomyMillerRepository::getPValueForStudentForSubject(
            $studentOne,
            1,
            collect(),
            collect(),
            collect()

        );

        $this->assertEquals(count(PValueTaxonomyMillerRepository::OPTIONS), collect($data)->count());
        $this->assertEquals(count(PValueTaxonomyMillerRepository::OPTIONS), collect($data)->filter(function ($item) {
            return in_array($item[0], PValueTaxonomyMillerRepository::OPTIONS);
        })->count());
    }

    /** @test */
    public function it_can_show_a_rtti_p_value_report_for_a_subject()
    {
//          $factory = FactoryScenarioTestTakeRated::create($this->getTeacherOne());

        $studentOne = $this->getStudentOne();
        $data = PValueTaxonomyRTTIRepository::getPValueForStudentForSubject(
            $studentOne,
            1,
            collect(),
            collect(),
            collect()

        );

        $this->assertEquals(count(PValueTaxonomyRTTIRepository::OPTIONS), collect($data)->count());
        $this->assertEquals(count(PValueTaxonomyRTTIRepository::OPTIONS), collect($data)->filter(function ($item) {
            return in_array($item[0], PValueTaxonomyRTTIRepository::OPTIONS);
        })->count());
    }

    /** @test */
    public function it_can_show_a_rtti_p_value_report_for_a_attainment()
    {
//          $factory = FactoryScenarioTestTakeRated::create($this->getTeacherOne());

        $studentOne = $this->getStudentOne();


        $attainment_id = PValueAttainment::whereIn('p_value_attainments.attainment_id',
            Attainment::where('base_subject_id', 1)->whereNull('attainment_id')->select('id'))
            ->groupBy('p_value_attainments.attainment_id')
            ->selectRaw('p_value_attainments.attainment_id, count(p_value_attainments.attainment_id) as count')
            ->orderByDesc('count')
            ->first()->attainment_id;

        $data = PValueTaxonomyRTTIRepository::getPValueForStudentForAttainment(
            $studentOne,
            $attainment_id,
            collect(),
            collect(),
            collect()

        );
        $this->assertEquals(count(PValueTaxonomyRTTIRepository::OPTIONS), collect($data)->count());
        $this->assertEquals(count(PValueTaxonomyRTTIRepository::OPTIONS), collect($data)->filter(function ($item) {
            return in_array($item[0], PValueTaxonomyRTTIRepository::OPTIONS);
        })->count());
    }
}