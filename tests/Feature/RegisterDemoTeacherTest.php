<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use tcCore\DemoTeacherRegistration;
use tcCore\Jobs\SendWelcomeMail;
use tcCore\Mail\TeacherRegistered;
use tcCore\Teacher;
use tcCore\TestQuestion;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\OpenQuestionTrait;
use Tests\Traits\TestTrait;

class RegisterDemoTeacherTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        Carbon::setTestNow('06-08-2020');
    }

    /** @test */
    public function a_teacher_can_register_for_a_demo_account(): void
    {
        $this->withoutExceptionHandling();

        $this->assertCount(0, DemoTeacherRegistration::all());

        $usersInDb = User::pluck('id');

        $response = $this->post(
            route('demo_account.store'),
            $this->getValidAttributes([])
        );

        $newUserCollection = User::whereNotIn('id', $usersInDb)->where('demo', 0)->get();


        // a user Record of type teacher has been created;
        $this->assertCount(1, $newUserCollection);

        $this->assertTrue(
            $newUserCollection->first()->isA('teacher')
        );

        $this->assertEquals(
            'fientje.van.amersfoort@some_bogus.nl',
            $newUserCollection->first()->username
        );

        $response->assertSuccessful();
        $this->assertCount(1, DemoTeacherRegistration::all());

        $registration = DemoTeacherRegistration::first()->toArray();

        foreach ($this->getValidAttributes() as $key => $value) {
            $this->assertEquals($value, $registration[$key]);
        }
    }

    /** @test */
    public function it_sends_an_email_to_customer_support_when_a_new_teacher_is_registered()
    {
        Mail::fake();

        $response = $this->post(
            route('demo_account.store'),
            $this->getValidAttributes([])
        );

        $registration = DemoTeacherRegistration::where('username', $this->getValidAttributes()['username'])->first();

        Mail::assertSent(TeacherRegistered::class, function ($mail) use ($registration) {
            return $mail->hasTo('support@test-correct.nl') &&
                $mail->demo->is($registration);
        });
    }

    /** @test */
    public function it_sends_an_email_to_the_new_teacher_when_a_user_is_registered()
    {
        Bus::fake();

        $response = $this->post(
            route('demo_account.store'),
            $this->getValidAttributes([])
        );

        Bus::assertDispatched(SendWelcomeMail::class);
    }

    /** @test */
    public function when_a_email_address_already_exists_it_creates_the_registration_but_not_the_account()
    {
        Bus::fake();
        Mail::fake();

        $startCountRegistrations = DemoTeacherRegistration::count();
        $startCount = User::count();

        $response = $this->post(route('demo_account.store'), $this->getValidAttributes(['username' => self::USER_TEACHER]));
        $response->assertSuccessful();
        $this->assertCount($startCount, User::all()->fresh());
        $this->assertCount(++$startCountRegistrations, DemoTeacherRegistration::all()->fresh());
        $lastRegistration = DemoTeacherRegistration::orderBy('id', 'desc')->first();

        Bus::assertNotDispatched(SendWelcomeMail::class);

        Mail::assertSent(TeacherRegistered::class, function ($mail) use ($lastRegistration) {
            return $mail->hasTo('support@test-correct.nl') &&
//                $mail->hasFrom(self::USER_TEACHER) &&
                $mail->demo->is($lastRegistration) &&
                $mail->withDuplicateEmailAddress === true;
        });
    }


    /**
     * @test
     * @dataProvider requiredFields
     */
    public function field_is_required($field)
    {
        $response = $this->post(route('demo_account.store'), $this->getValidAttributes([$field => '']));
        $response->assertStatus(425);
        $this->assertArrayHasKey($field, $response->decodeResponseJson()['errors']);
    }

    public function requiredFields()
    {
        return [
            ['school_location'],
            ['website_url'],
            ['address'],
            ['postcode'],
            ['city'],
            ['gender'],
            ['name_first'],
            ['name'],
            ['username'],
            ['subjects'],
        ];
    }

    /** @test */
    public function field_username_contains_a_valid_email()
    {
        $response = $this->post(route('demo_account.store'), $this->getValidAttributes(['username' => 'aaa']));
        $response->assertStatus(425);
        $this->assertArrayHasKey('username', $response->decodeResponseJson()['errors']);
        $this->assertCount(0, DemoTeacherRegistration::all());
    }


    /**
     * @test
     * @dataProvider optionalFields
     */
    public function field_is_optional($field)
    {
        $response = $this->post(route('demo_account.store'), $this->getValidAttributes([$field => '']));
        $response->assertSuccessful();
        $this->assertCount(1, DemoTeacherRegistration::all());
    }

    public function optionalFields()
    {
        return [
            ['name_suffix'],
            ['remarks'],
            ['how_did_you_hear_about_test_correct'],
        ];
    }


    /**
     * @return array
     */
    private function getValidAttributes(array $overrides = []): array
    {
        return array_merge([
            'school_location' => 'Drenthe college',
            'website_url' => 'www.drenthecollege.nl',
            'address' => 'Anne de Vriesstraat 70',
            'postcode' => '9402 NT',
            'city' => 'Assen',
            'gender' => 'Male',
            'name_first' => 'Fientje',
            'name_suffix' => 'van',
            'name' => 'Amersfoort',
            'username' => 'fientje.van.amersfoort@some_bogus.nl',
            'subjects' => 'Wiskunde',
            'remarks' => 'remarks',
            'how_did_you_hear_about_test_correct' => 'online',
            'mobile' => '0612345678',
            'abbreviation' => 'abc',
        ], $overrides);
    }


}
