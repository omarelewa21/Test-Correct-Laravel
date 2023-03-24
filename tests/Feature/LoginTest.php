<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
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
}
