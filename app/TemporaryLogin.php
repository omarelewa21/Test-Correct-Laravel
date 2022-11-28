<?php

namespace tcCore;

use Carbon\Carbon;
use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Traits\UuidTrait;

class TemporaryLogin extends Model
{
    use UuidTrait;

    const MAX_VALID_IN_SECONDS = 5;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'temporary_login';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'uuid', 'options'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function createCakeUrl() {
        return sprintf('%susers/temporary_login/%s', $this->getCorrectCakeUrl(), $this->uuid);
    }

    public static function createForUser(User $user)
    {
        self::where('user_id', $user->getKey())->forceDelete();

        return self::create(['user_id' => $user->getKey()]);
    }

    public static function isValid($uuid)
    {
        $result = false;
        $temporary_login = self::whereUuid($uuid)->first();

        if ($temporary_login && Carbon::now()->diffInSeconds($temporary_login->created_at) < self::MAX_VALID_IN_SECONDS) {
            $result['user'] = $temporary_login->user_id;
            $result['options'] = $temporary_login->options;
            // only valid once;
            $temporary_login->forceDelete();
        }

        return $result;
    }

    public static function createWithOptionsForUser($option, $optionValue, User $user)
    {
        $temporaryLogin = self::createForUser($user);

        if($options = self::buildValidOptionObject($option, $optionValue)) {
            $temporaryLogin->setAttribute('options', $options)->save();
        }
        return $temporaryLogin;
    }


    /**
     * @param $option
     * @param $optionValue
     * @param bool $toJson
     * @return array|null
     */
    public static function buildValidOptionObject($option, $optionValue, $toJson = true)
    {
        if (is_array($option)) {
            if (!is_array($optionValue) || count($option) != count($optionValue)) {
                return null;
            }

            $options = [];
            foreach ($option as $key => $value) {
                $options[$value] = $optionValue[$key];
            }

            return $toJson ? json_encode($options) : $options;
        }

        if (is_array($optionValue) && (isset($optionValue[0]) && is_array($optionValue[0]))) {
            return null;
        }

        return $toJson ? json_encode([$option => $optionValue]) : [$option => $optionValue];
    }

    public static function getOptionsForUser(User $user)
    {
        return TemporaryLogin::whereUserId($user->getKey())->value('options');
    }

    public function getCorrectCakeUrl()
    {
        return BaseHelper::getLoginUrl();
//        if (Str::contains(url('/'), 'welcome2.test')) {
//            return Str::replaceFirst('portal', 'portal2', config('app.url_login'));
//        }
//
//        return config('app.url_login');
    }
}
