<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;
use tcCore\Http\Helpers\EduIxService;

class EduIxRegistration extends Model
{

    protected $guarded = [];

    public static function initWithService(EduIxService $service)
    {
        $instance = self::ByDigiDeliveryId($service->getDigiDeliveryID());
        if ($instance === null) {
            $instance = self::create([
                'digi_delivery_id' => $service->getDigiDeliveryID(),
                'json' => $service->asJson(),
            ]);
        }
        return $instance;
    }

    public static function byDigiDeliveryId($value) {
       return self::where('digi_delivery_id', $value)->first();
    }

    public function isOpen() {
        return ($this->user_id === null);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function addUser(User $user) {
        if ($this->isClosed()) {
            throw new \Exception('not open for registration already closed');
        }
        $this->user()->associate($user);
        return $this;
    }

    /**
     * @return bool
     */
    public function isClosed(): bool
    {
        return !$this->isOpen();
    }
}
