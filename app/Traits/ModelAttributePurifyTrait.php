<?php

namespace tcCore\Traits;

use Illuminate\Support\Arr;
use tcCore\Http\Requests\Request;
use tcCore\Casts\PurifyAttributeCast;

/**
 * Trait ModelAttributePurifyTrait - Purify model attributes before storing them in database and decode them when retrieving.
 * 
 * To add custom ignore fields, add a customPurifyIgnoreFields array in your model.
 * 
 * To add casted fields, add a fieldsToDecodeOnRetrieval array in your model.
 */
trait ModelAttributePurifyTrait
{
    /**
     * default fields to ignore when purifying attributes
     * @var array
     */
    protected $purifyIgnoreFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'uuid'];


    public static function booted()
    {
        static::retrieved(function ($model) {
            if(!isset($model->fieldsToDecodeOnRetrieval)) return;

            foreach($model->fieldsToDecodeOnRetrieval as $attribute) {
                $model->mergeCasts([
                    $attribute => PurifyAttributeCast::class,
                ]);
            }
        });

        static::creating(function ($model) {
            foreach ($model->getAttributesToPurifyBeforeStoring() as $attribute) {
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
    protected function getAttributesToPurifyBeforeStoring(): array
    {
        $ignoreFields = array_merge($this->purifyIgnoreFields, array_keys($this->casts));
        if(isset($this->customPurifyIgnoreFields)) {
            $ignoreFields = array_merge($ignoreFields, $this->customPurifyIgnoreFields);
        }

        return array_keys(
            Arr::where($this->getAttributes(), fn($value, $key) => !in_array($key, $ignoreFields))
        );
    }
}