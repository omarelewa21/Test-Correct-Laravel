<?php
namespace tcCore\Http\Helpers;


use Bugsnag\BugsnagLaravel\Facades\Bugsnag;

class BugsnagHelper
{
    public static function notifyAndReturnFalse($message, $context = []): bool
    {
        self::notify($message, $context);

        return false;
    }

    public static function notifyAndReturnTrue($message, $context = []): bool
    {
        self::notify($message, $context);

        return true;
    }

    private static function notify($message, $context = []):void
    {
        Bugsnag::notifyException(new \Exception($message), function ($report) use ($context) {
            $report->setMetaData($context);
        });
    }
}
