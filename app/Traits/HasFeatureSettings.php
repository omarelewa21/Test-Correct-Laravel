<?php

namespace tcCore\Traits;

use Illuminate\Support\Str;
use tcCore\FeatureSetting;

trait HasFeatureSettings
{
    public function featureSettings()
    {
        return $this->morphMany(FeatureSetting::class, 'settingable');
    }

    public function getFeatureSettingsAttribute()
    {
        return $this->featureSettings()->getSettings()->mapWithKeys(function ($item) {
            return [$item->title => $item->value];
        });
    }

    public function __set($key, $value)
    {
        if (!$enum = $this->getEnumByKey($key)) {
            return parent::__set($key, $value);
        }
        $value = $enum->validateValue($value);

        $this->featureSettings()->setSetting($enum->value, $value);
    }

    public function __get($key)
    {
        if (!$enum = $this->getEnumByKey($key)) {
            return parent::__get($key);
        }

        $cast = $enum->castValue($this->featureSettings()->getSetting($enum->value)->pluck('value')->first());

        return ($cast || $cast === false) ? $cast : $this->featureSettings()->getSetting($enum->value)->exists();
    }

    public function __isset($key)
    {
        if ($this->getEnumByKey($key)) {
            return true;
        }

        return parent::__isset($key);
    }

    public function getEnumByKey($key)
    {
        if (enum_exists(static::FEATURE_SETTING_ENUM)) {
            return static::FEATURE_SETTING_ENUM::tryFrom(Str::snake($key));
        }
        return false;
    }

    public function getFeatureSettingEnumName(): string
    {
        return static::FEATURE_SETTING_ENUM;
    }

    public function fillFeatureSettings(array &$attributes)
    {
        foreach($attributes as $attribute => $value) {
            if ($this->getEnumByKey($attribute)) {
                $this->$attribute = $value;
                unset($attributes[$attribute]);
            }
        }
    }
}