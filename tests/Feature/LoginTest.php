<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Livewire\Auth\Login;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;
use Livewire\Livewire;

class LoginTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = ScenarioLoader::get('user');
    }

    /** @test */
    public function can_view_login_page()
    {
        $this->get(route('auth.login'))
            ->assertSuccessful()
            ->assertSeeLivewire('auth.login');
    }

    /** @test */
    public function teacher_can_login()
    {
        Livewire::test('auth.login')
            ->set('username', $this->user->username)
            ->set('password', 'TCSoBit500')
            ->call('login');

        $this->assertTrue(
            auth()->user()->is(User::where('username', $this->user->username)->first())
        );
    }

    /** @test */
    public function email_is_required()
    {
        Livewire::test('auth.login')
            ->set('password', 'TCSoBit500')
            ->call('login')
            ->assertHasErrors(['username' => 'required']);
    }

    /** @test */
    public function email_must_be_valid_email()
    {
        Livewire::test('auth.login')
            ->set('username', 'invalid-email')
            ->set('password', 'TCSoBit500')
            ->call('login')
            ->assertHasErrors(['username' => 'email']);
    }

    /** @test */
    public function password_is_required()
    {
        Livewire::test('auth.login')
            ->set('username', $this->user->username)
            ->call('login')
            ->assertHasErrors(['password' => 'required']);
    }

    /** @test */
    public function bad_login_attempt_shows_message()
    {
        Livewire::test('auth.login')
            ->set('username', $this->user->username)
            ->set('password', 'bad-password')
            ->call('login')
            ->assertHasErrors('invalid_user');

        $this->assertNull(auth()->user());
    }

    /**
     * @test
     * @dataProvider loginTestDirectCodeDataProvider
     */
    public function login_can_check_for_correct_test_code($testTakeCode, $expectedResult)
    {
        $obj = new Login();
        $obj->testTakeCode = $testTakeCode;

        $res = $this->callPrivateMethod($obj, 'isTestTakeCodeCorrectFormat', []);

        $this->assertEquals($expectedResult, $res);
    }

    public function loginTestDirectCodeDataProvider()
    {
        return [
            'valid code #1' => [['1', '2', '3', '4', '5', '6'], true],
            'valid code #2' => [[1, 2, 3, 4, 5, 6], true], //input values are always strings, but no harm allowing integers
            'invalid code #1' => [['1', '2', 'a', '4', '5', '6'], false],
            'invalid code #2' => [['1', '2', '', '4', '5', '6'], false],
            'invalid code #3' => [[' ', '', '', '4', '5', '6'], false],
            'invalid code #4' => [['1', '2', '3', '4', '5', ''], false],
        ];
    }
}
