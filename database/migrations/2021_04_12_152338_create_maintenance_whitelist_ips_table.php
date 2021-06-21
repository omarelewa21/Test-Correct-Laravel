<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\MaintenanceWhitelistIp;

class CreateMaintenanceWhitelistIpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance_whitelist_ips', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->efficientUuid('uuid')->index()->unique();
            $table->string('ip');
            $table->string('name');
        });

        $ipList = [
            '95.97.95.106' => 'Sobit kantoor',
            '84.87.252.175' => 'Martin thuis',
            '83.85.48.248' => 'Carlo thuis',
            '2001:1c00:2508:7000:dc4a:597f:4da0:6758' => 'Carlo thuis',
            '77.60.34.179' => 'TLC kantoor',
            '77.167.20.237' => 'Jonathan thuis',
            '136.144.207.195' => 'Devportal (Grafana)'
        ];
        MaintenanceWhitelistIp::withoutEvents(function () use ($ipList) {
            foreach ($ipList as $ip => $name) {
                $model = MaintenanceWhitelistIp::make([
                    'ip' => $ip,
                    'name' => $name,
                ]);
                $model->uuid = Ramsey\Uuid\Uuid::uuid4();
                $model->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('maintenance_whitelist_ips');
    }
}
