<?php namespace tcCore\Lib\Models;


use Illuminate\Database\Eloquent\Builder;

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
    protected function setKeysForSaveQuery(Builder $query)
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

        $attributes = array_except($this->attributes, $except);

        with($instance = new static)->setRawAttributes($attributes);

        return $instance->setRelations($this->relations);
    }
}