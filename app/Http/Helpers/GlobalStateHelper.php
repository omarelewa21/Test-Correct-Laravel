<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 17/01/2019
 * Time: 13:33
 */

namespace tcCore\Http\Helpers;


use Illuminate\Support\Facades\Auth;
use tcCore\User;

class GlobalStateHelper
{
    protected static $instance;
    protected $queueAllowed = true;

    protected function __construct()
    {
        $this->resetAll();
    }

    public static function getInstance()
    {
        if (static::$instance === null ) {
            static::$instance = new Static();
        }
        return static::$instance;
    }

    public function isQueueAllowed()
    {
        return $this->queueAllowed;
    }

    public function setQueueAllowed($val)
    {
        $this->queueAllowed = (bool) $val;
        return $this;
    }

    public function resetQueueAllowed()
    {
        $this->queueAllowed = true;
        return $this;
    }

    public function resetAll() {
        $this->resetQueueAllowed();
        return $this;
    }
}
