<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 17/01/2019
 * Time: 13:33
 */

namespace tcCore\Http\Helpers;


use Illuminate\Support\Facades\Auth;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\User;

class ActingAsHelper
{
    protected static $instance;
    protected $user;

    protected function __construct()
    {
        $this->user = Auth::user();
    }

    public static function getInstance()
    {
        if (static::$instance === null ) {
            static::$instance = new Static();
        }
        return static::$instance;
    }

    public function setUser(User $user)
    {
        PeriodRepository::reset();
        $this->user = $user;
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function reset() {
        PeriodRepository::reset();
        $this->user = null;
    }
}
