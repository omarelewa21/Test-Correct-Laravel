<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use tcCore\Exceptions\Handler;
use tcCore\User;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $ean = '9999999999444';
    private $sessionId = '1tcts328-i7og-ihri-d7ch-o62jhk0oha2f';
    private $signature = 'c18f06a6cc7685149e78ac305094ebef';

    /** @test */
    public function password_field_is_required()
    {
        $validAttributes = $this->validAttributes();
        unset($validAttributes['password']);

        $response = $this->post(
            sprintf(
                'edu-ix/%s/%s/%s',
                $this->ean,
                $this->sessionId,
                $this->signature
            ),
            $validAttributes
        );

        $this->assertResponseHasError($response, 'password');
    }

    /** @test */
    public function ean_code_must_be_registered_in_db()
    {
        //remove the ean code;
        \tcCore\EanCode::where('ean', $this->ean)->delete();

        $validAttributes = $this->validAttributes();
        unset($validAttributes['password']);

        $response = $this->post(
            sprintf(
                'edu-ix/%s/%s/%s',
                $this->ean,
                $this->sessionId,
                $this->signature
            ),
            $validAttributes
        );

        $this->assertResponseHasError($response, 'ean');
    }

    /** @test */
    public function when_ean_in_db_no_error_for_ean()
    {
        $validAttributes = $this->validAttributes();
        unset($validAttributes['password']);

        $response = $this->post(
            sprintf(
                'edu-ix/%s/%s/%s',
                $this->ean,
                $this->sessionId,
                $this->signature
            ),
            $validAttributes
        );

        $this->assertResponseHasNoError($response, 'ean');
    }



    /** @test */
    public function password_confirm_field_is_required()
    {
        $validAttributes = $this->validAttributes();
        unset($validAttributes['password_confirm']);

        $response = $this->post(
            sprintf(
                'edu-ix/%s/%s/%s',
                $this->ean,
                $this->sessionId,
                $this->signature
            ),
            $validAttributes
        );

        $this->assertResponseHasError($response, 'password_confirm');
    }

    /** @test */
    public function username_field_is_required()
    {
        $validAttributes = $this->validAttributes();
        unset($validAttributes['username']);

        $response = $this->post(
            sprintf(
                'edu-ix/%s/%s/%s',
                $this->ean,
                $this->sessionId,
                $this->signature
            ),
            $validAttributes
        );

        $this->assertResponseHasError($response, 'username');
    }

    /** @test */
    public function name_first_field_is_required()
    {
        $validAttributes = $this->validAttributes();
        unset($validAttributes['name_first']);

        $response = $this->post(
            sprintf(
                'edu-ix/%s/%s/%s',
                $this->ean,
                $this->sessionId,
                $this->signature
            ),
            $validAttributes
        );

        $this->assertResponseHasError($response, 'name_first');
    }

    /** @test */
    public function password_should_be_at_least_eight_characters_long()
    {
        $validAttributes = $this->validAttributes([
            'password'         => 'abc123',
            'password_confirm' => 'abc123',
        ]);

        $response = $this->post(
            sprintf(
                'edu-ix/%s/%s/%s',
                $this->ean,
                $this->sessionId,
                $this->signature
            ),
            $validAttributes
        );

        $this->assertResponseHasError($response, 'password');
    }


    private function validAttributes($overrides = [])
    {
        return array_merge([
            'username'         => '123@edu-ix.nl',
            'name_first'       => 'Stefan',
            'name_suffix'      => 'Veenedal',
            'name'             => '',
            'password'         => 'abcd1234',
            'password_confirm' => 'abcd1234',
            'gender'           => 'm',
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
        $this->assertFalse();
    }

    private function assertResponseHasNoError(\Illuminate\Foundation\Testing\TestResponse $response, string $field)
    {
        $arr = $response->decodeResponseJson();
        if (array_key_exists('errors', $arr)) {
            $this->assertArrayNotHasKey($field, $arr['errors']);
            return;
        }
        $this->assertTrue();
    }

}
