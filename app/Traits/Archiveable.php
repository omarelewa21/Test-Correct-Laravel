<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 26/03/16
 * Time: 21:12
 */

namespace tcCore\Traits;


use tcCore\Scopes\WithoutArchivedScope;
use Illuminate\Support\Facades\Auth;
use tcCore\ArchivedModel;
use tcCore\User;

trait Archiveable
{
    public function initializeArchiveable()
    {
        $this->append('archived');
    }

    public function getArchivedAttribute()
    {
        return (null === $this->archiveable_model_id) ? false : true;
    }

    public static function bootArchiveable()
    {
        static::addGlobalScope(new WithoutArchivedScope);
    }

    public function archiveForUser(User $user)
    {
        return ArchivedModel::archiveWithModelAndUser($this, $user);
    }

    public function unarchiveForUser(User $user)
    {
        return ArchivedModel::unarchiveWithModelAndUser($this, $user);
    }
}
