<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public const DEFAULT_VALUES = [
        'long'    => [
            'spell_check_available' => false,
            'text_formatting'       => true,
            'mathml_functions'      => true,
            'restrict_word_amount'  => false,
            'max_words'             => null,
        ],
        'medium'  => [
            'spell_check_available' => false,
            'text_formatting'       => true,
            'mathml_functions'      => true,
            'restrict_word_amount'  => false,
            'max_words'             => null,
        ],
        'writing' => [
            'spell_check_available' => true,
            'text_formatting'       => true,
            'mathml_functions'      => true,
            'restrict_word_amount'  => false,
            'max_words'             => null,
        ],
        'short'   => [
            'spell_check_available' => false,
            'text_formatting'       => false,
            'mathml_functions'      => false,
            'restrict_word_amount'  => true,
            'max_words'             => 50,
        ]
    ];

    public function up(): void
    {
        if (!Schema::hasColumn('open_questions', 'text_formatting')) {
            Schema::table('open_questions', function (Blueprint $table) {
                $table->boolean('text_formatting')->nullable();
                $table->boolean('mathml_functions')->nullable();
                $table->boolean('restrict_word_amount')->nullable();
                $table->integer('max_words')->nullable();
            });
        }

        \tcCore\OpenQuestion::select('subtype')
            ->distinct()
            ->pluck('subtype')
            ->each(function ($type) {
                \tcCore\OpenQuestion::withTrashed()
                    ->whereSubtype($type)
                    ->update(self::DEFAULT_VALUES[$type] ?? self::DEFAULT_VALUES['long']);
            });
    }

    public function down(): void
    {
        Schema::table('open_questions', function (Blueprint $table) {
            $table->dropColumn([
                'text_formatting',
                'mathml_functions',
                'restrict_word_amount',
                'max_words',
            ]);
        });
    }
};
