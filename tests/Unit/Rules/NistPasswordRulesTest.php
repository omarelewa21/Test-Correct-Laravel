<?php

namespace Tests\Unit\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use tcCore\Rules\NistPasswordRules;
use Tests\TestCase;

class NistPasswordRulesTest extends TestCase
{

    /**
     * Test the rules used on the login page
     * @test
     */
    public function loginPageChangePasswordRulesTest()
    {
        $username = "validuser@test-correct.nl";
        $validNewPassword = "|k(Y{Cyax|;*,Ev~e";
        $token = "validtoken";

        $invalidPasswords = [
            'too short' => 'aA1!',
            'sequential' => '12345678',
            'repeating' => 'aaaaaaaa',
            'dictionary1' => 'password',
            'dictionary2' => 'appelboom',
            'context-specific1' => 'test-correct', //domain part of email is not allowed to be used in a password
            'context-specific2' => 'validuser', //username part of email is not allowed to be used in a password
        ];

        //we are not checking the old password, so we don't pass a second parameter
        $rules = [
            'username' => 'required|email',
            'password' => NistPasswordRules::changePassword($username),
            'token'    => 'required',
        ];

        //valid change password request
        $dataValid = [
            'username'              => $username,
            'password'              => $validNewPassword,
            'password_confirmation' => $validNewPassword,
            'token'                 => $token,
        ];
        $validator = Validator::make($dataValid, $rules);
        $this->assertTrue($validator->passes());

        //invalid change password request, password confirmation not the same
        $dataInvalidNotTheSame = [
            'username'              => $username,
            'password'              => $validNewPassword,
            'password_confirmation' => $validNewPassword . 'a',
            'token'                 => $token,
        ];
        $validator = Validator::make($dataInvalidNotTheSame, $rules);
        $this->assertTrue($validator->fails());

        //check multiple invalid passwords
        foreach($invalidPasswords as $invalidPassword) {
            $dataInvalid = [
                'username'              => $username,
                'password'              => $invalidPassword,
                'password_confirmation' => $invalidPassword,
                'token'                 => $token,
            ];
            $validator = Validator::make($dataInvalid, $rules);
            $this->assertTrue($validator->fails());
        }

    }

    /**
     * Test the rules used on the onboarding page when registering
     * @test
     */
    public function onboardingPageRegisterRulesTest()
    {
        $username = "validuser@test-correct.nl";
        $validNewPassword = "|k(Y{Cyax|;*,Ev~e";

        $invalidPasswords = [
            'too short' => 'aA1!',
            'sequential' => '12345678',
            'repeating' => 'aaaaaaaa',
            'dictionary1' => 'password',
            'dictionary2' => 'appelboom',
            'context-specific1' => 'test-correct', //domain part of email is not allowed to be used in a password
            'context-specific2' => 'validuser', //username part of email is not allowed to be used in a password
        ];

        //we are not checking the old password, so we don't pass a second parameter
        $rules = [
            'username' => 'required|email',
            'password' => NistPasswordRules::register($username),
        ];

        //valid change password request
        $dataValid = [
            'username'              => $username,
            'password'              => $validNewPassword,
            'password_confirmation' => $validNewPassword,
        ];
        $validator = Validator::make($dataValid, $rules);
        $this->assertTrue($validator->passes());

        //invalid change password request, password confirmation not the same
        $dataInvalidNotTheSame = [
            'username'              => $username,
            'password'              => $validNewPassword,
            'password_confirmation' => $validNewPassword . 'a',
        ];
        $validator = Validator::make($dataInvalidNotTheSame, $rules);
        $this->assertTrue($validator->fails());

        //check multiple invalid passwords
        foreach($invalidPasswords as $invalidPassword) {
            $dataInvalid = [
                'username'              => $username,
                'password'              => $invalidPassword,
                'password_confirmation' => $invalidPassword,
            ];
            $validator = Validator::make($dataInvalid, $rules);
            $this->assertTrue($validator->fails());
        }

    }

    //test valid and invalid passwords
    public static function dictionaryWordsProvider()
    {
        return [
            //english words
            ['testtest'],
            ['password'],
            ['Alabama'],
            ['Alzheimer'],
            ['marshmallows'],
            ['pawnbrokers'],
            //dutch words
            ['adembenemend'],
            ['bekendmakingswet'],
            ['woordenboek'],
            ['koekoeksklok'],
            ['hondenmand'],
            ['kaasplank'],
        ];
    }

    public static function nonDictionaryWordsProvider()
    {
        return [
            ['tesb1234'],
            ['passwordz'],
            ['c_a_t_a_a'],
            ['d0gd0gd0g'],
            ['ch33s3ch33s3'],
            //dutch words
            ['ademb3nemend123'],
            ['bek3ndbek3nd'],
            ['w0ord1234'],
            ['k0e123k0e123'],
            ['h0nd1234'],
            ['k@as1234'],
        ];
    }

    /**
     * @dataProvider dictionaryWordsProvider
     */
    public function testFail($password)
    {
        $ruleSets = $this->getAvailableRuleSets();

        $username = 'a.teacher@test-correct.nl';

        foreach ($ruleSets as $ruleSet) {

            $data = array_filter(['username' => $username, 'password' => $password], function ($key) use ($ruleSet) {
                return in_array($key, $ruleSet['parameters']);
            },                   ARRAY_FILTER_USE_KEY);

            $rules = NistPasswordRules::{$ruleSet['method']}(...$data);

            $validator = \Validator::make([
                                              'username'              => $username,
                                              'password'              => $password,
                                              'password_confirmation' => $password
                                          ],
                                          ['password' => $rules]
            );

            if ($ruleSet['method'] === 'login') {
                //The login method does not check for anything except 'required' and 'string'
                continue;
            }

            $this->assertTrue($validator->fails(), $ruleSet['method'] . $validator->errors());
        }
    }

    /**
     * @dataProvider nonDictionaryWordsProvider
     */
    public function testPass($password)
    {
        $ruleSets = $this->getAvailableRuleSets();

        $username = 'a.teacher@test-correct.nl';

        foreach ($ruleSets as $ruleSet) {

            $data = array_filter(['username' => $username, 'password' => $password], function ($key) use ($ruleSet) {
                return in_array($key, $ruleSet['parameters']);
            },                   ARRAY_FILTER_USE_KEY);

            $rules = NistPasswordRules::{$ruleSet['method']}(...$data);

            $validator = \Validator::make([
                                              'username'              => $username,
                                              'password'              => $password,
                                              'password_confirmation' => $password
                                          ],
                                          ['password' => $rules]
            );

            $this->assertTrue($validator->passes(), $validator->errors());
        }

    }

    private function getAvailableRuleSets()
    {
        return [
            ['method' => 'register', 'parameters' => ['username']],
            ['method' => 'changePassword', 'parameters' => ['username', 'oldPassword']],
            ['method' => 'changePassword', 'parameters' => ['username']],
            ['method' => 'optionallyChangePassword', 'parameters' => ['username', 'oldPassword']],
            ['method' => 'optionallyChangePassword', 'parameters' => ['username']],
            ['method' => 'login', 'parameters' => []],
        ];
    }

    private function getPasswordRuleSets()
    {
        return [
            NistPasswordRules::register('username'),
            NistPasswordRules::changePassword('username', 'oldPassword'),
            NistPasswordRules::changePassword('username'),
            NistPasswordRules::optionallyChangePassword('username', 'oldPassword'),
            NistPasswordRules::optionallyChangePassword('username'),
            NistPasswordRules::login(),
        ];
    }

    public function testRuleTypes()
    {
        $passwordRuleSets = $this->getPasswordRuleSets();

        foreach ($passwordRuleSets as $passwordRules) {
            foreach ($passwordRules as $rule) {
                $validType = is_string($rule) || (is_object($rule) && $rule instanceof Rule);
                $this->assertTrue($validType, 'Invalid rule type.');
            }
        }
    }
}
