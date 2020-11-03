<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 26/03/16
 * Time: 21:12
 */

namespace tcCore\Traits;

use Dyrynda\Database\Support\GeneratesUuid;

trait UuidTrait {

    use GeneratesUuid;

    public function getUUIDKeyName()
    {
        return 'uuid';
    }

    public function getUUIDKey()
    {
        return $this->getAttribute($this->getUUIDKeyName());
    }

    public function getRouteKeyName()
    {
        throw new \Exception('[IDOR] Missing route key model binding for '.__CLASS__.' called url '.request()->fullUrl());
    }
}