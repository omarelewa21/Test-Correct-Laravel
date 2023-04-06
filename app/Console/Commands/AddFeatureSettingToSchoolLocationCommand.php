<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use tcCore\SchoolLocation;

class AddFeatureSettingToSchoolLocationCommand extends Command
{
    protected $signature = 'sl:add-feature-settings 
                            {schooLocationId=1 : The school location to add the settings to}
                            {setting=all : Specify a setting, or leave blank to add all currently available}';

    protected $description = 'Add feature settings the given schoolLocation in the local database';

    private $settingTitles = [
        'allow_creathlon',
        'allow_olympiade',
        'allow_new_taken_tests_page',
        'allow_analyses',
        'allow_new_co_learning',
        'allow_new_co_learning_teacher',
        'allow_new_assessment',
        'allow_new_reviewing',
    ];

    public function handle()
    {
        if (!in_array(config('app.env'), ['local', 'testing'])) {
            $this->error('Command unavailable when not on a local machine.');
            return false;
        }

        $schoolLocation = SchoolLocation::find($this->argument('schooLocationId'));
        if (!$schoolLocation) {
            $this->error('No school location found with the specified ID: ' . $this->argument('schooLocationId'));
            return false;
        }

        if (!$this->confirm('Do you want to add the settings to: "' . $schoolLocation->name . '"?', true)) {
            return false;
        }

        $startTally = $schoolLocation->featureSettings()->count();

        $this->addSettingsToSchoolLocation($schoolLocation);

        $endTally = $schoolLocation->featureSettings()->count();
        $updatedSettings = (int)$endTally - $startTally;

        $this->info("$updatedSettings Feature setting(s) added");
        return true;
    }

    /**
     * @param $schoolLocation
     * @return void
     */
    private function addSettingsToSchoolLocation(SchoolLocation $schoolLocation): void
    {
        if ($this->argument('setting') === 'all') {
            collect($this->settingTitles)->each(function ($title) use ($schoolLocation) {
                $schoolLocation->$title = true;
            });
        } else {
            $setting = $this->argument('setting');
            if (collect($this->settingTitles)->contains($setting)) {
                $schoolLocation->$setting = true;
            } else {
                $this->error("Setting: $setting is not a valid value.");
            }
        }
    }
}