<?php

namespace tcCore\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use tcCore\Http\Requests\Request;

class PurifyAttributeCast implements CastsAttributes
{
    /**
     * @param bool $getter - if true, the getter will be applied
     * @param bool $setter - if true, the setter will be applied
     */
    function __construct(private $getter = true, private $setter = true)
    {
    }

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return $this->getter ? html_entity_decode($value) : $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if($this->setter) Request::filter($value);
        return $value;
    }
}
