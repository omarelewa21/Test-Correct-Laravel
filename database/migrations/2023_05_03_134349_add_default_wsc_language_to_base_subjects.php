<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('base_subjects', function (Blueprint $table) {
            $table->string('wsc_lang')->nullable();
        });

        $languages = collect([
            1  => 'nl_NL',
            22 => 'en_GB',
            23 => 'fr_FR',
            24 => 'de_DE',
            25 => 'es_ES',
            76 => 'it_IT',
        ]);
        $languages->each(function ($lang, $id) {
            \tcCore\BaseSubject::where('id', $id)->update(['wsc_lang' => $lang]);
        });
    }

    public function down(): void
    {
        Schema::table('base_subjects', function (Blueprint $table) {
            $table->dropColumn('wsc_lang');
        });
    }
};
