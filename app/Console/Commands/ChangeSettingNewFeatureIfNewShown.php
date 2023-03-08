<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use tcCore\Info;
use tcCore\UserSystemSetting;

class ChangeSettingNewFeatureIfNewShown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SettingSetNewFeature:change';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'changes the setting for new features';

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
        $infoNewToday = info::getIfFeatureNewToday();

        if (!empty($infoNewToday)) {
            UserSystemSetting::setSettingForAllUsers('newFeaturesSeen', false);
            UserSystemSetting::setSettingForAllUsers('closedNewFeaturesMessage', false);
        }

    }
}
