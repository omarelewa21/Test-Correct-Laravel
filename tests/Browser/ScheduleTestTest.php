<?php

namespace Tests\Browser;

use Carbon\Carbon;
use Facebook\WebDriver\WebDriverKeys;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ScheduleTestTest extends DuskTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_teacher_can_schedule_a_test()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('http://testportal.test-correct.test');

            $this->loginTest($browser, 'info+bert@test-correct.nl', 'p.vries@31.com')
                ->assertDontSee('Demo GLR')
                ->pause(3000);

            $this->logout($browser);

            $this->scheduleTest($browser, 'krs@fioretti.nl', 'Number1!');

            $this->loginTest($browser, 'info+bert@test-correct.nl', 'p.vries@31.com')
                ->assertSee('Demo GLR');

        });
    }


    private function scheduleTest(Browser $browser, $login, $password)
    {
        $this->login($browser, $login, $password);

        $browser->mouseover('#tests')
            ->pause(3000)
            ->click('#tests_planned')
            ->waitForText('Geplande toetsen')
            ->pause(3000)
            ->mouseover('#testsTable')
            ->pause(3500)
            ->click('.mr2:nth-child(1)')
            ->pause(4000)
            ->type('#TestTakeDate0', Carbon::now()->format('d-m-Y'))
            ->type('#TestTakeWeight_0', '1')
            ->click('#TestTakeSelect_0')
            ->pause(6000)
            ->click('tr:nth-child(1) .fa-plus')
            ->pause(2000)
            ->click('#btnAddTestTakes')
            ->pause(3000)
            ->assertSee('Demo GLR [DEMO]')
            ->assertSee(Carbon::now()->format('d-m-Y'));

        $this->logout($browser);
    }


}
