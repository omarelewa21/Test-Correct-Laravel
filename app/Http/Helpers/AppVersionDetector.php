<?php
namespace tcCore\Http\Helpers;

use Browser;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class AllowedAppType
{
    // these strings are also used in the apps and therefore should not be changed
    const OK = "OK";
    const NEEDSUPDATE = "NEEDSUPDATE";
    const NOTALLOWED = "NOTALLOWED";
}

class AppVersionDetector
{
    private static $preSharedKeys = [
        "electron" => "aZCLBuzBbHsFpd2DPO3zK84xCoWCQxFaE0Uk3LLd",
        "ios" => "SEFu4HU6IWaXe3lUGIDHE0Mj9o0xco2NNUaL0IkP",
        "chromeos" => "VxCM01YTwRAojSP0zonQQLmhf1zu5RBA8lHJn9ja"
    ];

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
                "3.0.3",
                "3.0.4",
                "3.0.5",
                "3.1.0",
                "3.1.1",
                "3.1.2",
                "3.1.3",
                "3.1.4",
                "3.2.0",
                "3.2.1",
                "3.2.2",
                "3.2.3",
                "3.2.4",
                "4.0.0",
                "4.0.1",
                "4.0.2",
                "4.0.3",
                "4.0.4",
                "4.0.5",
                "4.1.0",
                "4.1.0",
                "4.1.1",
                "4.1.2",
                "4.1.3",
                "4.1.4",
                "4.1.5",
                "4.2.0",
                "4.2.1",
                "4.2.2",
                "4.2.3",
                "4.2.4",
                "4.2.5",
            ],
            "needsUpdate" => [
                "0.0.1",
            ],
            "needsUpdateDeadline" => [
                "0.0.1" => "31 december 2023",
            ],
        ],
        "ChromeOS" => [
            "ok" =>
            [
                "3.1.0",
                "3.1.10",
                "3.1.20",
                "3.1.30",
                "3.1.40",
                "3.1.50",
                "3.2.0",
                "3.2.10",
                "3.2.20",
                "3.2.30",
                "3.2.40",
                "3.2.50",
                "3.3.0",
                "3.3.10",
                "3.3.20",
                "3.3.30",
                "3.3.40",
                "3.3.50",
                "3.4.0",
                "3.4.10",
                "3.4.20",
                "3.4.30",
                "3.4.40",
                "3.4.50",
                "3.5.0",
                "3.5.10",
                "3.5.20",
                "3.5.30",
                "3.5.40",
                "3.5.50",
                "4.0.0",
                "4.0.1",
                "4.0.2",
                "4.0.3",
                "4.0.4",
                "4.0.5",
                "4.1.0",
                "4.1.0",
                "4.1.1",
                "4.1.2",
                "4.1.3",
                "4.1.4",
                "4.1.5",
                "4.2.0",
                "4.2.1",
                "4.2.2",
                "4.2.3",
                "4.2.4",
                "4.2.5",
            ],
            "needsUpdate" => [
                "0.0.1",
                "0.0.2"
            ],
            "needsUpdateDeadline" => [
                "0.0.1" => "31 december 2023",
                "0.0.2" => "20 juli 2023"
            ],
        ],
        "windowsElectron" => [
            "ok" => [
                "4.0.2",
                "4.0.3",
                "4.0.4",
                "4.0.5",
                "4.1.0",
                "4.1.0",
                "4.1.1",
                "4.1.2",
                "4.1.3",
                "4.1.4",
                "4.1.5",
                "4.2.0",
                "4.2.1",
                "4.2.2",
                "4.2.3",
                "4.2.4",
                "4.2.5",
            ],
            "needsUpdate" => [
                "0.0.1",
                "4.0.1"
            ],
            "needsUpdateDeadline" => [
                "0.0.1" => "31 december 2023",
                "4.0.1" => "1 november 2023"
            ],
        ],
        "macosElectron" => [
            "ok" => [
                "4.0.2",
                "4.0.3",
                "4.0.4",
                "4.0.5",
                "4.1.0",
                "4.1.0",
                "4.1.1",
                "4.1.2",
                "4.1.3",
                "4.1.4",
                "4.1.5",
                "4.2.0",
                "4.2.1",
                "4.2.2",
                "4.2.3",
                "4.2.4",
                "4.2.5",
            ],
            "needsUpdate" => [
                "0.0.1",
                "4.0.1"
            ],
            "needsUpdateDeadline" => [
                "0.0.1" => "31 december 2023",
                "4.0.1" => "1 november 2023"
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
                } else if (str::contains($lowerPart0, "mac os x")) {
                    $appType["os"] = 'macOs';
                }
            }
        }

        return $appType;
    }

    public static function osIsWindows() {
        /* This is a stupid implementation, but I cannot see if I'm on windows if they're using a different browser than Edge */
        return !self::isInApp() && !self::osIsMac() && !self::osIsChromebook();
//        return in_array(self::detect()['os'], ['windows10OS', 'windowsOS']);
    }

    public static function osIsMac() {
        return self::detect()['os'] == 'macOs';
    }

    public static function osIsChromebook() {
        return self::detect()['os'] == 'ChromeOS';
    }

    public static function isInApp($headers = false)
    {
        return !self::isInBrowser($headers);
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

    // Remove the "-beta.X" from the "1.2.3-beta.X" string to only keep the "1.2.3" version
    // This way we can have infinite beta versions for an allowed version
    private static function convertVersionNumber(string $version) : string
    {
        if (Str::contains($version, "beta")) {
            try {
                return substr($version, 0, strpos($version, "beta")-1);
            } catch (\Throwable $_) {}
        }
        return $version;
    }

    public static function needsUpdateDeadline($headers = false): \Carbon\Carbon | false
    {
        if (!$headers) {
            $headers = self::getAllHeaders();
        }
        if(is_object($headers)){
            $headers = (array) $headers;
        }
        $version = self::detect($headers);

        $version["app_version"] = self::convertVersionNumber($version["app_version"]);

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
            } catch(\Throwable $e){
                $date = Carbon::createFromLocaleIsoFormat(
                    'MMMM YYYY',
                    'nl',
                    self::$allowedVersions[$version["os"]]["needsUpdateDeadline"][$version["app_version"]]
                );
            }
            return $date;

        }
        return false;
    }

    public static function isVersionAllowed($headers = false)
    {
        $version = self::detect($headers);

        $version["app_version"] = self::convertVersionNumber($version["app_version"]);

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

    /*
     * Enforce app verification through a seperate 'tlckey' header.
     * This method should only be called if canUseBrowserTesting is false.
     * Faulty clients will be logged, and for now still be allowed to avoid potential problems on production.
     */
    public static function verifyKeyHeader(): bool
    {
        $headers = self::getAllHeaders();

        //backdoor header
        //if the header "4yQSPYaGl7HOOideJrjJBVDpPYSoK0GhzULSq1tz" is defined with value "sj6q9kwDa0kEePn9MXVnRkkJxmu6G3lXrvBHRZwz"
        //then we allow it because this is the backdoor for testing
        if (key_exists('4yQSPYaGl7HOOideJrjJBVDpPYSoK0GhzULSq1tz', $headers) && $headers["4yQSPYaGl7HOOideJrjJBVDpPYSoK0GhzULSq1tz"] == "sj6q9kwDa0kEePn9MXVnRkkJxmu6G3lXrvBHRZwz") {
            return true;
        }

        $version = self::detect($headers);
        if ($version["os"] == "windowsElectron" || $version["os"] == "macosElectron") {
            $presharedKey = self::$preSharedKeys['electron'];
            $key = $headers["tlckey"];
            $hmac = true;
        } else if ($version["os"] == "iOS") {
            $presharedKey = self::$preSharedKeys['ios'];
            $key = $headers["tlckey"];
            $hmac = false;
        } else if ($version["os"] == "ChromeOS") {
            $presharedKey = self::$preSharedKeys['chromeos'];
            $key = $headers["tlckey"];
            $hmac = false;
        } else {
            Log::stack(['loki'])->warning("verifyKeyHeader unknown OS, but allowed", ['version_os' => $version["os"], 'headers' => $headers]);
            return true;
        }
        if ($key == null) {
            Log::stack(['loki'])->warning("verifyKeyHeader no key found, but allowed", ['version_os' => $version["os"], 'headers' => $headers]);
            return true;
        }
        $result = self::computeKeyDigest($presharedKey, $hmac);
        if ($key === $result) {
            return true;
        } else {
            Log::stack(['loki'])->warning(
                "verifyKeyHeader failed, but allowed",
                [
                    'key' => $key,
                    'result' => $result,
                    'version_os' => $version["os"],
                    'headers' => $headers
                ]
            );
            return true;
        }
    }

    public static function getHashDate() {
        return Carbon::now('UTC')->format('Y-m-d');
    }

    private static function computeKeyDigest(string $presharedKey, bool $hmac): string
    {
        if ($presharedKey == null) {
            throw new Error("Preshared key is undefined. Key verification failed");
        }
        //get current date
        $hashDate = self::getHashDate();
        $hashUrl = strtok($_SERVER["REQUEST_URI"], '?');

        //electron uses hmac, native JS implementation doesn't
        if ($hmac) {
            $hasher = hash_init("sha256", HASH_HMAC, $presharedKey);

            hash_update($hasher, $hashUrl);
            hash_update($hasher, $hashDate);
            return hash_final($hasher);
        } else {
            return hash('sha256', $hashUrl . $hashDate . $presharedKey);
        }
    }

    public static function handleHeaderCheck($headers = null)
    {
        $headers = $headers != null ? $headers : self::getAllHeaders();

        session([
            'headers' => 'unset headers',
            'TLCVersion' => 'unset version',
            'TLCPlatform' => 'unset platform',
            'TLCPlatformType' => 'unset platform type',
            'TLCPlatformVersion' => 'unset platform version',
            'TLCBrowserType' => 'unset browser type',
            'TLCBrowserVersion' => 'unset browser version'
        ]);

        if (isset($headers['tlc'])) {
            session(['TLCHeader' => $headers['tlc']]);
        } else {
            session(['TLCHeader' => 'not secure...']);
        }

        $version = AppVersionDetector::detect($headers);

        $platform = "";
        if ($version['os'] != "unknown-") {
            $platform = $version['os'];
        } else {
            $platform = Browser::platformFamily();

            // prevent spoofing platform os by modifying user-agent
            if (array_key_exists($platform, self::$osConversion)) {
                $platform = "platform-conflict";
            }
        }

        session([
            'headers' => $headers,
            'TLCVersion' => $version['app_version'], // don't specify an alternative value since this value is used in the code for app checking
            'TLCPlatform' => $platform,
            'TLCPlatformVersion' => $version['os_release'], // as reported by Electron
            'TLCPlatformVersionMajor' => Browser::platformVersionMajor(),
            'TLCPlatformVersionMinor' => Browser::platformVersionMinor(),
            'TLCPlatformVersionPatch' => Browser::platformVersionPatch(),
            'TLCPlatformType' => $version['app_type'],
            'TLCBrowserType' => Browser::browserFamily(),
            'TLCBrowserVersionMajor' => Browser::browserVersionMajor(),
            'TLCBrowserVersionMinor' => Browser::browserVersionMinor(),
            'TLCBrowserVersionPatch' => Browser::browserVersionPatch(),
            'TLCIsIos12' => (Str::lower($version['os']) === 'ios') ? AppVersionDetector::isIos12($headers) : false,
        ]);

        $versionCheckResult = AppVersionDetector::isVersionAllowed($headers);

        session(['TLCVersioncheckResult' => $versionCheckResult]);
    }

    public function getAppVersion(){
        AppVersionDetector::handleHeaderCheck();
        return ['TLCVersion' => session('TLCVersion', null)];
    }

    public static function checkVersionDeadline()
    {
        if (!self::verifyKeyHeader()) {
            return [
                "allowed" => AllowedAppType::NOTALLOWED,
                "deadline" => false
            ];
        }
        $headers = self::getallheaders();
        $allowed = self::isVersionAllowed($headers);
        $deadline = self::needsUpdateDeadline($headers);
        if ($deadline !== false) {
            $deadline = $deadline->getTimestamp();
        }

        return [
            "allowed" => $allowed,
            "deadline" => $deadline
        ];
    }
}
