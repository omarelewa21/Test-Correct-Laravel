<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Livewire\Livewire;
use tcCore\Http\Helpers\TestTakeCodeHelper;
use tcCore\Http\Livewire\Auth\Login;
use tcCore\TestParticipant;
use tcCore\TestTakeCode;
use tcCore\User;
use Tests\TestCase;

class TestTakeCodeTest extends TestCase
{
    use DatabaseTransactions;

    public $loginComponent, $codeHelper;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->loginComponent = Livewire::test(Login::class)
            ->set(['firstName' => 'first', 'lastName' => 'last']);
        $this->codeHelper = new TestTakeCodeHelper();
    }

    /**
     * @test
     */
    public function login_with_incorrect_test_code_format_shows_error()
    {
        $this->loginComponent->set('testTakeCode', [1, 2, 3])
            ->call('guestLogin')
            ->assertHasErrors('invalid_test_code');
    }

    /**
     * @test
     */
    public function entered_test_code_with_null_values_shows_invalid_error()
    {
        $this->loginComponent->set('testTakeCode', [1, 2, 3, null, 5, 6])
            ->call('guestLogin')
            ->assertHasErrors('invalid_test_code');
    }

    /**
     * @test
     */
    public function entered_test_code_with_multiple_integer_values_shows_invalid_error()
    {
        $this->loginComponent->set('testTakeCode', [1, 2, 3, 11, 5, 6])
            ->call('guestLogin')
            ->assertHasErrors('invalid_test_code');
    }

    /**
     * @test
     */
    public function entered_test_code_with_string_value_shows_invalid_error()
    {
        $this->loginComponent->set('testTakeCode', [1, 2, 3, 'a', 5, 6])
            ->call('guestLogin')
            ->assertHasErrors('invalid_test_code');
    }

    /**
     * @test
     */
    public function entered_test_code_with_single_integer_values_shows_no_invalid_error()
    {
        $this->loginComponent->set('testTakeCode', [1, 2, 3, 1, 5, 6])
            ->call('guestLogin')
            ->assertHasNoErrors('invalid_test_code');
    }

    /**
     * @test
     */
    public function show_error_if_no_test_take_code_found_with_entered_code()
    {
        $enteredCode = [1, 2, 3, 1, 5, 6];

        $this->loginComponent->set('testTakeCode', $enteredCode)
            ->call('guestLogin')
            ->assertHasErrors('no_test_found_with_code');
    }

    /**
     * @test
     */
    public function code_helper_can_find_existing_test_take_code_record_by_array_or_string_of_numbers()
    {
        $testTakeCode = TestTakeCode::create(['test_take_id' => 1]);

        $codeToEnterOnLoginScreenAsArray = str_split($testTakeCode->code);
        $codeToEnterOnLoginScreenAsString = $testTakeCode->code;

        $this->assertNotNull($this->codeHelper->getTestTakeCodeIfExists($codeToEnterOnLoginScreenAsArray));
        $this->assertNotNull($this->codeHelper->getTestTakeCodeIfExists($codeToEnterOnLoginScreenAsString));
    }

    /**
     * @test
     */
    public function code_helper_can_create_user_by_test_take_code()
    {
        $testTakeCode = TestTakeCode::create(['test_take_id' => 22]);
        $userData = [
            'name_first' => 'Piet',
            'name' => 'Jansen'
        ];

        $createdUser = $this->codeHelper->createUserByTestTakeCode($userData, $testTakeCode);
        $latestUser = User::latest()->first();

        $this->assertNotNull($createdUser);
        $this->assertEquals($createdUser->getKey(), $latestUser->getKey());
    }

    /**
     * @test
     */
    public function code_helper_can_create_participant_by_test_take_code_for_user()
    {
        $testTakeCode = TestTakeCode::create(['test_take_id' => 22]);
        $userData = [
            'name_first' => 'Piet',
            'name' => 'Jansen'
        ];

        $createdUser = $this->codeHelper->createUserByTestTakeCode($userData, $testTakeCode);
        $createdParticipant = $this->codeHelper->createTestParticipantForGuestUserByTestTakeCode($createdUser, $testTakeCode);

        $databaseParticipant = TestParticipant::whereTestTakeId($testTakeCode->test_take_id)->whereUserId($createdUser->getKey())->first();

        $this->assertNotNull($createdParticipant);
        $this->assertEquals($createdParticipant->getKey(), $databaseParticipant->getKey());
    }
}
