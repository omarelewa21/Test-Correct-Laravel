<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \tcCore\UserFeatureSetting::where(
            'title',
            'grade_default_standard'
        )
            ->where('value', 'mistakes_per_point')
            ->update([
                'value' => 'errors_per_point'
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \tcCore\UserFeatureSetting::where(
            'title',
            'grade_default_standard'
        )
            ->where('value', 'errors_per_point')
            ->update([
                'value' => 'mistakes_per_point'
            ]);
    }
};
