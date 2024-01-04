<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use tcCore\Http\Enums\SchoolLocationFeatureSetting;
use tcCore\SchoolLocation;

class AddFeatureSettingToSchoolLocationCommand extends Command
{
    protected $signature = 'sl:add-feature-settings
                            {schoolLocationId=1 : The school location to add the settings to}
                            {setting=all : Specify a setting, or leave blank to add all currently available}';

    protected $description = 'Add feature settings the given schoolLocation in the local database';

    public function handle(): bool
    {
        if (!in_array(config('app.env'), ['local', 'testing'])) {
            $this->error('Command unavailable when not on a local machine.');
            return false;
        }

        $schoolLocation = SchoolLocation::find($this->argument('schoolLocationId'));
        if (!$schoolLocation) {
            $this->error('No school location found with the specified ID: ' . $this->argument('schoolLocationId'));
            return false;
        }

        if (!$this->confirm('Do you want to add the settings to: "' . $schoolLocation->name . '"?', true)) {
            return false;
        }

        $startTally = $schoolLocation->featureSettings()->count();

        $this->addSettingsToSchoolLocation($schoolLocation);

        $endTally = $schoolLocation->featureSettings()->count();
        $updatedSettings = $endTally - $startTally;

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
            $this->settings()->each(function (SchoolLocationFeatureSetting $enum) use ($schoolLocation) {
                $title = $enum->value;
                $schoolLocation->$title = true;
            });
            return;
        }

        $setting = $this->argument('setting');
        if (SchoolLocationFeatureSetting::tryFrom($setting)) {
            $schoolLocation->$setting = true;
            return;
        }

        $this->error("Setting: $setting is not a valid value.");
    }

    private function settings(): Collection
    {
        return collect(SchoolLocationFeatureSetting::cases())->filter(fn($enum) => $enum !== SchoolLocationFeatureSetting::TEST_PACKAGE);
    }
}
