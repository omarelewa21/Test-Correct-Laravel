<?php namespace tcCore\Commands;

class SeleniumTest
{
    const description = 'Prepare for Selenium test';

    const envFile = '.env';
    const envBackupFileWhileSeleniumtest = ".envBackupWhileSeleniumtest";

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
        if(file_get_contents(base_path(self::envBackupFileWhileSeleniumtest)) == 1){
            return false;
        }
        else{
            return true;
        }
    }

    static public function backupEnvFile() {
        self::checkEnv();

        if(!file_exists(base_path(self::envFile))){
            return false;
        }

        $envContents = file_get_contents(base_path(self::envFile));

        file_put_contents(base_path(self::envBackupFileWhileSeleniumtest), $envContents);

        $envContents = str_replace('DB_DATABASE=tccore_dev', 'DB_DATABASE=tccore_dev_selenium', $envContents);
        $envContents = str_replace('SELENIUM_TEST=false', 'SELENIUM_TEST=true', $envContents);
        
        file_put_contents(base_path(self::envFile), $envContents);
    }

    static public function restoreEnvFile() {
        if (!self::hasSeleniumtestSetup()) {
            return false;
        }

        $envContents = file_get_contents(base_path(self::envBackupFileWhileSeleniumtest));

        $envContents = str_replace('SELENIUM_TEST=true', 'SELENIUM_TEST=false', $envContents);

        file_put_contents(base_path(self::envFile),$envContents);
        file_put_contents(base_path(self::envBackupFileWhileSeleniumtest),'1');
    }

}
