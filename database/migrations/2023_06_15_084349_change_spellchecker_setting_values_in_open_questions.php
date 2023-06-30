<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        \tcCore\OpenQuestion::query()->update(['spell_check_available' => 0]);
    }

    public function down(): void
    {
        \tcCore\OpenQuestion::whereSubtype('writing')->update(['spell_check_available' => 1]);
    }
};
