<?php

namespace tcCore\Lib\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use tcCore\User;

abstract class UserSettingModel extends Model
{
    public $fillable = ['user_id', 'title', 'value'];

    public function user()
    {
        $this->belongsTo(User::class);
    }

    abstract protected static function sessionKey(User $user): string;

    static public function getAll(User $user, bool $sessionOnly): array
    {
        return static::retrieveSettings($user, $sessionOnly);
    }

    static public function getSettingFromSession(User $user, string $title): mixed
    {
        return static::getSetting($user, $title, true);
    }
    static public function getSetting(User $user, string $title, $sessionOnly = false): mixed
    {
        return static::retrieveSetting($user, $title, $sessionOnly);
    }

    static public function setSetting(User $user, string $title, mixed $value)
    {
        static::writeSettingToDatabase($user, $title, $value);
        static::writeSettingToSession($user, $title, $value);
    }

    static private function writeSettingToSession(User $user, string $title, mixed $value): void
    {
        $sessionValues = static::retrieveSettingsFromSession($user);
        $sessionValues[$title] = $value;

        Session::put(
            static::sessionKey($user),
            $sessionValues
        );
    }

    static private function writeSettingToDatabase(User $user, string $title, mixed $value): void
    {
        static::updateOrCreate([
            'user_id' => $user->getKey(),
            'title'   => $title,
            'value'   => $value
        ]);
    }

    /**
     * Retrieve all settings
     * @param User $user
     * @param bool $sessionOnly
     * @return array
     */
    static private function retrieveSettings(User $user, bool $sessionOnly): array
    {
        $settings = static::retrieveSettingsFromSession($user);
        if ($sessionOnly || !empty($settings)) {
            return $settings;
        }

        return self::retrieveSettingsFromDatabase($user) ?? [];
    }

    /**
     * Retrieve a single setting
     * @param User $user
     * @param string $title
     * @param bool $sessionOnly
     * @return mixed
     */
    static private function retrieveSetting(User $user, string $title, bool $sessionOnly): mixed
    {
        $setting = static::retrieveSettingFromSession($user, $title);
        if ($sessionOnly || !is_null($setting)) {
            return $setting;
        }

        return self::retrieveSettingFromDatabase($user, $title);
    }

    /**
     * Retrieve all settings from the session
     * @param User $user
     * @return array
     */
    static private function retrieveSettingsFromSession(User $user): array
    {
        return Session::get(static::sessionKey($user)) ?? [];
    }

    /**
     * Retrieve a single setting from the session
     * @param User $user
     * @param string $title
     * @return mixed
     */
    static private function retrieveSettingFromSession(User $user, string $title): mixed
    {
        $settings = Session::get(static::sessionKey($user));

        return $settings[$title] ?? null;
    }

    /**
     * Retrieve all settings from the database
     * @param User $user
     * @return mixed
     */
    static private function retrieveSettingsFromDatabase(User $user): array
    {
        return static::whereUserId($user->getKey())
            ->get()
            ->mapWithKeys(fn($setting) => [$setting->title => $setting->value])
            ->toArray() ?? [];
    }

    /**
     * Retrieve a single setting from the database
     * @param User $user
     * @param string $title
     * @return mixed
     */
    static private function retrieveSettingFromDatabase(User $user, string $title): mixed
    {
        return static::whereUserId($user->getKey())
            ->whereTitle($title)
            ->where('updated_at', Carbon::now()->subMinutes(5))
            ->value('title');
    }
}