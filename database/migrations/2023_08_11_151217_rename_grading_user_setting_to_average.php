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
            ->where('value', 'mean')
            ->update([
                'value' => 'average'
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
            ->where('value', 'average')
            ->update([
                'value' => 'mean'
            ]);
    }
};
