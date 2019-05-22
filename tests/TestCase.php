<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{


    protected $baseUrl = false;

    const USER_TEACHER = 'p.vries@31.com';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        $this->baseUrl = env('LOGIN_URL');

        return $app;
    }

    public static function getAuthRequestData($overrides = [])
    {
        return array_merge([
            'session_hash' => '5mTzff9qk9TObgv0NfsU7JjLDgnBsRRYSDfASpPEYLb2GjZHSQh1aePy1vhnBq1gFsjWprSU0dsCUgKTJhJVzbIHEEd7Mzw2Y1Y',
            'user'         => static::USER_TEACHER,
        ], $overrides);
    }

}
