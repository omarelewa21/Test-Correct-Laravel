<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;

class FeatureSetting extends Model
{
    protected $guarded = [];

    public function settingable()
    {
        $this->morphTo();
    }

    //todo create trait to handle feature settings
    // can() ... creathlon etc. or something like that

    public static function scopeGetSettings($query)
    {
        return $query->get(['title', 'value']);
    }

    /**
     * Set Setting for a model instance:
     * If setting doesn't exist, returns false.
     * So updating to false means removing record.
     */
    public function scopeSetSetting($query, string $title, $value)
    {
        $settingableValues = collect($query->getBindings())->mapWithKeys(function($value, $key) {
            return [class_exists($value) ? 'type' : 'id' => $value];
        });

        if (!$value) {
            return $query
                ->where('title', '=', $title)
                ->where('settingable_id', '=', $settingableValues['id'])
                ->where('settingable_type', '=', $settingableValues['type'])
                ->delete();
        }
        return $query
            ->updateOrCreate([
            'title' => $title,
            'settingable_id' => $settingableValues['id'],
            'settingable_type' => $settingableValues['type'],
        ], [
            'value' => $value,
        ]);
    }

    /**
     * Set Setting for a model instance:
     * If setting doesn't exist, returns false.
     * So updating to false means removing record.
     */
    public function scopeGetSetting($query, $title)
    {
        $settingableValues = collect($query->getBindings())->mapWithKeys(function($value, $key) {
            return [class_exists($value) ? 'type' : 'id' => $value];
        });

        return $query
            ->where([
            'title' => $title,
            'settingable_id' => $settingableValues['id'],
            'settingable_type' => $settingableValues['type'],
        ])->pluck('value')
            ->toArray();
    }
}
