<?php
namespace tcCore\Http\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Str;

abstract class AllowedAppType
{
    const OK = "OK";
    const NEEDSUPDATE = "NEEDSUPDATE";
    const NOTALLOWED = "NOTALLOWED";
}

class AppVersionDetector
{
    private static $osConversion = [
        "windows10" => "windows10OS",
        "windows" => "windowsOS",
        "macbook" => "macOS",
        "ipad" => "iOS",
        "iphone" => "iOS",
        "chromebook" => "ChromeOS",
        "win32" => "windowsElectron",
        "darwin" => "macosElectron"
    ];

    /* example:
        "iOS" => [
            "ok" => ["2.2", "2.3", "2.4", "2.5", "2.6", "2.8", "2.9"],
            "needsUpdate" => ["2.0","2.1"],
            "needsUpdateDeadline" => ["2.1"=>"1 mei 2022"],
        ],
    */
    private static $allowedVersions = [
        "windows10OS" => [
            "ok" => [],
            "needsUpdate" => [],
        ],
        "windowsOS" => [
            "ok" => [],
            "needsUpdate" => [],
        ],
        "macOS" => [
            "ok" => [],
            "needsUpdate" => [],
        ],
        "iOS" => [
            "ok" => [
                "3.0.0",
                "3.0.1",
                "3.0.2",
                "3.0.3",
                "3.0.4",
                "3.0.5",
                "3.1.0",
            ],
            "needsUpdate" => [
                "2.4.2"
            ],
            "needsUpdateDeadline" => [
                "2.4.2" => "29 November 2022",
            ],
        ],
        "ChromeOS" => [
            "ok" =>
            [
                "3.0.0",
                "3.0.1",
                "3.0.2",
                "3.0.3",
                "3.0.4",
                "3.0.5",
                "3.1.0",
            ],
            "needsUpdate" => [
            ],
            "needsUpdateDeadline" => [
            ],
        ],
        "windowsElectron" => [
            "ok" => [
                "3.2.3",
                "3.2.3-beta.1",
                "3.2.3-beta.2",
                "3.2.3-beta.3",
                "3.2.3-beta.4",
                "3.2.3-beta.5",
                "3.2.4",
                "3.2.4-beta.1",
                "3.2.4-beta.2",
                "3.2.4-beta.3",
                "3.2.4-beta.4",
                "3.2.4-beta.5",
                "3.2.5",
                "3.2.5-beta.1",
                "3.2.5-beta.2",
                "3.2.5-beta.3",
                "3.2.5-beta.4",
                "3.2.5-beta.5",
                "3.3.0",
                "3.3.0-beta.1",
                "3.3.0-beta.2",
                "3.3.0-beta.3",
                "3.3.0-beta.4",
                "3.3.0-beta.5",
            ],
            "needsUpdate" => [
            ],
            "needsUpdateDeadline" => [
            ],
        ],
        "macosElectron" => [
            "ok" => [
                "3.2.3",
                "3.2.3-beta.1",
                "3.2.3-beta.2",
                "3.2.3-beta.3",
                "3.2.3-beta.4",
                "3.2.3-beta.5",
                "3.2.4",
                "3.2.4-beta.1",
                "3.2.4-beta.2",
                "3.2.4-beta.3",
                "3.2.4-beta.4",
                "3.2.4-beta.5",
                "3.2.5",
                "3.2.5-beta.1",
                "3.2.5-beta.2",
                "3.2.5-beta.3",
                "3.2.5-beta.4",
                "3.2.5-beta.5",
                "3.3.0",
                "3.3.0-beta.1",
                "3.3.0-beta.2",
                "3.3.0-beta.3",
                "3.3.0-beta.4",
                "3.3.0-beta.5",
            ],
            "needsUpdate" => [
            ],
            "needsUpdateDeadline" => [
            ],
        ]
    ];

    public static function isIos12($headers = false)
    {

        if (!$headers) {
            $headers = self::getAllHeaders();
        }
        if(is_object($headers)){
            $headers = (array) $headers;
        }

        if(array_key_exists('user-agent',$headers)) {
            return Str::contains( $headers['user-agent'],'CPU OS 12');
        }
        return false;
    }

    public static function detect($headers = false)
    {
        if (!$headers) {
            $headers = self::getAllHeaders();
        }
        if(is_object($headers)){
            $headers = (array) $headers;
        }


        /**
         * Format of TLCTestCorrectVersion header:
         *
         * platform (= OS)|app-version|Test-Correct app|Architecture|OS release|Electron app type
         *
         */

        $appType = [
            "app_version" => "x",
            "os" => "unknown-", // REMARK:: also used in AnswersController::is_taking_inbrowser_test
            "arch" => "",
            "os_release" => "",
            "app_type" => "",
        ];

        if (isset($headers["tlctestcorrectversion"])) {
            $data = explode("|", strtolower($headers["tlctestcorrectversion"]));
            $appType["os"] = isset(self::$osConversion[$data[0]])
                ? self::$osConversion[$data[0]]
                : "unknown-" . $data[0];
            $appType["app_version"] = isset($data[1]) ? $data[1] : "x";

            if (isset($data[3])) {
                $appType["arch"] = $data[3];
            }

            if (isset($data[4])) {
                $appType["os_release"] = $data[4];
            }

            if (isset($data[5])) {
                $appType["app_type"] = $data[5];
            }
        } else {
            // only for windows 2.0 and 2.1
            if (array_key_exists("user-agent", $headers)) {
                $parts = explode("|", $headers["user-agent"]);
                $lowerPart0 = strtolower($parts[0]);
                if ($lowerPart0 == "windows" || $lowerPart0 == "chromebook") {
                    $appType["os"] = self::$osConversion[$lowerPart0];
                    $appType["app_version"] = $parts[1];
                }
            }
        }

        return $appType;
    }

    public static function isInBrowser($headers = false)
    {
        if (!$headers) {
            $headers = self::getAllHeaders();
        }
        if(!isset($headers["tlctestcorrectversion"])){
            return true;
        }
        $data = explode("|", strtolower($headers["tlctestcorrectversion"]));
        if(!isset(self::$osConversion[$data[0]])){
            return true;
        }
        return false;
    }

    public static function needsUpdateDeadline($headers = false)
    {
        if (!$headers) {
            $headers = self::getAllHeaders();
        }
        if(is_object($headers)){
            $headers = (array) $headers;
        }
        $version = self::detect($headers);
        if(!isset(self::$allowedVersions[$version["os"]])){
            return false;
        }
        if(!isset(self::$allowedVersions[$version["os"]]["needsUpdateDeadline"])){
            return false;
        }
        if (array_key_exists($version["app_version"], self::$allowedVersions[$version["os"]]["needsUpdateDeadline"])) {
            try {
                $date = Carbon::createFromLocaleIsoFormat(
                    '!DD MMMM YYYY',
                    'nl',
                    self::$allowedVersions[$version["os"]]["needsUpdateDeadline"][$version["app_version"]]
                );
                return $date->isoFormat('LL');
            } catch(\Throwable $e){
                $date = Carbon::createFromLocaleIsoFormat(
                    'MMMM YYYY',
                    'nl',
                    self::$allowedVersions[$version["os"]]["needsUpdateDeadline"][$version["app_version"]]
                );
            }
            return $date->isoFormat('MMMM YYYY');

        }
        return false;
    }

    public static function isVersionAllowed($headers = false)
    {
        $version = self::detect($headers);

        if (
            isset(self::$allowedVersions[$version["os"]]["ok"]) &&
            in_array(
                $version["app_version"],
                self::$allowedVersions[$version["os"]]["ok"]
            )
        ) {
            return AllowedAppType::OK;
        }

        if (
            isset(self::$allowedVersions[$version["os"]]["needsUpdate"]) &&
            in_array(
                $version["app_version"],
                self::$allowedVersions[$version["os"]]["needsUpdate"]
            )) {

            return AllowedAppType::NEEDSUPDATE;
        }

        return AllowedAppType::NOTALLOWED;
    }

    public static function getAllHeaders()
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == "HTTP_") {
                $headers[
                strtolower(
                    str_replace(
                        " ",
                        "-",
                        str_replace("_", " ", substr($name, 5))
                    )
                )
                ] = $value;
            }
        }

        return $headers;
    }

    public static function handleHeaderCheck($headers = null)
    {
        $headers = $headers != null ? $headers : self::getAllHeaders();

        session([
            'headers' => 'unset headers',
            'TLCVersion' => 'unset version',
            'TLCOs' => 'unset os'
        ]);
//        $this->Session->write('headers', 'unset headers');
//        $this->Session->write('TLCVersion', 'unset version');
//        $this->Session->write('TLCOs', 'unset os');

        if (isset($headers['tlc'])) {
            session(['TLCHeader' => $headers['tlc']]);
//            $this->Session->write('TLCHeader', $headers['tlc']);
        } else {
            session(['TLCHeader' => 'not secure...']);
//            $this->Session->write('TLCHeader', 'not secure...');
        }

        $version = AppVersionDetector::detect($headers);

        session([
            'headers' => $headers,
            'UserOsVersion' => self::getUserOSVersion(),
            'UserOsPlatform' => self::getUserOSPlatform(),
            'TLCVersion' => $version['app_version'],
            'TLCOs' => $version['os'],
            'TLCIsIos12' => (Str::lower($version['os']) === 'ios') ? AppVersionDetector::isIos12($headers) : false,
        ]);

//        $this->Session->write('headers', $headers);
//        $this->Session->write('TLCVersion', $version['app_version']);
//        $this->Session->write('TLCOs', $version['os']);

        $versionCheckResult = AppVersionDetector::isVersionAllowed($headers);

        session(['TLCVersioncheckResult' => $versionCheckResult]);
//        $this->Session->write('TLCVersionCheckResult', $versionCheckResult);
    }

    public static function getUserOSPlatform() 
    { 
        $headers = self::getAllHeaders();
        $user_agent = $headers['user-agent'];
        $os_platform  = "Unknown OS Platform"; 
        $os_array     = array(
                              '/windows nt 10/i'      =>  'Windows 10',
                              '/windows nt 6.3/i'     =>  'Windows 8.1',
                              '/windows nt 6.2/i'     =>  'Windows 8',
                              '/windows nt 6.1/i'     =>  'Windows 7',
                              '/windows nt 6.0/i'     =>  'Windows Vista',
                              '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                              '/windows nt 5.1/i'     =>  'Windows XP',
                              '/windows xp/i'         =>  'Windows XP',
                              '/windows nt 5.0/i'     =>  'Windows 2000',
                              '/windows me/i'         =>  'Windows ME',
                              '/win98/i'              =>  'Windows 98',
                              '/win95/i'              =>  'Windows 95',
                              '/win16/i'              =>  'Windows 3.11',
                              '/macintosh|mac os x/i' =>  'Mac OS X',
                              '/mac_powerpc/i'        =>  'Mac OS 9',
                              '/linux/i'              =>  'Linux',
                              '/ubuntu/i'             =>  'Ubuntu',
                              '/iphone/i'             =>  'iPhone',
                              '/ipod/i'               =>  'iPod',
                              '/ipad/i'               =>  'iPad',
                              '/android/i'            =>  'Android',
                              '/blackberry/i'         =>  'BlackBerry',
                              '/webos/i'              =>  'Mobile'
                        );
    
        foreach ($os_array as $regex => $value)
            if (preg_match($regex, $user_agent))
                $os_platform = $value;
    
        return $os_platform;
    }

    public static function getUserOSVersion()
    {
        $headers = self::getAllHeaders();
        $version = null;
        $iosRegularExpression = '/ip(?:hone|[ao]d) os \K[\d_]+/i';
        $androidRegularExpression = '/Android ((\d+|\.)+[^,;]+)/';
        $widowsRegularExpression = '/windows nt \K[\d_]+/i';
        $user_agent = $headers['user-agent'];

        if(preg_match($iosRegularExpression, $user_agent, $matches, PREG_OFFSET_CAPTURE, 0)) {
            $version = $matches[0][0];
        } elseif(preg_match($androidRegularExpression, $user_agent, $matches)) {
            $version = $matches[1];
        } elseif(preg_match($widowsRegularExpression, $user_agent, $matches, PREG_OFFSET_CAPTURE, 0)) {
            $version = $matches[0][0];
        }

        return $version;
    }

    public function getAppVersion(){
        AppVersionDetector::handleHeaderCheck();
        return ['TLCVersion' => session('TLCVersion', null)];
    }
}
