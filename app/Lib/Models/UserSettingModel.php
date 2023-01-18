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

    static public function getAll(User $user,
                                  bool $sessionOnly = false,
                                  bool $sessionStore = false
    ): array
    {
        return static::retrieveSettings($user, $sessionOnly, $sessionStore);
    }

    static public function getSettingFromSession(User $user, string $title): mixed
    {
        return static::getSetting($user, $title, true);
    }

    static public function getSetting(User   $user,
                                      string $title,
                                      bool   $sessionOnly = false,
                                      bool   $sessionStore = false,
    ): mixed
    {
        return static::retrieveSetting($user, $title, $sessionOnly, $sessionStore);
    }

    static public function hasSetting(User $user, string $title): bool
    {
        return static::hasSettingInSession($user, $title) || static::hasSettingInDatabase($user, $title);
    }

    static public function hasSettingInSession(User $user, string $title): bool
    {
        return !is_null(static::retrieveSettingFromSession($user, $title));
    }

    static public function hasSettingInDatabase(User $user, string $title): bool
    {
        return !is_null(static::retrieveSettingFromDatabase($user, $title));
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
        ], [
            'value' => is_array($value) ? json_encode($value) : $value
        ]);
    }

    /**
     * Retrieve all settings
     * @param User $user
     * @param bool $sessionOnly
     * @return array
     */
    static private function retrieveSettings(User $user,
                                             bool $sessionOnly = false,
                                             bool $sessionStore = false
    ): array
    {
        $settings = static::retrieveSettingsFromSession($user);
        if ($sessionOnly || !empty($settings)) {
            return $settings;
        }

        $databaseSettings = static::retrieveSettingsFromDatabase($user);
        if ($databaseSettings && $sessionStore) {
            foreach ($databaseSettings as $title => $value) {
                static::writeSettingToSession($user, $title, $value);
            }
        }
        return $databaseSettings ?? [];
    }

    /**
     * Retrieve a single setting
     * @param User $user
     * @param string $title
     * @param bool $sessionOnly
     * @return mixed
     */
    static private function retrieveSetting(User   $user,
                                            string $title,
                                            bool   $sessionOnly = false,
                                                   $sessionStore = false): mixed
    {
        $setting = static::retrieveSettingFromSession($user, $title);
        if ($sessionOnly || !is_null($setting)) {
            return $setting;
        }
        $databaseSetting = static::retrieveSettingFromDatabase($user, $title);
        if ($databaseSetting && $sessionStore) {
            static::writeSettingToSession($user, $title, $databaseSetting);
        }
        return $databaseSetting;
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
            ->mapWithKeys(fn($setting) => [$setting->title => static::validValue($setting->value)])
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
        $value = static::whereUserId($user->getKey())
            ->whereTitle($title)
            ->value('value');

        return static::validValue($value);
    }

    static private function validValue($value): mixed
    {
        return static::isJson($value) ? json_decode($value, true) : $value;
    }

    static private function isJson($value): bool
    {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }
}