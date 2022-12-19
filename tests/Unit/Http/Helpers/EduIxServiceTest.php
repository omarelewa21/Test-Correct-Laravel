<?php


namespace Tests\Unit\Http\Helpers;


use tcCore\Http\Helpers\EduIxService;
use Tests\TestCase;

/**
 * @group ignore
 */
class EduIxServiceTest extends TestCase
{
    private $service;


    /** @test */
    public function is_can_return_a_ean()
    {
        $this->assertEquals(
            '9999999999444',
            $this->service->getEan()
        );
    }

    /** @test */
    public function is_can_return_a_digi_delivery_id()
    {
        $this->assertEquals(
            '97889c33-18c4-47a8-94ae-37f741fc19ab',
            $this->service->getDigiDeliveryId()
        );
    }

    /** @test */
    public function is_can_return_a_home_organisation_id()
    {
        $this->assertEquals(
            '35ZZ',
            $this->service->getHomeOrganizationId()
        );
    }

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->service = new EduIxService('1tcts328-i7og-ihri-d7ch-o62jhk0oha2f', 'c18f06a6cc7685149e78ac305094ebef');
    }
}