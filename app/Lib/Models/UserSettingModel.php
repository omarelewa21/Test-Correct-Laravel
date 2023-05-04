<?php

namespace tcCore\Lib\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use tcCore\Http\Enums\FeatureSettingKey;
use tcCore\User;

abstract class UserSettingModel extends Model
{
    public $fillable = ['user_id', 'title', 'value'];

    public function user()
    {
        $this->belongsTo(User::class);
    }

    abstract protected static function sessionKey(User $user): string;

    /**
     * @param User $user
     * @param bool $sessionOnly Do not go to the database to retrieve a value;
     * @param bool $sessionStore When there's no session value present, write the database retrieved data into the session;
     * @return array
     */
    public static function getAll(
        User $user,
        bool $sessionOnly = false,
        bool $sessionStore = false
    ): array {
        return static::retrieveSettings($user, $sessionOnly, $sessionStore);
    }

    /**
     * @param User $user
     * @param string|FeatureSettingKey $title
     * @param bool $sessionOnly Do not go to the database to retrieve a value;
     * @param bool $sessionStore When there's no session value present, write the database retrieved data into the session;
     * @return mixed
     */
    public static function getSetting(
        User                     $user,
        string|FeatureSettingKey $title,
        bool                     $sessionOnly = false,
        bool                     $sessionStore = false,
        mixed                    $default = null,
    ): mixed {
        $value = static::retrieveSetting($user, $title, $sessionOnly, $sessionStore);
        if ($title instanceof FeatureSettingKey) {
            $value = $title->castValue($value);
        }
        return $value ?? $default;
    }

    public static function getSettingFromSession(User $user, string|FeatureSettingKey $title): mixed
    {
        return static::getSetting($user, $title, true);
    }

    public static function hasSetting(User $user, string|FeatureSettingKey $title): bool
    {
        return static::hasSettingInSession($user, $title) || static::hasSettingInDatabase($user, $title);
    }

    public static function hasSettingInSession(User $user, string|FeatureSettingKey $title): bool
    {
        return !is_null(static::retrieveSettingFromSession($user, $title));
    }

    public static function hasSettingInDatabase(User $user, string|FeatureSettingKey $title): bool
    {
        return !is_null(static::retrieveSettingFromDatabase($user, $title));
    }

    public static function setSetting(User $user, string|FeatureSettingKey $title, mixed $value): void
    {
        static::writeSettingToDatabase($user, $title, $value);
        static::writeSettingToSession($user, $title, $value);
    }

    public static function clearSession(User $user): void
    {
        Session::forget(static::sessionKey($user),);
    }

    private static function writeSettingToSession(User $user, string|FeatureSettingKey $title, mixed $value): void
    {
        $sessionValues = static::retrieveSettingsFromSession($user);
        $sessionValues[self::getTitleValue($title)] = $value;

        Session::put(
            static::sessionKey($user),
            $sessionValues
        );
    }

    private static function writeSettingToDatabase(User $user, string|FeatureSettingKey $title, mixed $value): void
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
    private static function retrieveSettings(
        User $user,
        bool $sessionOnly = false,
        bool $sessionStore = false
    ): array {
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
        return $databaseSettings;
    }

    /**
     * Retrieve a single setting
     * @param User $user
     * @param string|FeatureSettingKey $title
     * @param bool $sessionOnly
     * @return mixed
     */
    private static function retrieveSetting(
        User                     $user,
        string|FeatureSettingKey $title,
        bool                     $sessionOnly = false,
        bool                     $sessionStore = false
    ): mixed {
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
    private static function retrieveSettingsFromSession(User $user): array
    {
        return Session::get(static::sessionKey($user)) ?? [];
    }

    /**
     * Retrieve a single setting from the session
     * @param User $user
     * @param string|FeatureSettingKey $title
     * @return mixed
     */
    private static function retrieveSettingFromSession(User $user, string|FeatureSettingKey $title): mixed
    {
        $settings = Session::get(static::sessionKey($user));

        return $settings[self::getTitleValue($title)] ?? null;
    }

    /**
     * Retrieve all settings from the database
     * @param User $user
     * @return mixed
     */
    private static function retrieveSettingsFromDatabase(User $user): array
    {
        return static::whereUserId($user->getKey())
            ->get()
            ->mapWithKeys(fn($setting) => [$setting->title => static::parsedValue($setting->value)])
            ->toArray() ?? [];
    }

    /**
     * Retrieve a single setting from the database
     * @param User $user
     * @param string|FeatureSettingKey $title
     * @return mixed
     */
    private static function retrieveSettingFromDatabase(User $user, string|FeatureSettingKey $title): mixed
    {
        $value = static::whereUserId($user->getKey())
            ->whereTitle($title)
            ->value('value');

        return static::parsedValue($value);
    }

    private static function parsedValue($value): mixed
    {
        return static::isJson($value) ? json_decode($value, true) : $value;
    }

    private static function isJson($value): bool
    {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private static function getTitleValue(string|FeatureSettingKey $title): string
    {
        if ($title instanceof FeatureSettingKey) {
            return $title->value;
        }
        return $title;
    }
}