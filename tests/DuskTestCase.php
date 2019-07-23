<?php

namespace Tests;

use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
//            '--disable-gpu',
//            '--headless',
            '--window-size=1920,1080',
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
            ChromeOptions::CAPABILITY, $options
            )
        );
    }

    protected function logout(Browser $browser)
    {
        $browser->click('#user')
            ->click('#btnLogout')
            ->waitForText('Inloggen op Test-Correct', 50);
    }

    protected function loginTest(Browser $browser, $login, $password)
    {
        $browser->waitForText('Inloggen op Test-Correct', 50)
            ->type('#UserEmail', $login)
            ->type('#UserPassword', $password)
            ->click('.btnLoginTest')
            // Hier ben je ingelogged.
            ->pause(2000);

        return $browser;
    }

    protected function login(Browser $browser, $login, $password)
    {
        $browser->waitForText('Inloggen op Test-Correct', 50)
            ->type('#UserEmail', $login)
            ->type('#UserPassword', $password)
            ->click('a[class="btn mt5 mr5 blue pull-right btnLogin"]')
            // Hier ben je ingelogged.
            ->pause(2000);
    }
}
