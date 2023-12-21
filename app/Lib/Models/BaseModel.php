<?php namespace tcCore\Lib\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use tcCore\Lib\User\Roles;

abstract class BaseModel extends Model
{
    protected $exceptCloneModelOnly = [];

    public function hasAttribute($attr)
    {
        return array_key_exists($attr, $this->attributes);
    }

    /**
     * Save model without triggering observers on model
     */
    public function saveQuietly(array $options = [])
    {
        return static::withoutEvents(function () use ($options) {
            return $this->save($options);
        });
    }

    public function cloneModelOnly(array $except = [])
    {
        $except = array_merge($except, $this->exceptCloneModelOnly);

        $defaults = [
            $this->getKeyName(),
            'uuid',
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
        ];

        $attributes = Arr::except(
            $this->attributes, array_unique(array_merge($except, $defaults))
        );

        $instance = new static;
        $instance->setRawAttributes($attributes);

        return $instance;
    }

    protected function getUserRoles()
    {
        return Roles::getUserRoles();
    }

    /**
     * Syncs many-to-many relations within Test-Correct (soft-delete relations!)
     * @param $entities array Array with current entities
     * @param $wantedEntities array Wanted values in attribute in entities
     * @param $attribute string Attribute to compare
     * @param $create callable Function called to create a link
     */
    protected function syncTcRelation($entities, $wantedEntities, $attribute, $create)
    {
        // Convert to collections and filter out soft-deleted entities
        $entityCollection = collect($entities);
        $wantedCollection = collect($wantedEntities)->unique()->reject('');

        // Find entities to be deleted
        $entitiesToDelete = $entityCollection->withoutTrashed()
            ->reject(function ($entity) use ($attribute, $wantedCollection) {
                return $wantedCollection->contains($entity->$attribute);
            });

        // Delete entities
        $entitiesToDelete->each(function ($entity) {
            $entity->delete();
        });

        // Find entities to be restored
        $entitiesToRestore = $entityCollection->onlyTrashed()
            ->whereIn($attribute, $wantedCollection);

        // Restore entities
        $entitiesToRestore->each(function ($entity) {
            $entity->restore();
        });

        // Find entities to be created
        $entitiesToCreate = $wantedCollection->diff($entityCollection->pluck($attribute));

        // Create entities
        $entitiesToCreate->each(function ($entity) use ($create) {
            $create($this, $entity);
        });
    }

    public static function getPossibleEnumValues($column)
    {
        //Get the enum column from the DB with the type;
        $type = DB::select(
            sprintf('SHOW COLUMNS FROM %s WHERE Field = "%s"', static::getTableName(), $column)
        )[0]->Type;

        //Strip the enum word + ()'s
        preg_match('/^enum\((.*)\)$/', $type, $matches);

        //Add the values to an array to return
        $enum = array();
        foreach (explode(',', $matches[1]) as $value) {
            $v = trim($value, "'");
            $enum[] = $v;
        }
        return $enum;
    }

    public static function getTableName(): string
    {
        return (new static())->getTable();
    }
}
