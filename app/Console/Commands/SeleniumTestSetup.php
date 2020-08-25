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

        if ($state == 'true') {
            SeleniumTest::backupEnvFile();
        } else {
            SeleniumTest::restoreEnvFile();
        }

        return 0;
    }
}
