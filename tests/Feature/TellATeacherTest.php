<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use tcCore\DemoTeacherRegistration;
use tcCore\Jobs\SendTellATeacherMail;
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
use tcCore\Http\Helpers\SchoolHelper;

class TellATeacherTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        Carbon::setTestNow('06-08-2020');
    }

    /** @test */
    public function a_teacher_can_invite_a_collegue()
    {
        Mail::fake();

        $this->assertFalse(User::whereUsername('fientje.van.amersfoort@sobit.nl')->exists());
        $this->assertFalse(DemoTeacherRegistration::whereUsername('fientje.van.amersfoort@sobit.nl')->exists());

        $this->assertCount(0, DemoTeacherRegistration::all());

        $response = $this->post(
            route('tell_a_teacher.store'),
            $this->getTeacherFromPrivateSchoolRequestData(
                $this->getValidAttributes([
                    'school_location_id' => 3,
                    'invited_by'         => $this->getTeacherFromPrivateSchool()->getKey(),
                    'step'               => '1',
                    'submit'             => 'false',
                ])
            )
        )->assertSuccessful();

        $this->assertEquals(
            ['success' => true],
            $response->decodeResponseJson()
        );

        $response = $this->post(
            route('tell_a_teacher.store'),
            $this->getTeacherFromPrivateSchoolRequestData(
                $this->getValidAttributes([
                    'school_location_id' => 3,
                    'invited_by'         => $this->getTeacherFromPrivateSchool()->getKey(),
                    'data'               => [
                        'message'         => 'lorem ipsum',
                        'email_addresses' => 'fientje.van.amersfoort@sobit.nl',
                    ],
                    'step'               => '2',
                    'submit'             => 'true',
                ])
            )
        )->assertSuccessful();
//        dd($response->decodeResponseJson());
        Mail::assertSent(SendTellATeacherMail::class, function ($mail) {
            return $mail->hasTo('fientje.van.amersfoort@sobit.nl');
        });
    }


    /** @test */
    public function a_teacher_can_invite_a_collegue_but_the_message_cannot_be_empty()
    {
        $response = $this->post(
            route('tell_a_teacher.store'),
            $this->getTeacherFromPrivateSchoolRequestData(
                $this->getValidAttributes([
                    'school_location_id' => 3,
                    'invited_by'         => $this->getTeacherFromPrivateSchool()->getKey(),
                    'data'               => [
                        'message'         => '',
                        'email_addresses' => 'fientjevanamersfoort@sobit.nl',
                    ],
                    'step'               => '2',
                    'submit'             => 'true',
                ])
            )
        )->assertStatus(422);

        $this->assertEquals(
            'Het bericht is verplicht',
            $response->decodeResponseJson()['errors']['data.message'][0]
        );
    }

    /** @test */
    public function a_teacher_can_invite_a_collegue_but_the_message_cannot_be_short()
    {
        Mail::fake();

        $response = $this->post(
            route('tell_a_teacher.store'),
            $this->getTeacherFromPrivateSchoolRequestData(
                $this->getValidAttributes([
                    'school_location_id' => 3,
                    'invited_by'         => $this->getTeacherFromPrivateSchool()->getKey(),
                    'data'               => [
                        'message'         => 'abcede',
                        'email_addresses' => 'fientjevanamersfoort@sobit.nl',
                    ],
                    'step'               => '2',
                    'submit'             => 'false',
                ])
            )
        )->assertStatus(422);

        $this->assertEquals(
            'Het bericht moet minimaal 10 karakters lang zijn.',
            $response->decodeResponseJson()['errors']['data.message'][0]
        );

        Mail::assertNothingSent();
    }

    /** @test */
    public function a_teacher_can_invite_a_collegue_but_the_email_address_should_bv_valid_in_step2_also()
    {
        Mail::fake();
        $response = $this->post(
            route('tell_a_teacher.store'),
            $this->getTeacherFromPrivateSchoolRequestData(
                $this->getValidAttributes([
                    'school_location_id' => 3,
                    'invited_by'         => $this->getTeacherFromPrivateSchool()->getKey(),
                    'data'               => [
                        'message'         => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean sollicitudin nibh a velit dictum, vitae pe',
                        'email_addresses' => 'fientjevanamersfoort@.nl',
                    ],
                    'step'               => '2',
                    'submit'             => 'false',
                ])
            )
        )->assertStatus(422);
        $this->assertEquals(
            ["Het e-mailadres <strong>fientjevanamersfoort@.nl</strong> is niet valide."],
            $response->decodeResponseJson()['errors']['form']
        );

        Mail::assertNothingSent();
    }


    /** @test */
    public function e_mail_addresses_should_be_a_valid_email()
    {
        Mail::fake();
        $this->assertCount(0, DemoTeacherRegistration::all());

        $response = $this->post(
            route('tell_a_teacher.store'),
            $this->getTeacherFromPrivateSchoolRequestData(
                $this->getValidAttributes([
                    'data'               => ['email_addresses' => 'not_valid'],
                    'school_location_id' => 3,
                    'invited_by'         => $this->getTeacherFromPrivateSchool()->getKey(),
                ])
            )
        )->assertStatus(422);

        $errors = $response->decodeResponseJson()['errors'];
        $this->assertArrayHasKey('email_addresses.0', $errors);
        $this->assertEquals(['The email_addresses.0 must be a valid email address.'], $errors['email_addresses.0']);
        $this->assertEquals(['Het e-mailadres <strong>not_valid</strong> is niet valide.'], $errors['form']);
        Mail::assertNothingSent();
    }

    /** @test */
    public function a_request_can_have_multiple_email_addresses_semicolon_seperated()
    {


        $this->assertCount(0, DemoTeacherRegistration::all());

        $response = $this->post(
            route('tell_a_teacher.store'),
            $this->getTeacherFromPrivateSchoolRequestData(
                $this->getValidAttributes([
                    'data'               => ['email_addresses' => 'm.folkerts@sobit.nl;martin@sobit.nl'],
                    'school_location_id' => 3,
                    'invited_by'         => $this->getTeacherFromPrivateSchool()->getKey(),
                ])
            )
        )->assertSuccessful();

    }

    /** @test */
    public function a_request_can_have_multiple_email_addresses_semicolon_seperated_then_multiple_mails_should_be_sent()
    {

        Mail::fake();

        $this->assertCount(0, DemoTeacherRegistration::all());

        $response = $this->post(
            route('tell_a_teacher.store'),
            $this->getTeacherFromPrivateSchoolRequestData(
                $this->getValidAttributes([
                    'data'               => [
                        'email_addresses' => 'm.folkerts@sobit.nl;martin@sobit.nl',
                        'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean sollicitudin nibh a velit dictum, vitae pe'
                    ],
                    'school_location_id' => 3,
                    'invited_by'         => $this->getTeacherFromPrivateSchool()->getKey(),
                    'step'=> '2',

                ])
            )
        )->assertSuccessful();
        Mail::assertSent(SendTellATeacherMail::class, 2);

    }


    /** @test */
    public function a_request_can_spot_which_email_addresses_are_not_valid()
    {
        $this->assertCount(0, DemoTeacherRegistration::all());

        $response = $this->post(
            route('tell_a_teacher.store'),
            $this->getTeacherFromPrivateSchoolRequestData(
                $this->getValidAttributes([
                    'data'               => ['email_addresses' => 'm.folkerts@sobit.nl;bogus;martin@sobit.nl'],
                    'school_location_id' => 3,
                    'invited_by'         => $this->getTeacherFromPrivateSchool()->getKey(),
                ])
            )
        )->assertStatus(422);

        $errors = $response->decodeResponseJson()['errors'];
        $this->assertArrayHasKey('email_addresses.1', $errors);
        $this->assertEquals(['The email_addresses.1 must be a valid email address.'], $errors['email_addresses.1']);
        $this->assertEquals(['De e-mailadressen m.folkerts@sobit.nl;<strong>bogus</strong>;martin@sobit.nl zijn niet valide.'],
            $errors['form']);
    }




//    /** @test */
//    public function a_teacher_can_invite_a_colleague_with_a_different_email_domain_this_user_should_be_added_to_the_temp_school(): void
//    {
//        // ticket 157
////        $this->withoutExceptionHandling();
//        Bus::fake();
//        Mail::fake();
//        $this->assertFalse(User::whereUsername('fientje.van.amersfoort@some_bogus.nl')->exists());
//        $this->assertFalse(DemoTeacherRegistration::whereUsername('fientje.van.amersfoort@some_bogus.nl')->exists());
//
//        $this->assertCount(0, DemoTeacherRegistration::all());
//
//        $response = $this->post(
//            route('user.store'),
//            $this->getTeacherFromPrivateSchoolRequestData(
//                $this->getValidAttributes([
//                    'school_location_id' => 3,
//                    'invited_by'         => $this->getTeacherFromPrivateSchool()->getKey(),
//                ])
//            )
//        )->assertSuccessful();
//
//        $fientje = User::whereUsername('fientje.van.amersfoort@some_bogus.nl')->first();
//        $this->assertNotNull($fientje);
//        $this->assertTrue($fientje->schoolLocation->is(SchoolHelper::getTempTeachersSchoolLocation()));
//
//        // user fientje moet welkoms mail gestuurd krijgen.
//        Bus::assertDispatched(SendWelcomeMail::class);
//
//        // een record krijgen in RegisteredDemoTeacher.
//        $this->assertTrue(DemoTeacherRegistration::whereUsername('fientje.van.amersfoort@some_bogus.nl')->exists());
//
//        // mail naar support met de melding nieuwe gebruiker.
//        $registration = DemoTeacherRegistration::where('username', $this->getValidAttributes()['username'])->first();
//        Mail::assertSent(TeacherRegistered::class, function ($mail) use ($registration) {
//            return $mail->hasTo('support@test-correct.nl') &&
//                $mail->demo->is($registration);
//        });
//    }

//    /** @test */
//    public function a_teacher_can_invite_a_colleague_with_a_different_email_domain_this_user_should_be_added_to_the_temp_school(): void
//    {
//        // ticket 157
////        $this->withoutExceptionHandling();
//        Bus::fake();
//        Mail::fake();
//        $this->assertFalse(User::whereUsername('fientje.van.amersfoort@some_bogus.nl')->exists());
//        $this->assertFalse(DemoTeacherRegistration::whereUsername('fientje.van.amersfoort@some_bogus.nl')->exists());
//
//        $this->assertCount(0, DemoTeacherRegistration::all());
//
//        $response = $this->post(
//            route('user.store'),
//            $this->getTeacherFromPrivateSchoolRequestData(
//                $this->getValidAttributes([
//                    'school_location_id' => 3,
//                    'invited_by'         => $this->getTeacherFromPrivateSchool()->getKey(),
//                ])
//            )
//        )->assertSuccessful();
//
//        $fientje = User::whereUsername('fientje.van.amersfoort@some_bogus.nl')->first();
//        $this->assertNotNull($fientje);
//        $this->assertTrue($fientje->schoolLocation->is(SchoolHelper::getTempTeachersSchoolLocation()));
//
//        // user fientje moet welkoms mail gestuurd krijgen.
//        Bus::assertDispatched(SendWelcomeMail::class);
//
//        // een record krijgen in RegisteredDemoTeacher.
//        $this->assertTrue(DemoTeacherRegistration::whereUsername('fientje.van.amersfoort@some_bogus.nl')->exists());
//
//        // mail naar support met de melding nieuwe gebruiker.
//        $registration = DemoTeacherRegistration::where('username', $this->getValidAttributes()['username'])->first();
//        Mail::assertSent(TeacherRegistered::class, function ($mail) use ($registration) {
//            return $mail->hasTo('support@test-correct.nl') &&
//                $mail->demo->is($registration);
//        });
//    }
//
//    /** @test */
//    public function a_teacher_can_invite_a_colleague_with_same_email_domain()
//    {
//        // maak de actuele periode voor de docent;
//        Carbon::setTestNow('06-08-2019');
//
//        Bus::fake();
//        Mail::fake();
//        $this->assertFalse(User::whereUsername('fientje.van.amersfoort@test-correct.nl')->exists());
//        $this->assertFalse(DemoTeacherRegistration::whereUsername('fientje.van.amersfoort@test-correct.nl')->exists());
//
//        $this->assertCount(0, DemoTeacherRegistration::all());
//
//        $response = $this->post(
//            route('user.store'),
//            $this->getTeacherFromPrivateSchoolRequestData(
//                $this->getValidAttributes([
//                    'school_location_id' => 3,
//                    'invited_by'         => $this->getTeacherFromPrivateSchool()->getKey(),
//                    'username'           => 'fientje.van.amersfoort@test-correct.nl',
//                ])
//            )
//        )->assertSuccessful();
//
//        $fientje = User::whereUsername('fientje.van.amersfoort@test-correct.nl')->first();
//        $this->assertNotNull($fientje);
//        $this->assertTrue($fientje->schoolLocation->isNot(SchoolHelper::getTempTeachersSchoolLocation()));
//        $this->assertEquals($fientje->school_location_id, $this->getTeacherFromPrivateSchool()->school_location_id);
//
//        // GEEN record krijgen in RegisteredDemoTeacher.
//        $this->assertFalse(DemoTeacherRegistration::whereUsername('fientje.van.amersfoort@test-correct.nl')->exists());
//
//        // user teacher fientje moet worden aangemaakt in de same school;
//        $this->assertTrue(User::whereUsername('fientje.van.amersfoort@test-correct.nl')->exists());
//        // user fientje moet welkomsmail gestuurd krijgen.
//        Bus::assertDispatched(SendWelcomeMail::class);
//        // geen mail naar Support desk,
//        Mail::assertNotSent(TeacherRegistered::class);
//    }
//
//    /** @test */
//    public function a_teacher_in_temp_school_can_invite_a_user_with_same_email_domain_in_the_temp_school()
//    {
//        // als mail domein hetzelfde is dan wordt record in RegisteredDemoTeacher verrijken met data van uitnodiger
//        Bus::fake();
//        Mail::fake();
//        $this->assertFalse(User::whereUsername('fientje.van.amersfoort@test-correct.nl')->exists());
//        $this->assertFalse(DemoTeacherRegistration::whereUsername('fientje.van.amersfoort@test-correct.nl')->exists());
//
//        $this->assertCount(0, DemoTeacherRegistration::all());
//
//        $response = $this->post(
//            route('user.store'),
//            $this->getTeacherFromPrivateSchoolRequestData(
//                $this->getValidAttributes([
//                    'username'           => 'fientje.van.amersfoort@test-correct.nl',
//                ])
//            )
//        )->assertSuccessful();
//
//        $fientje = User::whereUsername('fientje.van.amersfoort@test-correct.nl')->first();
//        $this->assertNotNull($fientje);
//        $this->assertTrue($fientje->schoolLocation->is(SchoolHelper::getTempTeachersSchoolLocation()));
//
//        // user fientje moet welkoms mail gestuurd krijgen.
//        Bus::assertDispatched(SendWelcomeMail::class);
//
//        // een record krijgen in RegisteredDemoTeacher.
//        $this->assertTrue(DemoTeacherRegistration::whereUsername('fientje.van.amersfoort@test-correct.nl')->exists());
//
//        // mail naar support met de melding nieuwe gebruiker.
//        $registration = DemoTeacherRegistration::whereUsername('fientje.van.amersfoort@test-correct.nl')->first();
//
//        Mail::assertSent(TeacherRegistered::class, function ($mail) use ($registration) {
//            return $mail->hasTo('support@test-correct.nl') &&
//                $mail->demo->id === $registration->id;
//        });
//    }
//
//    /** @test */
//    public function a_teacher_in_temp_school_can_invite_a_user_with_different_email_domain_in_the_temp_school()
//    {
//        Bus::fake();
//        Mail::fake();
//        $this->assertFalse(User::whereUsername('fientje.van.amersfoort@some_bogus.nl')->exists());
//        $this->assertFalse(DemoTeacherRegistration::whereUsername('fientje.van.amersfoort@some_bogus.nl')->exists());
//
//        $this->assertCount(0, DemoTeacherRegistration::all());
//
//        $response = $this->post(
//            route('user.store'),
//            $this->getTeacherFromPrivateSchoolRequestData(
//                $this->getValidAttributes()
//            )
//        )->assertSuccessful();
//
//        $fientje = User::whereUsername('fientje.van.amersfoort@some_bogus.nl')->first();
//        $this->assertNotNull($fientje);
//        $this->assertTrue($fientje->schoolLocation->is(SchoolHelper::getTempTeachersSchoolLocation()));
//
//        // user fientje moet welkoms mail gestuurd krijgen.
//        Bus::assertDispatched(SendWelcomeMail::class);
//
//        // een record krijgen in RegisteredDemoTeacher.
//        $this->assertTrue(DemoTeacherRegistration::whereUsername('fientje.van.amersfoort@some_bogus.nl')->exists());
//
//        // mail naar support met de melding nieuwe gebruiker.
//        $registration = DemoTeacherRegistration::whereUsername('fientje.van.amersfoort@some_bogus.nl')->first();
//
//        Mail::assertSent(TeacherRegistered::class, function ($mail) use ($registration) {
//            return $mail->hasTo('support@test-correct.nl') &&
//                $mail->demo->id === $registration->id;
//        });
//
//    }
//
//
    /**
     * @return array
     */
    private function getValidAttributes(array $overrides = []): array
    {
        // let op school_location_id = 1 is de "TC-tijdelijke-docenten-account school;
        return array_merge([
            'school_location_id' => '1',
            'user_roles'         => [1],
            'send_welcome_mail'  => true,
            'invited_by'         => $this->getTeacherFromTempSchool()->getKey(),
            'data'               => ['email_addresses' => 'fientje.van.amersfoort@sobit.nl'],
        ], $overrides);
    }

    private function getTeacherFromTempSchoolRequestData($overrides = [])
    {
        return self::getTeacherOneAuthRequestData($overrides);
    }

    private function getTeacherFromPrivateSchoolRequestData($overrides = [])
    {
        return self::getUserAuthRequestData(
            $this->getTeacherFromPrivateSchool(),
            $overrides
        );
    }

    private function getTeacherFromTempSchool()
    {
        return User::where('username', 'd1@test-correct.nl')->first();
    }

    private function getTeacherFromPrivateSchool()
    {
        return User::where('username', 'd3@test-correct.nl')->first();
    }
}
