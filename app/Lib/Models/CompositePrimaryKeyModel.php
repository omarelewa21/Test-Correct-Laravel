<?php

namespace tcCore\Lib\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * Class CompositePrimaryKeyModel
 * @package tcCore\Lib\Models
 *
 *
 * This package was merged with https://github.com/mopo922/LaravelTreats/blob/master/src/Model/Traits/HasCompositePrimaryKey.php
 * This was necessary because pluck method on compositePrimaryKeyModels throws a missing attribute failure cause it uses the
 * method.
 *
 * I choose to leave the old methods inplace
 * I added missing method from the package. eq getIncrementing
 * I commented out the duplicates and the rest because I dont need them now and they can have negative site affects. But please look ath
 * them when you come across this comment and are experiencing problems with refresh find findOrFail on your CompositePrimaryKey models.
 *
 * made a ticket to resolve the issue later. The issue being:
 * This CompositePrimaryKeyModel problem has been solved in many packages. So if we stick with this design we should choose a
 * package that solves this problem well and include it als a package (not copy the code over)
 * Or remove the composit primary keys and and dedicated primarykeys on the models and refactor the Composite primaryKey model out of the project
 * which whould be my option of choice.
 */

abstract class CompositePrimaryKeyModel extends BaseModel {
    /**
     * Destroy the models for the given IDs.
     *
     * @param  array|int  $ids
     * @return int
     */
    public static function destroy($ids)
    {
        // We'll initialize a count here so we will return the total number of deletes
        // for the operation. The developers can then check this number as a boolean
        // type value or get this total count of records deleted for logging, etc.
        $count = 0;

        $ids = is_array($ids) ? $ids : func_get_args();

        $instance = new static;

        // We will actually pull the models from the database table and call delete on
        // each of them individually so that their events get fired properly with a
        // correct set of attributes in case the developers wants to check these.
        $key = $instance->getKeyName();
        if (is_array($key)) {
            $query = $instance->select();
            foreach($ids as $id) {
                $query->orWhere(function ($query) use($key, $id) {
                    foreach($key as $k) {
                        $query->where($k, $id[$k]);
                    }
                });
            }

            foreach ($query->get() as $model) {
                if ($model->delete()) $count++;
            }
        } else {
            foreach ($instance->whereIn($key, $ids)->get() as $model) {
                if ($model->delete()) $count++;
            }
        }

        return $count;
    }

    /**
     * Reload a fresh model instance from the database.
     *
     * @param  array  $with
     * @return $this
     */
    public function fresh($with = array())
    {
        $key = $this->getKeyName();

        if($this->exists) {
            if (is_array($key)) {
                $query = static::with($with);

                foreach($this->getKey() as $variable => $value) {
                    $query->where($variable, $value);
                }
                return $query->first();
            } else {
                return static::with($with)->where($key, $this->getKey())->first();
            }
        } else {
            return null;
        }
    }

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        $key = $this->getKeyName();
        if (is_array($key)) {
            $result = [];
            foreach($key as $k) {
                $result[$k] = $this->getAttribute($k);
            }
            return $result;
        } else {
            return $this->getAttribute($key);
        }
    }

    /**
     * Get the primary key value for a save query.
     *
     * @return mixed
     */
    protected function getKeyForSaveQuery()
    {
        $key = $this->getKeyName();
        if (is_array($key)) {
            $result = [];

            foreach($key as $k) {
                if (isset($this->original[$k])) {
                    $result[$k] = $this->original[$k];
                } else {
                    $result[$k] = $this->getAttribute($k);
                }
            }

            return $result;
        } else {
            if (isset($this->original[$key])) {
                return $this->original[$key];
            }

            return $this->getAttribute($key);
        }
    }

    /**
     * Insert the given attributes and set the ID on the model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $attributes
     * @return void
     */
    protected function insertAndSetId(Builder $query, $attributes)
    {
        $key = $this->getKeyName();
        if (is_array($key)) {
            $query->insert($attributes);
        } else {
            $id = $query->insertGetId($attributes, $keyName = $this->getKeyName());

            $this->setAttribute($keyName, $id);
        }
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function performDeleteOnModel()
    {
        $key = $this->getKeyName();
        if (is_array($key)) {
            $query = $this->newQuery();
            $ids = $this->getKey();
            foreach($ids as $key => $value) {
                $query->where($key, $value);
            }
            $query->delete();
        } else {
            $this->newQuery()->where($this->getKeyName(), $this->getKey())->delete();
        }
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        $key = $this->getKeyName();

        if (is_array($key)) {
            $ids = $this->getKey();
            foreach($ids as $key => $value) {
                $query->where($key, '=', $value);
            }
        } else {
            $query->where($key, '=', $this->getKeyForSaveQuery());
        }

        return $query;
    }

    /**
     * Clone the model into a new, non-existing instance.
     *
     * @param  array  $except
     * @return \Illuminate\Database\Eloquent\Model
     * @noinspection UnsupportedStringOffsetOperationsInspection
     */
    public function replicate(array $except = null)
    {
        $defaults = $this->getKeyName();
        if (!is_array($defaults)) {
            $defaults = [$defaults];
        }
        $defaults[] = $this->getCreatedAtColumn();
        $defaults[] = $this->getUpdatedAtColumn();

        $except = $except ?: $defaults;

        $attributes = Arr::except($this->attributes, $except);

        with($instance = new static)->setRawAttributes($attributes);

        return $instance->setRelations($this->relations);
    }

    // end original file;

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

//    /**
//     * Get the value of the model's primary key.
//     *
//     * @return mixed
//     */
//    public function getKey()
//    {
//        $attributes = [];
//
//        foreach ($this->getKeyName() as $key) {
//            $attributes[$key] = $this->getAttribute($key);
//        }
//
//        return $attributes;
//    }

//    /**
//     * Set the keys for a save update query.
//     *
//     * @param  \Illuminate\Database\Eloquent\Builder $query
//     * @return \Illuminate\Database\Eloquent\Builder
//     */
//    protected function setKeysForSaveQuery(Builder $query)
//    {
//        foreach ($this->getKeyName() as $key) {
//            if (isset($this->$key))
//                $query->where($key, '=', $this->$key);
//            else
//                throw new Exception(__METHOD__ . 'Missing part of the primary key: ' . $key);
//        }
//
//        return $query;
//    }

//    /**
//     * Execute a query for a single record by ID.
//     *
//     * @param  array  $ids Array of keys, like [column => value].
//     * @param  array  $columns
//     * @return mixed|static
//     */
//    public static function find($ids, $columns = ['*'])
//    {
//        $me = new self;
//        $query = $me->newQuery();
//
//        foreach ($me->getKeyName() as $key) {
//            $query->where($key, '=', $ids[$key]);
//        }
//
//        return $query->first($columns);
//    }

//    /**
//     * Find a model by its primary key or throw an exception.
//     *
//     * @param mixed $ids
//     * @param array $columns
//     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
//     *
//     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
//     */
//    public static function findOrFail($ids, $columns = ['*'])
//    {
//        $result = self::find($ids, $columns);
//
//        if (!is_null($result)) {
//            return $result;
//        }
//
//        throw (new ModelNotFoundException)->setModel(
//            __CLASS__, $ids
//        );
//    }

    /**
     * Reload the current model instance with fresh attributes from the database.
     *
     * @return $this
     */
    public function refresh()
    {
        if (!$this->exists) {
            return $this;
        }

        $this->setRawAttributes(
            static::findOrFail($this->getKey())->attributes
        );

        $this->load(collect($this->relations)->except('pivot')->keys()->toArray());

        return $this;
    }
}