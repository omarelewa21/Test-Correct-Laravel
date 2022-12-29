<?php

namespace tcCore\Traits;

use tcCore\FeatureSetting;

trait FeatureSettings
{
    public function featureSettings()
    {
        return $this->morphMany(FeatureSetting::class, 'settingable');
    }

    public function getFeatureSettingsAttribute()
    {
        return $this->featureSettings()->getSettings()->mapWithKeys(function($item) {
            return [$item->title => $item->value];
        });
    }
}