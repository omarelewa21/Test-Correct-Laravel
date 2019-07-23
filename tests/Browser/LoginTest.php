<?php

namespace Tests\Browser;

use Facebook\WebDriver\WebDriverKeys;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LoginTest extends DuskTestCase
{
//    use DatabaseTransactions;

    /** @test */
    public function a_logged_on_user_can_change_his_password_and_login_with_his_new_password()
    {
        $this->browse(function (Browser $browser) {

            $oldPassword = 'p.vries@31.com';
            $newPassword = '123abcefg';

            $browser->visit('http://testportal.test-correct.test');
            $this->loginTest($browser, $oldPassword, $oldPassword);

            $browser->waitForText('Itembank')
                ->assertSee('Itembank')
                ->pause(2000)
                ->click('#user')
                ->pause(3000)
                ->click('#btnChangePassword')
                ->pause(3000)
                ->type('#UserPasswordOld', $oldPassword)
                ->type('#UserPassword', $newPassword)
                ->type('#UserPasswordNew', $newPassword)
                ->click('#btnSavePassword')
                ->waitForText('Wachtwoord gewijzigd')
                ->assertSee('Wachtwoord gewijzigd')
                ->pause(3000);

            $this->logout($browser);
            $this->login($browser, 'p.vries@31.com', $newPassword);

            $browser->waitForText('Itembank')
                ->assertSee('Itembank')
                ->pause(2000)
                ->click('#user')
                ->pause(3000)
                ->click('#btnChangePassword')
                ->pause(3000)
                ->type('#UserPasswordOld', $newPassword)
                ->type('#UserPassword', $oldPassword)
                ->type('#UserPasswordNew', $oldPassword)
                ->click('#btnSavePassword')
                ->waitForText('Wachtwoord gewijzigd')
                ->assertSee('Wachtwoord gewijzigd')
                ->pause(3000)
                ->click('#user')
                ->click('#btnLogout')
                ->waitForText('Inloggen op Test-Correct', 50)
                ->type('#UserEmail', 'p.vries@31.com')
                ->type('#UserPassword', $oldPassword);
        });
    }

    /** @test */
    public function bert_can_testlogin_this_is_login_without_app_restriction()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('http://testportal.test-correct.test');
            $this->loginTest($browser, 'info+bert@test-correct.nl', 'p.vries@31.com');
            $browser->assertSee('Welkom in Test-Correct');
        });
    }



//    public function testToetsing()
//    {
//        $this->browse(function (Browser $browser) {
//
//            $browser->visit('https://testportal.test-correct.nl/')
////            $browser->visit('http://portal.test-correct.test')
//                ->waitForText('Inloggen op Test-Correct', 50)
//                ->type('#UserEmail', 'p.vries@31.com')
//                ->type('#UserPassword', 'p.vries@31.com')
//                ->click('a[class="btn mt5 mr5 blue pull-right btnLogin"]')
//                // Hier ben je ingelogged.
//                ->waitForText('Itembank'
//                )
//                ->assertSee('Itembank')
//                ->mouseover('#library')
//                ->pause(3000)
//                ->waitFor('#tests_overview')
//                ->pause(2000)
//                ->click('#tests_overview')
//                ->waitForText('Toetsen', 15)
//                ->waitForText('Nederlands', 50)
//                ->click('span[class="fa fa-folder-open-o"]')
//                ->pause(20000);
//
//        });
//    }
//
//    public function testVragenFilter()
//    {
//        $this->browse(function (Browser $browser) {
//
////            $browser->visit('https://testportal.test-correct.nl/')
//            $browser->visit('http://portal.test-correct.test')
//                ->waitForText('Inloggen op Test-Correct', 50)
//                ->type('#UserEmail', 'p.vries@31.com')
//                ->type('#UserPassword', 'p.vries@31.com')
//                ->click('a[class="btn mt5 mr5 blue pull-right btnLogin"]')
//                // Hier ben je ingelogged.
//                ->waitForText('Itembank'
//                )
//                ->assertSee('Itembank')
//                ->mouseover('#library')
//                ->pause(2000)
//                ->waitFor('#questions_overview')
//                ->pause(2000)
//                ->click('#questions_overview')
//                ->moveMouse(0, 250)
//                ->pause(2000)
//                ->waitForText('ilter', 15)
//                ->click('#filters')
//                ->waitForText('Termen', 50)
////                ->click('input[class="select2-search__field"]')
////                ->pause(20000)
//                // Hier loop ik vast omdat het veld waar ik in wil typen geen naam heeft
//                ->click('input[class="select2-search__field"]')
//                ->keys('input[class="select2-search__field"]', ['vraag'])
//                ->pause(3000)
//                //->click('input[class="select2-search__field"]')
//                ->click('li[class="select2-results__option select2-results__option--highlighted"]')
////                ->script("var e = $.Event( 'keypress', { which: 13 } );$('body:first').trigger(e);")
//                ->pause(5000)
//                // ->waitForText( 'Sluiten')
//                // ->click( 'input[class="select2-search__field"]')
//                ->pause(20000);
//
//
//        });
//    }


}
