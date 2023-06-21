<?php

namespace tcCore\Traits;
use tcCore\Http\Requests\Request;
use tcCore\Casts\PurifyAttributeCast;

trait ModelAttributePurifyTrait
{
    protected $purifyAttributes = [];

    public static function booted()
    {
        static::retrieved(function ($model) {
            foreach ($model->purifyAttributes as $attribute) {
                $model->mergeCasts([
                    $attribute => PurifyAttributeCast::class,
                ]);
            }
        });

        static::creating(function ($model) {
            foreach ($model->purifyAttributes as $attribute) {
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
        if(in_array($attribute, array_keys($this->casts))) return;  // already in cast

        $value = $this->$attribute;
        Request::filter($value);
        $this->setAttribute($attribute, $value);
    }
}