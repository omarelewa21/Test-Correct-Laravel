<?php

namespace tcCore\Http\Helpers;


use Bugsnag\BugsnagLaravel\Facades\Bugsnag;

class BugsnagHelper
{
    public static function notifyAndReturn(string $message, array $context = [], mixed $returnValue = false): mixed
    {
        self::notify($message, $context);

        return $returnValue;
    }

    public static function notifyAndReturnFalse(string $message, array $context = []): bool
    {
        return self::notifyAndReturn($message, $context);
    }

    public static function notifyAndReturnTrue(string $message, array $context = []): bool
    {
        return self::notifyAndReturn($message, $context, true);
    }

    private static function notify(string $message, array $context = []): void
    {
        Bugsnag::notifyException(
            new \Exception($message),
            fn($report) => $report->setMetaData($context)
        );
    }
}
