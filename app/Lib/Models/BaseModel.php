<?php namespace tcCore\Lib\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use tcCore\Lib\User\Roles;

abstract class BaseModel extends Model {

    protected $exceptCloneModelOnly = [];

    public function hasAttribute($attr)
    {
        return array_key_exists($attr, $this->attributes);
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

    protected function getUserRoles() {
        return Roles::getUserRoles();
    }

    /**
     * Syncs many-to-many relations within Test-Correct (soft-delete relations!)
     * @param $entities array Array with current entities
     * @param $wantedEntities array Wanted values in attribute in entities
     * @param $attribute string Attribute to compare
     * @param $create callable Function called to create a link
     */
    protected function syncTcRelation($entities, $wantedEntities, $attribute, $create) {
        $deletedEntities = [];

        if (!is_array($wantedEntities)) {
            $wantedEntities = [$wantedEntities];
        }
        $wantedEntities = array_filter(array_unique($wantedEntities));

        foreach($entities as $key => $entity) {
            if ($entity->getAttribute('deleted_at') !== null) {
                $deletedEntities[] = $entity;
                unset($entities[$key]);
            }
        }

        foreach($entities as $entity) {
            if (!in_array($entity->getAttribute($attribute), $wantedEntities)) {
                $entity->delete();
            } elseif(($key = array_search($entity->getAttribute($attribute), $wantedEntities)) !== false) {
                unset($wantedEntities[$key]);
            }
        }

        foreach($deletedEntities as $entity) {
            if (in_array($entity->getAttribute($attribute), $wantedEntities)) {
                $entity->setAttribute('deleted_at', null);
                $entity->save();

                if(($key = array_search($entity->getAttribute($attribute), $wantedEntities)) !== false) {
                    unset($wantedEntities[$key]);
                }
            }
        }

        foreach($wantedEntities as $entity) {
            $create($this, $entity);
        }
    }
}
