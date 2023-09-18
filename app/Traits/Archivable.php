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
        if($this->shouldNotAppend()) {
            return null;
        }

        if (array_key_exists('archivable_model_id', $this->attributes)) {
            return (null !== $this->attributes['archivable_model_id']);
        }
        
        return false;
    }

    public function scopeFilterByArchived($query, $filter)
    {
        if ($this->hasArchivedScope() && $filter != null && array_key_exists('archived', $filter) && $filter['archived'] == '0') {
            $query->whereNull('archivable_model_id');
        }
        return $query;
    }

    protected function hasArchivedScope()
    {
        $scopes = collect($this->getGlobalScopes());
        return $scopes->contains(new ArchivedScope);
    }

    public static function bootArchivable()
    {
        // The Scope uses the auth user which is not available in jobs. 
        // Currently 29-10-2020 no jobs are using the archivable trait.
        if(!app()->runningInConsole() && Auth::user()) {
            static::addGlobalScope(new ArchivedScope);
        }
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
