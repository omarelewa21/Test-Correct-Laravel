<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class DevPullAndDeploy extends Command
{
    use CommandsHelperTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:pullAndDeploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull from git and refresh config, routes, views and reinit queue';

    protected $defaultDeployBranchName = 'master';

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
     * @return mixed
     */
    public function handle()
    {

        if (config('app.env') === 'production') {
            $this->error("You cannot perform this action on the production environment! Use 'php artisan production:pullAndDeploy' instead");
            return 1;
        }

        $currentBranch = exec('git branch | grep \* | cut -d \' \' -f2');
        if ($currentBranch != $this->defaultDeployBranchName) {
            if (!$this->confirm("Are you sure you want to pull from the $currentBranch branch" . PHP_EOL . " this is NOT the `$this->defaultDeployBranchName` branch?", false)) {
                $this->error('stop due to your call NOT to pull from  the `' . $currentBranch . '` branch`');
                return 1;
            }
        }

        $this->info('Going to pull the latest info from git');
        if (!exec('git pull')) {
            $this->error('I\'m sorry, but we couldn\'t pull the latest data from git, please fix this first');
            return 1;
        };
        $this->info('done');

        $this->info(PHP_EOL);

        if ($this->confirm('Do you wan\'t to do a forced migration if needed?')) {
            $this->addMigrations();
        }

        if ($this->confirm('Do you wan\'t to do a composer install if needed?')) {
            if ($this->confirm('with dev [yes] without dev [no/default]?')) {
                $this->composerInstallWithDev();
            } else {
                $this->composerInstall();
            }
        }


        $this->info('Start caching');
        $this->printSubItem('cache clear');
        Artisan::call('cache:clear');
        $this->info('done');
        foreach (['route', 'config', 'view'] as $type) {
            $this->printSubItem($type . ' cache clear');
            Artisan::call(sprintf('%s:clear', $type));
            // do not perform route:cache, config:cache and view:cache on dev.
//            Artisan::call(sprintf('%s:cache',$type));
            $this->info('done');
        };
        $this->info('caching complete');

        $this->info(PHP_EOL);

        // queue runners dont need to restart because dev should be sync.
//        $this->info('Restarting queue');
//        Artisan::call('queue:restart');
        $this->info('done');

    }

    protected function composerInstallWithDev()
    {
        $this->info('going to do a composer install');
        if (!exec('php composer.phar install --optimize-autoloader')) {
            $this->error('an error occured while doing a composer install, please take care manually');
        }
        $this->info('done');
        $this->info(PHP_EOL);
    }

    protected function composerInstall()
    {
        $this->info('going to do a composer install');
        if (!exec('php composer.phar install --optimize-autoloader --no-dev')) {
            $this->error('an error occured while doing a composer install, please take care manually');
        }
        $this->info('done');
        $this->info(PHP_EOL);
    }
}
