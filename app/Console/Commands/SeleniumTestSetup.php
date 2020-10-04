<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use tcCore\Commands\SeleniumTest;

class SeleniumTestSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seleniumtest:toggle {toggle}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable / disable Selenium test state';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $state = $this->argument('toggle');

        $result = true;

        if ($state == 'true') {
            $result = SeleniumTest::applySeleniumEnvFile();
        } else {
            $result = SeleniumTest::restoreEnvFile();
        }

        //handle errors
        if (!$result) {
            if (env('SELENIUM_TEST', false) == true && $state == 'true') {
                $this->info('Selenium is already toggled to true');
            } else if (env('SELENIUM_TEST', false) == false && $state == 'false') {
                $this->info('Selenium is already toggled to false');
            } else {
                $this->error('Toggle failed! Does ' . SeleniumTest::seleniumEnvFile . ' exist? And are read/write permissions set correctly?');
            }
        }

        return 0;
    }
}
