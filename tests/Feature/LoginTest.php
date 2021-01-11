<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use Tests\TestCase;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function can_view_login_page()
    {
        $this->get(route('auth.login'))
            ->assertSuccessful()
            ->assertSeeLivewire('auth.login');
    }


    /** @test */
    public function can_login()
    {
        $user = self::studentOne();

        Livewire::test('auth.login')
            ->set('email', $user->email)
            ->set('password', 'Sobit4456')
            ->call('login');

        $this->assertTrue(
            auth()->user()->is(User::where('username', $user->email)->first())
        );
    }

//    /** @test */
//    public function is_redirected_to_intended_after_login_prompt_from_auth_guard()
//    {
//        Route::get('/intended')->middleware('auth');
//
//        $user = self::studentOne();
//
//        $this->get('/intended')->assertRedirect('/login');
//
//        Livewire::test('auth.login')
//            ->set('email', $user->email)
//            ->set('password', 'password')
//            ->call('login')
//            ->assertRedirect('/intended');
//    }

//    /** @test */
//    public function is_redirected_to_root_after_login()
//    {
//        $user = self::studentOne();
//
//        Livewire::test('auth.login')
//            ->set('email', $user->email)
//            ->set('password', 'Sobit4456')
//            ->call('login')
//            ->assertRedirect('/');
//    }

    /** @test */
    public function email_is_required()
    {

        Livewire::test('auth.login')
            ->set('password', 'Sobit4456')
            ->call('login')
            ->assertHasErrors(['email' => 'required']);
    }

    /** @test */
    public function email_must_be_valid_email()
    {
        User::factory()->create();

        Livewire::test('auth.login')
            ->set('email', 'invalid-email')
            ->set('password', 'Sobit4456')
            ->call('login')
            ->assertHasErrors(['email' => 'email']);
    }

    /** @test */
    public function password_is_required()
    {
        $user = self::studentOne();

        Livewire::test('auth.login')
            ->set('email', $user->email)
            ->call('login')
            ->assertHasErrors(['password' => 'required']);
    }

    /** @test */
    public function bad_login_attempt_shows_message()
    {
        $user = self::studentOne();

        Livewire::test('auth.login')
            ->set('email', $user->email)
            ->set('password', 'bad-password')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertNull(auth()->user());
    }

    private static function studentOne(){
        return User::firstWhere('username', 's1@test-correct.nl');
    }
}
