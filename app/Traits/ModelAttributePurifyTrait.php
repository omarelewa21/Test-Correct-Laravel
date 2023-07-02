<?php

namespace tcCore\Traits;

use Illuminate\Support\Arr;
use tcCore\Http\Requests\Request;
use tcCore\Casts\PurifyAttributeCast;

trait ModelAttributePurifyTrait
{
    protected $purifyIgnoreFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'uuid'];
    protected $customPurifyIgnoreFields = [];

    public static function booted()
    {
        static::retrieved(function ($model) {
            foreach($model->getAttributesToPurify() as $attribute) {
                $model->mergeCasts([
                    $attribute => PurifyAttributeCast::class,
                ]);
            }
        });

        static::creating(function ($model) {
            foreach ($model->getAttributesToPurify() as $attribute) {
                $model->purifyAttributeIfNeeded($attribute);
            }
        });
    }

    /**
     * purify model attribute when not in cast
     * 
     * @param string $attribute 
     */
    protected function purifyAttributeIfNeeded($attribute)
    {
        $value = $this->$attribute;
        Request::filter($value);
        $this->setAttribute($attribute, $value);
    }

    /**
     * get all model attributes except ignored fields and already casted fields
     * 
     * @return array
     */
    protected function getAttributesToPurify(): array
    {
        $ignoreFields = array_merge($this->purifyIgnoreFields, $this->customPurifyIgnoreFields, array_keys($this->casts));

        return array_keys(
            Arr::where($this->getAttributes(), fn($value, $key) => !in_array($key, $ignoreFields))
        );
    }
}