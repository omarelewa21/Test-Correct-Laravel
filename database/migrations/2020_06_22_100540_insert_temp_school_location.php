<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use tcCore\SchoolLocation;

class InsertTempSchoolLocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if(SchoolLocation::where('customer_code','TC-tijdelijke-docentaccounts')->first() == null){
			  SchoolLocation::where('id',1)->update(['customer_code' =>'TC-tijdelijke-docentaccounts']);
		  }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      if(SchoolLocation::where('customer_code','TC-tijdelijke-docentaccounts')->first() != null){
			  SchoolLocation::where('id',1)->update(['customer_code' =>'niet-TC-tijdelijke-docentaccounts']);
		  }
    }
}
