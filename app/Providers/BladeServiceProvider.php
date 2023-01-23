<?php

namespace tcCore\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Blade::if('student', function () {
            return Auth::user()->isA('student');
        });

        Blade::if('teacher', function () {
            return Auth::user()->isA('teacher');
        });

        Blade::if('notempty', function ($value) {
            return !empty($value);
        });

        Blade::directive('datetime', function ($expression, $format = 'jS F Y') {
            return "<?php echo Carbon::parse($expression)->format($format); ?>";
        });
    }
}