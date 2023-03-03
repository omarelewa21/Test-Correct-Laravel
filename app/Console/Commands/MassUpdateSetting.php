<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use tcCore\FeatureSetting;
use tcCore\SchoolLocation;

class MassUpdateSetting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schoolLocation:massUpdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update a setting for all school locations';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        if(!$setting = $this->ask('Which setting do you want to update?')){
            $this->error('please let us know which setting you want to update');
            return Command::FAILURE;
        }

        $type = $this->choice('What type of setting is it?',['feature','column']);
        if(!$this->checkSetting($type,$setting)){
            return Command::FAILURE;
        }

        if(!$value = $this->ask('What value should the setting be set to?')){
            $this->error('Please set a value for the setting');
        }
        if(!$this->checkSettingValue($type, $setting, $value)){
            return Command::FAILURE;
        }

        if(!$this->confirm(sprintf('Are you sure you want to update the setting `%s` and set it to `%s`',$setting,$value))){
            return Command::FAILURE;
        }

        $this->updateSetting($type,$setting,$value);

        return Command::SUCCESS;
    }

    protected function updateSetting($type,$setting,$value)
    {
        if($type === 'feature'){
            SchoolLocation::all()->each(function(SchoolLocation $sl) use ($setting,$value){
                $sl->featureSettings()->setSetting($setting,$value);
            });
        } else {
            SchoolLocation::where($setting,'<>',$value)->update([$setting => $value]);
        }
        $this->info(sprintf('The setting `%s` has been set to `%s` for all school locations',$setting,$value));
        $this->alert('REMARK: there still needs to ben an update of the code in order to make it a default value');
    }

    protected function checkSettingValue($type, $setting, $value)
    {
        $errorMessage = null;
        if($type === 'feature'){
            if(!$fs = FeatureSetting::where('title',$setting)->where('value',$value)->first()){
                $this->error(sprintf('The feature setting `%s` with value `%s`could not be found for any record, that doesn`t seem to be right',$setting, $value));
                return false;
            }
        } else {
            if(!SchoolLocation::where($setting,$value)->exists()){
                $this->error(sprintf('No School Location could be found with setting `%s` and value `%s`, that doesn`t seem right',$setting, $value));
                return false;
            }
        }
        return true;
    }

    protected function checkSetting($type, $setting)
    {
        $errorMessage = null;
        if($type === 'feature'){
            if(!$fs = FeatureSetting::where('title',$setting)->first()){
                $this->error(sprintf('The feature setting `%s` could not be found for any record, that doesn`t seem to be right',$setting));
                return false;
            }
            if(!$fs->settingable instanceof SchoolLocation){
                $this->error(sprintf('the feature setting `%s` does not seem to be of the school location model, `%s` model found',$setting, get_class($fs->settingable)));
                return false;
            }
        }
        elseif($type === 'column'){
            if(!Schema::hasColumn('school_locations', $setting)){
                $this->error(sprintf('The column `%s`, could not be found in the school_locations table', $setting));
                return false;
            }
        } else {
            $this->error(sprintf('The type of setting is unknown, needs to be feature or column, `%s` given', $type));
            return false;
        }
        return true;
    }
}
