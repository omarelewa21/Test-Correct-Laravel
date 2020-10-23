<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 26/03/16
 * Time: 21:12
 */

/**
 * This trait adds two columns and three attributes to your model;
 */

namespace tcCore\Traits;


use tcCore\Lib\Models\BaseModel;
use tcCore\Scopes\ArchivedScope;
use Illuminate\Support\Facades\Auth;
use tcCore\ArchivedModel;
use tcCore\User;

trait Archivable
{
    public function initializeArchivable()
    {
        $this->append('archived');

        if ($this instanceof BaseModel) {
            $this->exceptCloneModelOnly = array_merge(
                $this->exceptCloneModelOnly,
                [
                    'archivable_model_id',
                    'archivable_user_id',
                ]
            );
        }
    }

    public function getArchivedAttribute()
    {
        return (null !== $this->attributes['archivable_model_id']);
    }

    public function scopeWithoutArchived($query)
    {
        return $query->whereNull('archivable_model_id');
    }

    public function scopeArchived($query){
        return $query->whereNotNull('archivable_model_id');
    }

    public static function bootArchivable()
    {
        static::addGlobalScope(new ArchivedScope);
    }

    public function archiveForUser(User $user)
    {
        return ArchivedModel::archiveWithModelAndUser($this, $user);
    }

    public function unArchiveForUser(User $user)
    {
        return ArchivedModel::unarchiveWithModelAndUser($this, $user);
    }
}
