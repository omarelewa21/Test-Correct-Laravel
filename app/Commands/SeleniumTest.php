<?php namespace tcCore\Commands;

class SeleniumTest
{
    const description = 'Prepare for Selenium test';

    const envFile = '.env';
    const envBackupFileWhileSeleniumtest = ".envBackupWhileSeleniumtest";
    const seleniumEnvFile = '.env.selenium';

	static private function checkEnv()
	{
		if (!in_array(env('APP_ENV'), ['local', 'testing'])) {
            exit();
		} else {
			return true;
		}
    }
    
    static protected function hasSeleniumtestSetup(){
        if(!file_exists(base_path(self::envBackupFileWhileSeleniumtest))){
            die('error searching for the '.self::envBackupFileWhileSeleniumtest.' file');
        }

        if(env('SELENIUM_TEST', false) == true) {
            return true;
        } else {
            return false;
        }
    }

    /*
    1. Copy content of self::envBackupFileWhileSeleniumtest to self::envFile

    return false if failed
    return true otherwise 
    */
    static public function restoreEnvFile() {
        self::checkEnv();

        if (!self::hasSeleniumtestSetup()) {
            return false;
        }

        $envContents = file_get_contents(base_path(self::envBackupFileWhileSeleniumtest));

        file_put_contents(base_path(self::envFile),$envContents);

        return true;
    }

    /*
    1. Backup self::envFile file to self::envBackupFileWhileSeleniumtest
    2. Copy self::seleniumEnvFile to self::envFile

    return false if failed
    return true otherwise
    */
    static function applySeleniumEnvFile() {
        self::checkEnv();

        if (!file_exists(base_path(self::envFile)) || self::hasSeleniumtestSetup()) {
            return false;
        }

        $envContents = file_get_contents(base_path(self::envFile));

        file_put_contents(base_path(self::envBackupFileWhileSeleniumtest), $envContents);

        $seleniumEnvContents = file_get_contents(base_path(self::seleniumEnvFile));

        file_put_contents(base_path(self::envFile), $seleniumEnvContents);

        return true;
    }

}
