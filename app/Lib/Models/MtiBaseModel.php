<?php namespace tcCore\Lib\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassAssignmentException;

abstract class MtiBaseModel extends BaseModel
{
    public $mtiBaseClass;
    public $mtiClassField;
    public $mtiParentTable;
    public $parentInstance;

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        if ($this->useMti()) {
            if (get_class($this) != $this->mtiBaseClass) {
                $this->parentInstance = new $this->mtiBaseClass();
                $this->parentInstance->setAttribute($this->mtiClassField, $this->stripNamespaceFromClass());
            } else {
                $this->setAttribute($this->mtiClassField, $this->stripNamespaceFromClass());
            }
        }
    }

    public function useMti()
    {
        return ($this->mtiClassField && $this->mtiBaseClass);
    }

    public function newFromBuilder($attributes = array(), $parentInstance = null)
    {
        $instance = parent::newFromBuilder($attributes);
        $instance->parentInstance = $parentInstance;
        return $instance;
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new MtiBuilder($query);
    }

    /**
     * Create a collection of models from plain arrays.
     *
     * @param  array  $items
     * @param  string  $connection
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function hydrate(array $items, $connection = null)
    {
        $instance = new static();
        if ($instance->useMti()) {
            //Todo: Handle Mti version
        } else {
            $collection = parent::hydrate($items, $connection);
        }

        return $collection;
    }

    protected function stripNamespaceFromClass($class = null) {
        if ($class === null) {
            $class = $this;
        }

        $className = explode('\\',get_class($class));
        return end($className);
    }

    /**
     * Perform a model update operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $options
     * @return bool|null
     */
    protected function performUpdate(Builder $query, array $options = [])
    {
        if ($this->useMti()) {
            $instance = $this;
            $this->getConnection()->transaction(function() use ($instance, $query, $options) {
                $dirty = $instance->getDirty();

                if (count($dirty) > 0) {
                    // If the updating event returns false, we will cancel the update operation so
                    // developers can hook Validation systems into their models and cancel this
                    // operation if the model does not pass validation. Otherwise, we update.
                    if ($instance->fireModelEvent('updating') === false) {
                        return false;
                    }

                    // Because this model has inheritance, save the parent instance first
                    if ($instance->parentInstance !== null) {
                        $instance->syncAttributesWithParent();
                        $saved = $instance->parentInstance->save();
                    }

                    // If it failed to save throw an expection, so the transaction is rolled back.
                    if ($instance->parentInstance !== null && !$saved) {
                        throw new \Exception('Parent failed to save');
                    } elseif($instance->parentInstance !== null) {
                        $instance->setAttribute($instance->getKeyName(), $instance->parentInstance->getKey());
                    }

                    // First we need to create a fresh query instance and touch the creation and
                    // update timestamp on the model which are maintained by us for developer
                    // convenience. Then we will just continue saving the model instances.
                    if ($instance->timestamps && array_get($options, 'timestamps', true)) {
                        $instance->updateTimestamps();
                    }

                    // Once we have run the update operation, we will fire the "updated" event for
                    // this model instance. This will allow developers to hook into these after
                    // models are updated, giving them a chance to do any special processing.
                    $dirty = $instance->getDirty();

                    if (count($dirty) > 0) {
                        $instance->setKeysForSaveQuery($query)->update($dirty);

                        $instance->fireModelEvent('updated', false);
                    }
                }
            });

            return true;
        } else {
            return parent::performUpdate($query, $options);
        }
    }

    public function syncAttributesWithParent() {
        foreach($this->attributes as $key => $value) {
            if (array_key_exists($key, $this->parentInstance->attributes)) {
                $this->parentInstance->$key = $value;
            }
        }
    }

    /**
     * Perform a model insert operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $options
     * @return bool
     */
    protected function performInsert(Builder $query, array $options = [])
    {
        if ($this->useMti()) {
            $instance = $this;
            $this->getConnection()->transaction(function() use ($instance, $query, $options) {
                if ($instance->fireModelEvent('creating') === false) return false;

                // Because this model has inheritance, save the parent instance first
                if ($instance->parentInstance !== null) {
                    $instance->syncAttributesWithParent();
                    $saved = $this->parentInstance->save();
                }

                // If it failed to save throw an expection, so the transaction is rolled back.
                if ($instance->parentInstance !== null && !$saved) {
                    throw new \Exception('Parent failed to save');
                }

                // First we'll need to create a fresh query instance and touch the creation and
                // update timestamps on this model, which are maintained by us for developer
                // convenience. After, we will just continue saving these model instances.
                if ($instance->timestamps && array_get($options, 'timestamps', true)) {
                    $instance->updateTimestamps();
                }

                // If the model has an incrementing key, we can use the "insertGetId" method on
                // the query builder, which will give us back the final inserted ID for this
                // table from the database. Not all tables have to be incrementing though.
                if ($instance->incrementing && $instance->parentInstance === null) {
                    $attributes = $this->attributes;
                    $instance->insertAndSetId($query, $attributes);
                }

                // If the table is not incrementing we'll simply insert this attributes as they
                // are, as this attributes arrays must contain an "id" column already placed
                // there by the developer as the manually determined key for these models.
                else {
                    $instance->setAttribute($this->getKeyName(), $instance->parentInstance->getKey());
                    $attributes = $this->attributes;
                    $query->insert($attributes);
                }


                // We will go ahead and set the exists property to true, so that it is set when
                // the created event is fired, just in case the developer tries to update it
                // during the event. This will allow them to do so and run an update here.
                $instance->exists = true;

                $instance->fireModelEvent('created', false);
            });

            return true;
        } else {
            return parent::performInsert($query, $options);
        }
    }

    /**
     * Delete the model from the database.
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete()
    {
        if ($this->useMti()) {
            $instance = $this;
            try {
                $this->getConnection()->transaction(function () use ($instance) {
                    if (is_null($instance->primaryKey))
                    {
                        throw new \Exception("No primary key defined on model.");
                    }

                    if ($instance->exists)
                    {
                        if ($instance->fireModelEvent('deleting') === false) return false;

                        // Here, we'll touch the owning models, verifying these timestamps get updated
                        // for the models. This will allow any caching to get broken on the parents
                        // by the timestamp. Then we will go ahead and delete the model instance.
                        $instance->touchOwners();

                        $instance->performDeleteOnModel();

                        if ($instance->parentInstance !== null) {
                            $deleted = $instance->parentInstance->delete();
                        }

                        if ($instance->parentInstance !== null && !$deleted) {
                            throw new \Exception('Parent failed to delete');
                        }


                        $instance->exists = false;

                        // Once the model has been deleted, we will fire off the deleted event so that
                        // the developers may hook into post-delete operations. We will then return
                        // a boolean true as the delete is presumably successful on the database.
                        $instance->fireModelEvent('deleted', false);

                        return true;
                    }
                });
            } catch (\Exception $e) {
                throw $e;
            }
            return true;
        } else {
            return parent::delete();
        }
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function performDeleteOnModel()
    {
        $query = parent::newQuery();
        $query->where($this->getKeyName(), $this->getKey())->delete();
    }

    public function getParent() {
        return $this->parentInstance;
    }

    public function toArray() {
        if ($this->parentInstance !== null) {
            $attributes = $this->parentInstance->toArray();
        } else {
            $attributes = array();
        }
        return array_merge($attributes, parent::toArray());
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (!array_key_exists($key, $this->attributes) && !$this->hasGetMutator($key) && !array_key_exists($key, $this->relations) && !method_exists($this, $key) && $this->parentInstance !== null) {
            return $this->parentInstance->getAttribute($key);
        } else {
            return parent::getAttribute($key);
        }
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if ($this->parentInstance !== null) {
            $this->parentInstance->fill($attributes);
        }

        return $this;
    }
}