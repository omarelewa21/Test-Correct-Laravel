<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use tcCore\UserSystemSetting;
use Tests\TestCase;

class UserSystemSettingTest extends TestCase
{
    use DatabaseTransactions;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacher = self::getTeacherOne();
        $this->sessionKey = sprintf('_user-%s-system-settings', $this->teacher->uuid);
    }

    /**
     * @test
     */
    public function can_store_string_values_in_database_and_session()
    {
        $settingKey = 'test-case-setting';
        $settingValue = 'kaassoufflé';

        UserSystemSetting::setSetting(
            $this->teacher,
            $settingKey,
            $settingValue
        );

        $retrievedSessionData = session()->get($this->sessionKey);
        $retrievedDatabaseData = UserSystemSetting::whereUserId($this->teacher->getKey())
            ->whereTitle($settingKey)
            ->first();

        $this->assertNotEmpty($retrievedSessionData);
        $this->assertArrayHasKey($settingKey, $retrievedSessionData);
        $this->assertEquals($settingValue, $retrievedSessionData[$settingKey]);

        $this->assertModelExists($retrievedDatabaseData);
        $this->assertEquals($settingValue, $retrievedDatabaseData->value);
    }

    /**
     * @test
     */
    public function can_update_values_in_database_and_session()
    {
        $settingKey = 'test-case-setting';
        $settingValue = 'kaassoufflé';

        UserSystemSetting::setSetting(
            $this->teacher,
            $settingKey,
            $settingValue
        );

        $newSettingValue = 'mexicano';
        UserSystemSetting::setSetting(
            $this->teacher,
            $settingKey,
            $newSettingValue
        );

        $retrievedSessionData = session()->get($this->sessionKey);
        $retrievedDatabaseData = UserSystemSetting::whereUserId($this->teacher->getKey())
            ->whereTitle($settingKey)
            ->get();

        $this->assertCount(1, $retrievedSessionData);
        $this->assertCount(1, $retrievedDatabaseData);

        $this->assertNotEquals($settingValue, $retrievedSessionData[$settingKey]);
        $this->assertEquals($newSettingValue, $retrievedSessionData[$settingKey]);

        $this->assertNotEquals($settingValue, $retrievedDatabaseData->first()->value);
        $this->assertEquals($newSettingValue, $retrievedDatabaseData->first()->value);
    }

    /**
     * @test
     */
    public function can_store_array_values_in_session()
    {
        $settingKey = 'test-case-setting';
        $settingValue = ['kaas' => 'lekker'];

        UserSystemSetting::setSetting(
            $this->teacher,
            $settingKey,
            $settingValue
        );

        $retrievedSessionData = session()->get($this->sessionKey);

        $this->assertNotEmpty($retrievedSessionData);
        $this->assertArrayHasKey($settingKey, $retrievedSessionData);
        $this->assertEquals($settingValue, $retrievedSessionData[$settingKey]);

    }

    /**
     * @test
     */
    public function can_store_array_values_in_database_as_json()
    {
        $settingKey = 'test-case-setting';
        $settingValue = ['kaas' => 'lekker'];

        UserSystemSetting::setSetting(
            $this->teacher,
            $settingKey,
            $settingValue
        );

        $retrievedDatabaseData = UserSystemSetting::whereUserId($this->teacher->getKey())
            ->whereTitle($settingKey)
            ->first();

        $this->assertModelExists($retrievedDatabaseData);
        $this->assertEquals($settingValue, json_decode($retrievedDatabaseData->value, true));
        $this->assertEquals(json_encode($settingValue), $retrievedDatabaseData->value);
    }

    /**
     * @test
     */
    public function can_retrieve_string_values()
    {
        $settingKey = 'test-case-setting';
        $settingValue = 'kaassoufflé';

        UserSystemSetting::setSetting(
            $this->teacher,
            $settingKey,
            $settingValue
        );

        $retrievedValue = UserSystemSetting::getSetting(
            $this->teacher,
            $settingKey
        );
        $this->assertEquals($settingValue, $retrievedValue);
    }

    /**
     * @test
     */
    public function can_retrieve_array_values()
    {
        $settingKey = 'test-case-setting';
        $settingValue = ['kaas' => 'lekker'];

        UserSystemSetting::setSetting(
            $this->teacher,
            $settingKey,
            $settingValue
        );

        $retrievedValue = UserSystemSetting::getSetting(
            $this->teacher,
            $settingKey
        );

        $this->assertEquals($settingValue, $retrievedValue);
    }

    /**
     * @test
     */
    public function can_check_if_a_setting_exists_in_either_session_or_database()
    {
        $settingKey = 'test-case-setting';
        $nonExistentSettingKey = 'frikandel';
        $settingValue = ['kaas' => 'lekker'];

        UserSystemSetting::setSetting(
            $this->teacher,
            $settingKey,
            $settingValue
        );

        $this->assertTrue(
            UserSystemSetting::hasSetting(
                $this->teacher,
                $settingKey
            )
        );

        $this->assertFalse(
            UserSystemSetting::hasSetting(
                $this->teacher,
                $nonExistentSettingKey
            )
        );
    }

    /**
     * @test
     */
    public function can_retrieve_multiple_settings_at_once_as_array()
    {
        $settingKey = 'test-case-setting';
        $settingKey2 = 'test-case-setting-2';
        $settingValue = ['kaas' => 'lekker'];
        $settingValue2 = ['worst' => 'ok'];

        UserSystemSetting::setSetting(
            $this->teacher,
            $settingKey,
            $settingValue
        );

        UserSystemSetting::setSetting(
            $this->teacher,
            $settingKey2,
            $settingValue2
        );

        $retrievedData = UserSystemSetting::getAll($this->teacher);

        $this->assertIsArray($retrievedData);
        $this->assertCount(2, $retrievedData);
        $this->assertArrayHasKey($settingKey, $retrievedData);
        $this->assertArrayHasKey($settingKey2, $retrievedData);
        $this->assertEquals($settingValue, $retrievedData[$settingKey]);
        $this->assertEquals($settingValue2, $retrievedData[$settingKey2]);
    }
}
