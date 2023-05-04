<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use tcCore\EanCode;
use tcCore\Exceptions\Handler;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\SchoolLocation;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

/**
 * @group ignore
 * This is a test for Edu-ix which has been developed in 2019 but I think EduIx is not or no longer used;
 * all code related to this should go. MF 26-01-2023
 */
class HomeControllerTest extends TestCase
{

    private $ean = '9999999999444';
    private $sessionId = '1tcts328-i7og-ihri-d7ch-o62jhk0oha2f';
    private $signature = 'c18f06a6cc7685149e78ac305094ebef';

    private $schoolLocationForService = false;

    protected $loadScenario = FactoryScenarioSchoolSimple::class;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = ScenarioLoader::get('user');


        $this->schoolLocationForService = $this->user->schoolLocation;
        $this->schoolLocationForService->edu_ix_organisation_id = '35ZZ';
        $this->schoolLocationForService->save();
    }

    /** @test */
    public function valid_params_should_lead_to_a_user_entry()
    {
        EanCode::create([
            'ean'         => $this->ean,
            'description' => 'lorem',
        ]);
        $this->assertCount(
            0,
            User::whereUsername('123@edu-ix.nl')->get()
        );

        $response = $this->getTestResponse($this->validAttributes());

        $persistedUser = User::whereUsername('123@edu-ix.nl')->first();

        $this->assertNotNull($persistedUser);
        $this->assertTrue(
            $persistedUser->schoolLocation->is($this->schoolLocationForService)
        );
    }

    /** @test */
    public function password_field_is_required()
    {
        $validAttributes = $this->validAttributes();
        unset($validAttributes['password']);

        $response = $this->getTestResponse($validAttributes);

        $this->assertResponseHasError($response, 'password');
    }

    /** @test */
    public function when_digi_deliver_id_already_used_it_should_throw_an_error()
    {
        $this->getTestResponse($this->validAttributes());
        $response = $this->getTestResponse($this->validAttributes(['username' => 'jtestDummy@test.nl']));

        $this->assertEquals(
            "Failed to create user",
            $response->getContent()
        );
        $response->assertStatus(500);
    }

    /** @test */
    public function ean_code_must_be_registered_in_db()
    {
        //remove the ean code;
        EanCode::where('ean', $this->ean)->delete();

        $validAttributes = $this->validAttributes();
        unset($validAttributes['password']);

        $response = $this->getTestResponse($validAttributes);

        $this->assertResponseHasError($response, 'ean');
    }

    /** @test */
    public function when_ean_in_db_no_error_for_ean()
    {
        $validAttributes = $this->validAttributes();
        unset($validAttributes['password']);

        $response = $this->getTestResponse($validAttributes);

        $this->assertResponseHasNoError($response, 'ean');
    }

    /** @test */
    public function when_school_location_not_in_db_it_should_error()
    {
        $locations = SchoolLocation::where('edu_ix_organisation_id', '35ZZ');
        $locations->get()->each(function ($location) {
            $location->edu_ix_organisation_id = null;
            $location->save();
        });

        $this->assertCount(
            0,
            SchoolLocation::where('edu_ix_organisation_id', '35ZZ')->get()
        );

        $response = $this->getTestResponse($this->validAttributes());

        $this->assertResponseHasError($response, 'school');
    }

    /** @test */
    public function when_school_location_in_db_not_error()
    {
        $this->assertCount(
            1,
            SchoolLocation::where('edu_ix_organisation_id', '35ZZ')->get()
        );

        $response = $this->getTestResponse($this->validAttributes());

        $this->assertResponseHasNoError($response, 'school');
    }

    /** @test */
    public function password_confirm_field_is_required()
    {
        $validAttributes = $this->validAttributes();
        unset($validAttributes['password_confirm']);

        $response = $this->getTestResponse(
            $validAttributes
        );

        $this->assertResponseHasError($response, 'password_confirm');
    }

    /** @test */
    public function username_field_is_required()
    {
        $validAttributes = $this->validAttributes();
        unset($validAttributes['username']);

        $response = $this->getTestResponse($validAttributes);

        $this->assertResponseHasError($response, 'username');
    }

    /** @test */
    public function name_first_field_is_required()
    {
        $validAttributes = $this->validAttributes();
        unset($validAttributes['name_first']);

        $response = $this->getTestResponse($validAttributes);

        $this->assertResponseHasError($response, 'name_first');
    }

    /** @test */
    public function password_should_be_at_least_eight_characters_long()
    {
        $validAttributes = $this->validAttributes([
            'password'         => 'abc123',
            'password_confirm' => 'abc123',
        ]);

        $response = $this->getTestResponse($validAttributes);

        $this->assertResponseHasError($response, 'password');
    }


    private function validAttributes($overrides = [])
    {
        return array_merge([
            'username'         => '123@edu-ix.nl',
            'name_first'       => 'Stefan',
            'name_suffix'      => '',
            'name'             => 'Veenedal',
            'password'         => 'abCd1234',
            'password_confirm' => 'abCd1234',
            'gender'           => 'Male',
        ],
            $overrides
        );
    }


    /** @test */
    public function it_should_return_edu_profile()
    {
        $jsonResponse = $this->get(
            sprintf('edu-ix/%s/%s/%s',
                $this->ean,
                $this->sessionId,
                $this->signature
            )
        )->decodeResponseJson();

        $this->assertEquals(
            [
                "eduProfile"   => [
                    "uid"                => "a7d3dc1128fa094c8dd06e0e90fa14g86a19c1c4@edu-ix.nl",
                    "personRealID"       => "123@edu-ix.nl",
                    "givenName"          => "Stefan",
                    "sn"                 => "Veenedal",
                    "affiliation"        => "student",
                    "digiDeliveryID"     => "97889c33-18c4-47a8-94ae-37f741fc19ab",
                    "homeOrganization"   => "Edu-iX Test College",
                    "homeOrganizationID" => "35ZZ",
                ],
                "personCredit" => [
                    "personCreditInformation" => [
                        "personCredit"  => [
                            "ean"                => "9999999999444",
                            "creditStartDate"    => "2019-12-05",
                            "creditEndDate"      => "2020-06-02",
                            "personProductState" => "Active",
                            "schoolYear"         => 1920,
                        ],
                        "specification" => [
                            "specificationState"     => "Specified",
                            "specificationStateDate" => "2019-12-05",
                        ],
                        "license"       => [
                            "activationDate"   => "2019-06-02",
                            "licenseStartDate" => "2019-12-05",
                            "licenseEndDate"   => "2020-06-02",
                        ]
                    ]
                ],
                "schoolCredit" => [],

            ], $jsonResponse);

    }

    private function assertResponseHasError(\Illuminate\Foundation\Testing\TestResponse $response, string $field)
    {
        $arr = $response->decodeResponseJson();
        if (array_key_exists('errors', $arr)) {
            $this->assertArrayHasKey($field, $arr['errors']);
            return;
        }
        $this->assertFalse(true);
    }

    private function assertResponseHasNoError(\Illuminate\Foundation\Testing\TestResponse $response, string $field)
    {
        $arr = $response->decodeResponseJson();
        if (array_key_exists('errors', $arr)) {
            $this->assertArrayNotHasKey($field, $arr['errors']);
            return;
        }
        $this->assertTrue(true);
    }


    private function getTestResponse(array $attributes)
    {
        return $this->post(
            sprintf(
                'api-c/edu-ix/%s/%s/%s',
                $this->ean,
                $this->sessionId,
                $this->signature
            ),
            $attributes
        );
    }
}
